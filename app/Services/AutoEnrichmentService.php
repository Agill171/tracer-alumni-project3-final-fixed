<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\HasilPelacakan;
use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use App\Models\PelacakanQuery;
use App\Services\Search\SearchProviderManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AutoEnrichmentService
{
    private const PROJECT4_FIELDS = [
        'linkedin',
        'instagram',
        'facebook',
        'tiktok',
        'email',
        'no_hp',
        'tempat_bekerja',
        'alamat_bekerja',
        'posisi',
        'kategori_pekerjaan',
        'sosmed_tempat_bekerja',
    ];


    /*
    |--------------------------------------------------------------------------
    | CONSTRUCTOR
    |--------------------------------------------------------------------------
    */

    public function __construct(
        private SearchProviderManager $providerManager,
        private IdentityMatchingService $identityMatcher
    ) {
        //
    }


    /*
    |--------------------------------------------------------------------------
    | PROCESS SATU ITEM
    |--------------------------------------------------------------------------
    */

    public function processItem(
        PelacakanBatchItem $item
    ): void {
        $item->loadMissing([
            'batch',
            'alumni',
        ]);


        $batch = $item->batch;
        $alumni = $item->alumni;


        if (! $batch || ! $alumni) {
            throw new RuntimeException(
                'Batch atau alumni tidak ditemukan.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | MULAI
        |--------------------------------------------------------------------------
        */

        $item->update([
            'enrichment_status' =>
                PelacakanBatchItem::ENRICHMENT_DIPROSES,

            'enrichment_attempts' =>
                (int) $item->enrichment_attempts + 1,

            'enrichment_error' =>
                null,

            'enrichment_started_at' =>
                $item->enrichment_started_at
                    ?? now(),

            'enrichment_finished_at' =>
                null,
        ]);


        $provider =
            $this
                ->providerManager
                ->driver();


        $queries =
            $this->selectQueries(
                $batch,
                $alumni
            );


        if ($queries->isEmpty()) {
            throw new RuntimeException(
                'Tidak ada query yang tersedia untuk Auto Enrichment.'
            );
        }


        $resultsPerQuery =
            max(
                1,
                min(
                    20,
                    (int) config(
                        'enrichment.results_per_query',
                        5
                    )
                )
            );


        /*
        |--------------------------------------------------------------------------
        | KANDIDAT TRANSIENT
        |--------------------------------------------------------------------------
        |
        | Search result Tavily tidak disimpan ke tabel kandidat.
        | Kandidat hanya hidup di memory selama job.
        |
        */

        $candidates = [];


        foreach ($queries as $query) {
            $results =
                $provider->search(
                    $query->query,
                    $resultsPerQuery
                );


            foreach ($results as $result) {
                $analysis =
                    $this
                        ->identityMatcher
                        ->analyze(
                            $alumni,
                            $result
                        );


                $url =
                    trim(
                        (string) (
                            $result['url']
                            ?? ''
                        )
                    );


                if ($url === '') {
                    continue;
                }


                $candidates[] = [
                    'query_id' =>
                        $query->id,

                    'query_text' =>
                        $query->query,

                    'source_key' =>
                        $query->sumber,

                    'url' =>
                        $url,

                    'url_hash' =>
                        hash(
                            'sha256',
                            mb_strtolower(
                                $url
                            )
                        ),

                    'domain' =>
                        $analysis['domain']
                        ?? null,

                    'rank' =>
                        (int) (
                            $result['rank']
                            ?? 999
                        ),

                    'signals' =>
                        $analysis['signals']
                        ?? [],

                    'base_score' =>
                        (int) (
                            $analysis['score']
                            ?? 0
                        ),

                    'score' =>
                        (int) (
                            $analysis['score']
                            ?? 0
                        ),

                    'project4' =>
                        $analysis['project4']
                        ?? [],
                ];
            }


            /*
             * Bonus bila URL sama ditemukan
             * melalui beberapa sumber/query.
             */
            $candidates =
                $this->applyCrossSourceBonus(
                    $candidates
                );


            $bestSoFar =
                $this->bestCandidate(
                    $candidates
                );


            /*
             * Early stop hanya bila kandidat:
             *
             * - identitas kuat
             * - mempunyai evidence Project 4 langsung
             */
            if (
                $bestSoFar !== null
                && $this->isStrongCandidate(
                    $bestSoFar
                )
                && ! empty(
                    $bestSoFar['project4']
                )
            ) {
                break;
            }
        }


        $candidates =
            $this->applyCrossSourceBonus(
                $candidates
            );


        $best =
            $this->bestCandidate(
                $candidates
            );


        /*
        |--------------------------------------------------------------------------
        | SIMPAN HASIL FINAL
        |--------------------------------------------------------------------------
        */

        DB::transaction(
            function () use (
                $item,
                $alumni,
                $queries,
                $best,
                $provider
            ) {
                $reviewThreshold =
                    (int) config(
                        'enrichment.review_threshold',
                        50
                    );


                /*
                 * Tidak ada kandidat layak.
                 */
                if (
                    $best === null
                    || (int) $best['score']
                        < $reviewThreshold
                ) {
                    $this->recordNotFound(
                        $item,
                        $alumni,
                        $queries,
                        $provider->name()
                    );

                    return;
                }


                /*
                 * Identitas kuat.
                 */
                if (
                    $this->isStrongCandidate(
                        $best
                    )
                ) {
                    $this->recordStrong(
                        $item,
                        $alumni,
                        $best,
                        $provider->name()
                    );

                    return;
                }


                /*
                 * Kandidat menengah:
                 * manusia harus memeriksa.
                 */
                $this->recordReview(
                    $item,
                    $alumni,
                    $best,
                    $provider->name()
                );
            }
        );


        $this->syncBatchProgress(
            $batch->id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PILIH QUERY
    |--------------------------------------------------------------------------
    */

    private function selectQueries(
        PelacakanBatch $batch,
        Alumni $alumni
    ): Collection {
        $max =
            max(
                1,
                (int) config(
                    'enrichment.max_queries_per_alumni',
                    4
                )
            );


        $sources =
            $batch->sources
            ?? [];


        return PelacakanQuery::query()

            ->where(
                'alumni_id',
                $alumni->id
            )

            ->when(
                $sources !== [],
                fn ($query) =>
                    $query->whereIn(
                        'sumber',
                        $sources
                    )
            )

            ->get()

            ->sortBy(
                function (
                    PelacakanQuery $query
                ) use ($alumni) {
                    return sprintf(
                        '%02d-%03d-%010d',
                        $this->queryTier(
                            $query,
                            $alumni
                        ),
                        $query->prioritas
                            ?? 99,
                        $query->id
                    );
                }
            )

            ->unique(
                fn (PelacakanQuery $query) =>
                    trim(
                        $query->query
                    )
            )

            ->take(
                $max
            )

            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | QUERY TIER
    |--------------------------------------------------------------------------
    */

    private function queryTier(
        PelacakanQuery $query,
        Alumni $alumni
    ): int {
        $text =
            mb_strtolower(
                $query->query
            );


        $name =
            mb_strtolower(
                trim(
                    (string) $alumni->nama
                )
            );


        $nim =
            mb_strtolower(
                trim(
                    (string) $alumni->nim
                )
            );


        $prodi =
            mb_strtolower(
                trim(
                    (string) $alumni->prodi
                )
            );


        $campus =
            mb_strtolower(
                trim(
                    (string) config(
                        'tracer.campus',
                        'Universitas Muhammadiyah Malang'
                    )
                )
            );


        /*
         * Tier 1:
         * Nama + NIM
         */
        if (
            $name !== ''
            && $nim !== ''
            && str_contains(
                $text,
                $name
            )
            && str_contains(
                $text,
                $nim
            )
        ) {
            return 1;
        }


        /*
         * Tier 2:
         * NIM + Kampus
         */
        if (
            $nim !== ''
            && str_contains(
                $text,
                $nim
            )
            && str_contains(
                $text,
                $campus
            )
        ) {
            return 2;
        }


        /*
         * Tier 3:
         * Nama + Prodi + Kampus
         */
        if (
            $name !== ''
            && $prodi !== ''
            && str_contains(
                $text,
                $name
            )
            && str_contains(
                $text,
                $prodi
            )
            && str_contains(
                $text,
                $campus
            )
        ) {
            return 3;
        }


        /*
         * Tier 4:
         * LinkedIn
         */
        if (
            $query->sumber
            === 'linkedin'
        ) {
            return 4;
        }


        /*
         * Tier 5:
         * Company web
         */
        if (
            $query->sumber
            === 'company_web'
        ) {
            return 5;
        }


        return 9;
    }


    /*
    |--------------------------------------------------------------------------
    | CROSS SOURCE BONUS
    |--------------------------------------------------------------------------
    */

    private function applyCrossSourceBonus(
        array $candidates
    ): array {
        if ($candidates === []) {
            return [];
        }


        $sourcesByUrl = [];


        foreach ($candidates as $candidate) {
            $hash =
                $candidate['url_hash'];


            $source =
                $candidate['source_key']
                ?? null;


            if (
                ! isset(
                    $sourcesByUrl[$hash]
                )
            ) {
                $sourcesByUrl[$hash] =
                    [];
            }


            if (
                filled($source)
                && ! in_array(
                    $source,
                    $sourcesByUrl[$hash],
                    true
                )
            ) {
                $sourcesByUrl[$hash][] =
                    $source;
            }
        }


        foreach (
            $candidates
            as $index => $candidate
        ) {
            $sourceCount =
                count(
                    $sourcesByUrl[
                        $candidate['url_hash']
                    ] ?? []
                );


            $bonus =
                min(
                    10,
                    max(
                        0,
                        $sourceCount - 1
                    ) * 5
                );


            $candidates[$index]['score'] =
                min(
                    100,
                    (int) $candidate['base_score']
                    + $bonus
                );
        }


        return $candidates;
    }


    /*
    |--------------------------------------------------------------------------
    | BEST CANDIDATE
    |--------------------------------------------------------------------------
    */

    private function bestCandidate(
        array $candidates
    ): ?array {
        if ($candidates === []) {
            return null;
        }


        usort(
            $candidates,
            function (
                array $a,
                array $b
            ): int {
                /*
                 * Score tertinggi.
                 */
                if (
                    $a['score']
                    !== $b['score']
                ) {
                    return $b['score']
                        <=> $a['score'];
                }


                /*
                 * Kandidat yang mempunyai
                 * Project 4 didahulukan.
                 */
                $aProject4 =
                    empty(
                        $a['project4']
                    )
                        ? 0
                        : 1;


                $bProject4 =
                    empty(
                        $b['project4']
                    )
                        ? 0
                        : 1;


                if (
                    $aProject4
                    !== $bProject4
                ) {
                    return $bProject4
                        <=> $aProject4;
                }


                /*
                 * Search rank kecil lebih baik.
                 */
                return $a['rank']
                    <=> $b['rank'];
            }
        );


        return $candidates[0];
    }


    /*
    |--------------------------------------------------------------------------
    | STRONG IDENTITY
    |--------------------------------------------------------------------------
    |
    | Score >= threshold saja belum cukup.
    |
    | Harus:
    |
    | Nama cocok
    |
    | DAN
    |
    | NIM cocok
    |
    | ATAU
    |
    | Kampus cocok + Timeline/Bidang cocok
    |
    */

    private function isStrongCandidate(
        array $candidate
    ): bool {
        $strongThreshold =
            (int) config(
                'enrichment.strong_threshold',
                80
            );


        if (
            (int) $candidate['score']
            < $strongThreshold
        ) {
            return false;
        }


        $signals =
            $candidate['signals']
            ?? [];


        $name =
            (bool) (
                $signals['nama']
                ?? false
            );


        $nim =
            (bool) (
                $signals['nim']
                ?? false
            );


        $campus =
            (bool) (
                $signals['kampus']
                ?? false
            );


        $timeline =
            (bool) (
                $signals['timeline']
                ?? false
            );


        $bidang =
            (bool) (
                $signals['bidang']
                ?? false
            );


        if (! $name) {
            return false;
        }


        if ($nim) {
            return true;
        }


        return $campus
            && (
                $timeline
                || $bidang
            );
    }


    /*
    |--------------------------------------------------------------------------
    | RECORD STRONG
    |--------------------------------------------------------------------------
    */

    private function recordStrong(
        PelacakanBatchItem $item,
        Alumni $alumni,
        array $candidate,
        string $provider
    ): void {
        $project4 =
            $candidate['project4']
            ?? [];


        $automationKey =
            hash(
                'sha256',
                'auto|'
                .$item->id
                .'|'
                .$candidate['url_hash']
                .'|strong'
            );


        HasilPelacakan::updateOrCreate(
            [
                'automation_key' =>
                    $automationKey,
            ],
            [
                'alumni_id' =>
                    $alumni->id,

                'user_id' =>
                    $item->batch->user_id,

                'status_pelacakan' =>
                    Alumni::STATUS_TERIDENTIFIKASI,

                'judul_temuan' =>
                    'Auto Enrichment: kandidat identitas kuat',

                'sumber_temuan' =>
                    $provider
                    .' / '
                    .(
                        $candidate['domain']
                        ?? 'Web'
                    ),

                'link_bukti' =>
                    $candidate['url'],

                'query_pencarian' =>
                    $candidate['query_text'],

                'ringkasan_hasil' =>
                    'Auto Enrichment menemukan kandidat dengan '
                    .'confidence '
                    .$candidate['score']
                    .'%. Kandidat memenuhi aturan identitas kuat. '
                    .'Field Project 4 hanya diterapkan bila mempunyai '
                    .'evidence langsung dan field alumni masih kosong.',

                'tanggal_ditemukan' =>
                    now()->toDateString(),

                'confidence_score' =>
                    $candidate['score'],

                'kategori_kecocokan' =>
                    HasilPelacakan::classify(
                        $candidate['score']
                    ),

                'sinyal_identitas' =>
                    $candidate['signals'],

                'temuan_project4' =>
                    $project4 !== []
                        ? $project4
                        : null,

                'status_audit' =>
                    HasilPelacakan::AUDIT_BELUM,
            ]
        );


        /*
         * Hanya apply data Project 4
         * yang berasal dari evidence langsung.
         */
        $this->applyProject4(
            $alumni,
            $project4
        );


        $alumni->update([
            'status_verifikasi' =>
                Alumni::STATUS_TERIDENTIFIKASI,
        ]);


        $item->update([
            'enrichment_status' =>
                PelacakanBatchItem::ENRICHMENT_TERIDENTIFIKASI,

            'enrichment_error' =>
                null,

            'enrichment_finished_at' =>
                now(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RECORD REVIEW
    |--------------------------------------------------------------------------
    */

    private function recordReview(
        PelacakanBatchItem $item,
        Alumni $alumni,
        array $candidate,
        string $provider
    ): void {
        $automationKey =
            hash(
                'sha256',
                'auto|'
                .$item->id
                .'|'
                .$candidate['url_hash']
                .'|review'
            );


        HasilPelacakan::updateOrCreate(
            [
                'automation_key' =>
                    $automationKey,
            ],
            [
                'alumni_id' =>
                    $alumni->id,

                'user_id' =>
                    $item->batch->user_id,

                'status_pelacakan' =>
                    Alumni::STATUS_PERLU_VERIFIKASI,

                'judul_temuan' =>
                    'Auto Enrichment: kandidat perlu verifikasi',

                'sumber_temuan' =>
                    $provider
                    .' / '
                    .(
                        $candidate['domain']
                        ?? 'Web'
                    ),

                'link_bukti' =>
                    $candidate['url'],

                'query_pencarian' =>
                    $candidate['query_text'],

                'ringkasan_hasil' =>
                    'Auto Enrichment menemukan kandidat dengan '
                    .'confidence '
                    .$candidate['score']
                    .'%. Kandidat belum memenuhi aturan identitas '
                    .'kuat dan harus diperiksa manual.',

                'tanggal_ditemukan' =>
                    now()->toDateString(),

                'confidence_score' =>
                    $candidate['score'],

                'kategori_kecocokan' =>
                    HasilPelacakan::classify(
                        $candidate['score']
                    ),

                'sinyal_identitas' =>
                    $candidate['signals'],

                /*
                 * Kandidat Project 4 boleh disimpan
                 * sebagai evidence, tetapi tidak
                 * diterapkan otomatis ke Alumni.
                 */
                'temuan_project4' =>
                    ! empty(
                        $candidate['project4']
                    )
                        ? $candidate['project4']
                        : null,

                'status_audit' =>
                    HasilPelacakan::AUDIT_BELUM,
            ]
        );


        /*
         * Jangan menurunkan alumni yang sebelumnya
         * sudah teridentifikasi kuat.
         */
        if (
            $alumni->status_verifikasi
            !== Alumni::STATUS_TERIDENTIFIKASI
        ) {
            $alumni->update([
                'status_verifikasi' =>
                    Alumni::STATUS_PERLU_VERIFIKASI,
            ]);
        }


        $item->update([
            'enrichment_status' =>
                PelacakanBatchItem::ENRICHMENT_PERLU_VERIFIKASI,

            'enrichment_error' =>
                null,

            'enrichment_finished_at' =>
                now(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | RECORD NOT FOUND
    |--------------------------------------------------------------------------
    */

    private function recordNotFound(
        PelacakanBatchItem $item,
        Alumni $alumni,
        Collection $queries,
        string $provider
    ): void {
        $automationKey =
            hash(
                'sha256',
                'auto|'
                .$item->id
                .'|not-found'
            );


        HasilPelacakan::updateOrCreate(
            [
                'automation_key' =>
                    $automationKey,
            ],
            [
                'alumni_id' =>
                    $alumni->id,

                'user_id' =>
                    $item->batch->user_id,

                'status_pelacakan' =>
                    Alumni::STATUS_TIDAK_DITEMUKAN,

                'judul_temuan' =>
                    'Auto Enrichment tidak menemukan kandidat relevan',

                'sumber_temuan' =>
                    $provider,

                'link_bukti' =>
                    null,

                'query_pencarian' =>
                    $queries
                        ->pluck(
                            'query'
                        )
                        ->take(3)
                        ->implode(
                            ' | '
                        ),

                'ringkasan_hasil' =>
                    'Auto Enrichment telah memeriksa '
                    .$queries->count()
                    .' query prioritas. Tidak ditemukan kandidat '
                    .'yang mencapai confidence minimum untuk '
                    .'diperiksa lebih lanjut.',

                'tanggal_ditemukan' =>
                    now()->toDateString(),

                'confidence_score' =>
                    null,

                'kategori_kecocokan' =>
                    null,

                'sinyal_identitas' =>
                    null,

                'temuan_project4' =>
                    null,

                'status_audit' =>
                    HasilPelacakan::AUDIT_BELUM,
            ]
        );


        /*
         * Jangan downgrade status yang sebelumnya
         * sudah lebih kuat.
         */
        if (
            blank(
                $alumni->status_verifikasi
            )
            || in_array(
                $alumni->status_verifikasi,
                [
                    Alumni::STATUS_BELUM_DILACAK,
                    Alumni::STATUS_TIDAK_DITEMUKAN,
                ],
                true
            )
        ) {
            $alumni->update([
                'status_verifikasi' =>
                    Alumni::STATUS_TIDAK_DITEMUKAN,
            ]);
        }


        $item->update([
            'enrichment_status' =>
                PelacakanBatchItem::ENRICHMENT_TIDAK_DITEMUKAN,

            'enrichment_error' =>
                null,

            'enrichment_finished_at' =>
                now(),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | APPLY PROJECT 4
    |--------------------------------------------------------------------------
    */

    private function applyProject4(
        Alumni $alumni,
        array $project4
    ): void {
        if ($project4 === []) {
            return;
        }


        $updates = [];


        foreach (
            $project4
            as $field => $value
        ) {
            if (
                ! in_array(
                    $field,
                    self::PROJECT4_FIELDS,
                    true
                )
                || blank(
                    $value
                )
            ) {
                continue;
            }


            /*
             * Jangan overwrite data lama otomatis.
             */
            if (
                blank(
                    $alumni->getAttribute(
                        $field
                    )
                )
            ) {
                $updates[$field] =
                    $value;
            }
        }


        if ($updates !== []) {
            $alumni->update(
                $updates
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MARK FAILED
    |--------------------------------------------------------------------------
    */

    public function markFailed(
        PelacakanBatchItem $item,
        ?string $message
    ): void {
        $item->update([
            'enrichment_status' =>
                PelacakanBatchItem::ENRICHMENT_GAGAL,

            'enrichment_error' =>
                mb_substr(
                    $message
                    ?? 'Auto Enrichment gagal.',
                    0,
                    5000
                ),

            'enrichment_finished_at' =>
                now(),
        ]);


        $this->syncBatchProgress(
            $item->pelacakan_batch_id
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SYNC BATCH PROGRESS
    |--------------------------------------------------------------------------
    */

    public function syncBatchProgress(
        int $batchId
    ): void {
        $batch =
            PelacakanBatch::find(
                $batchId
            );


        if (! $batch) {
            return;
        }


        $base =
            PelacakanBatchItem::query()
                ->where(
                    'pelacakan_batch_id',
                    $batchId
                );


        $identified =
            (clone $base)
                ->where(
                    'enrichment_status',
                    PelacakanBatchItem::ENRICHMENT_TERIDENTIFIKASI
                )
                ->count();


        $review =
            (clone $base)
                ->where(
                    'enrichment_status',
                    PelacakanBatchItem::ENRICHMENT_PERLU_VERIFIKASI
                )
                ->count();


        $notFound =
            (clone $base)
                ->where(
                    'enrichment_status',
                    PelacakanBatchItem::ENRICHMENT_TIDAK_DITEMUKAN
                )
                ->count();


        $failed =
            (clone $base)
                ->where(
                    'enrichment_status',
                    PelacakanBatchItem::ENRICHMENT_GAGAL
                )
                ->count();


        $processed =
            $identified
            + $review
            + $notFound
            + $failed;


        /*
         * Semua item sudah mendapatkan
         * hasil terminal dari mesin.
         */
        $allProcessed =
            (int) $batch->total_items > 0
            && $processed
                >= (int) $batch->total_items;


        /*
        |--------------------------------------------------------------------------
        | STATUS BATCH
        |--------------------------------------------------------------------------
        */

        if (! $allProcessed) {
            $status =
                PelacakanBatch::STATUS_ENRICHMENT;
        } elseif (
            $failed
            >= (int) $batch->total_items
        ) {
            $status =
                PelacakanBatch::STATUS_GAGAL;
        } elseif (
            $review > 0
            || $failed > 0
        ) {
            $status =
                PelacakanBatch::STATUS_PERLU_REVIEW;
        } else {
            $status =
                PelacakanBatch::STATUS_SELESAI;
        }


        $batch->update([
            'enrichment_processed_items' =>
                $processed,

            'identified_items' =>
                $identified,

            'review_items' =>
                $review,

            'not_found_items' =>
                $notFound,

            'enrichment_failed_items' =>
                $failed,

            'status' =>
                $status,

            'enrichment_started_at' =>
                $batch->enrichment_started_at
                    ?? now(),

            /*
             * Perlu Review tetap berarti
             * proses mesin sudah selesai.
             */
            'enrichment_finished_at' =>
                $allProcessed
                    ? (
                        $batch->enrichment_finished_at
                        ?? now()
                    )
                    : null,

            'finished_at' =>
                $allProcessed
                    ? (
                        $batch->finished_at
                        ?? now()
                    )
                    : null,
        ]);
    }
}