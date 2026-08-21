<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchAutoEnrichmentBatch;
use App\Models\Alumni;
use App\Models\HasilPelacakan;
use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use App\Services\AutoEnrichmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AutoEnrichmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | START AUTO ENRICHMENT
    |--------------------------------------------------------------------------
    */

    public function start(
        PelacakanBatch $batch
    ): RedirectResponse {
        if (
            ! config(
                'enrichment.enabled'
            )
        ) {
            return back()
                ->withErrors([
                    'enrichment' =>
                        'Auto Enrichment belum diaktifkan di .env.',
                ]);
        }


        $provider =
            config(
                'enrichment.provider',
                'tavily'
            );


        if (
            $provider === 'tavily'
            && blank(
                config(
                    'enrichment.tavily.api_key'
                )
            )
        ) {
            return back()
                ->withErrors([
                    'enrichment' =>
                        'TAVILY_API_KEY belum diisi.',
                ]);
        }


        /*
         * Jangan jalankan ulang batch yang semua item
         * enrichment-nya sudah selesai.
         */
        if (
            (int) $batch->total_items > 0
            && (int) $batch->enrichment_processed_items
                >= (int) $batch->total_items
        ) {
            return back()
                ->withErrors([
                    'enrichment' =>
                        'Semua item pada batch ini sudah diproses Auto Enrichment.',
                ]);
        }


        $readyItems =
            $batch
                ->items()
                ->whereIn(
                    'status',
                    [
                        PelacakanBatchItem::STATUS_QUERY_SIAP,
                        PelacakanBatchItem::STATUS_SELESAI,
                    ]
                )
                ->where(function ($query) {
                    $query
                        ->whereNull(
                            'enrichment_status'
                        )
                        ->orWhere(
                            'enrichment_status',
                            PelacakanBatchItem::ENRICHMENT_GAGAL
                        );
                })
                ->count();


        if ($readyItems === 0) {
            return back()
                ->withErrors([
                    'enrichment' =>
                        'Tidak ada item yang siap diproses Auto Enrichment.',
                ]);
        }


        DispatchAutoEnrichmentBatch::dispatch(
            $batch->id
        );


        return back()
            ->with(
                'success',
                'Auto Enrichment masuk antrean. '
                .'Jalankan queue worker untuk memproses batch.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | REVIEW
    |--------------------------------------------------------------------------
    |
    | Candidate mentah Tavily tidak disimpan.
    | Evidence final tersedia pada Detail Alumni.
    |
    */

    public function review(
        PelacakanBatchItem $item
    ): RedirectResponse {
        return redirect()
            ->route(
                'alumni.show',
                [
                    'alumni' =>
                        $item->alumni_id,

                    'from_batch' =>
                        $item->pelacakan_batch_id,
                ]
            );
    }


    /*
    |--------------------------------------------------------------------------
    | TOLAK KANDIDAT / FALSE POSITIVE
    |--------------------------------------------------------------------------
    |
    | Evidence TIDAK dihapus.
    |
    | Yang dilakukan:
    |
    | - kandidat ditandai Salah pada audit
    | - evidence/link tetap disimpan
    | - hasil menjadi Tidak Cocok
    | - alumni disinkronkan
    | - batch item berubah dari Perlu Verifikasi
    |   menjadi Tidak Ditemukan
    |
    */

    public function reject(
        Request $request,
        HasilPelacakan $pelacakan,
        AutoEnrichmentService $enrichmentService
    ): RedirectResponse {
        $batchId =
            $request->integer(
                'from_batch'
            );


        $alumni =
            $pelacakan->alumni;


        abort_if(
            ! $alumni,
            404,
            'Alumni pada hasil pelacakan tidak ditemukan.'
        );


        /*
         * Hanya kandidat Auto Enrichment yang boleh
         * menggunakan tombol khusus ini.
         */
        if (
            blank(
                $pelacakan->automation_key
            )
        ) {
            return redirect()
                ->route(
                    'alumni.show',
                    $this->alumniRouteParameters(
                        $alumni,
                        $batchId
                    )
                )
                ->withErrors([
                    'enrichment' =>
                        'Hasil ini bukan kandidat Auto Enrichment.',
                ]);
        }


        /*
         * Sudah pernah ditolak.
         */
        if (
            $pelacakan->status_audit
            === HasilPelacakan::AUDIT_SALAH
        ) {
            return redirect()
                ->route(
                    'alumni.show',
                    $this->alumniRouteParameters(
                        $alumni,
                        $batchId
                    )
                )
                ->with(
                    'success',
                    'Kandidat ini sudah ditandai sebagai false positive.'
                );
        }


        /*
         * Hanya kandidat review yang dapat ditolak
         * melalui workflow ini.
         */
        if (
            $pelacakan->status_pelacakan
            !== Alumni::STATUS_PERLU_VERIFIKASI
        ) {
            return redirect()
                ->route(
                    'alumni.show',
                    $this->alumniRouteParameters(
                        $alumni,
                        $batchId
                    )
                )
                ->withErrors([
                    'enrichment' =>
                        'Kandidat ini tidak sedang berstatus Perlu Verifikasi.',
                ]);
        }


        DB::transaction(
            function () use (
                $request,
                $pelacakan,
                $alumni,
                $batchId
            ) {
                /*
                 * Simpan informasi scoring asli
                 * dalam catatan audit.
                 */
                $originalScore =
                    $pelacakan->confidence_score;


                $originalSignals =
                    $pelacakan->sinyal_identitas
                    ?? [];


                $signalParts =
                    [];


                foreach (
                    $originalSignals
                    as $key => $value
                ) {
                    $signalParts[] =
                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                (string) $key
                            )
                        )
                        .'='
                        .(
                            $value
                                ? 'ya'
                                : 'tidak'
                        );
                }


                $signalSummary =
                    implode(
                        ', ',
                        $signalParts
                    );


                $auditNote =
                    'Kandidat Auto Enrichment ditolak manual '
                    .'sebagai false positive.';


                if (
                    $originalScore !== null
                ) {
                    $auditNote .=
                        ' Skor otomatis awal: '
                        .$originalScore
                        .'%.';
                }


                if (
                    $signalSummary !== ''
                ) {
                    $auditNote .=
                        ' Sinyal otomatis awal: '
                        .$signalSummary
                        .'.';
                }


                /*
                 * Preserve ringkasan hasil pencarian asli.
                 */
                $ringkasan =
                    trim(
                        (string) $pelacakan->ringkasan_hasil
                    );


                if ($ringkasan !== '') {
                    $ringkasan .=
                        PHP_EOL
                        .PHP_EOL;
                }


                $ringkasan .=
                    'Keputusan manual: kandidat ditolak sebagai '
                    .'false positive. Evidence tetap disimpan '
                    .'sebagai jejak audit dan tidak digunakan '
                    .'untuk mengisi data Project 4.';


                /*
                 * Jangan hapus evidence.
                 *
                 * URL, query, sumber, score awal, dan sinyal
                 * tetap tersedia untuk transparansi audit.
                 */
                $pelacakan->update([
                    'status_pelacakan' =>
                        Alumni::STATUS_TIDAK_DITEMUKAN,

                    'judul_temuan' =>
                        'Auto Enrichment: kandidat ditolak manual',

                    'kategori_kecocokan' =>
                        HasilPelacakan::KATEGORI_TIDAK_COCOK,

                    'ringkasan_hasil' =>
                        $ringkasan,

                    /*
                     * Tidak boleh ada kandidat Project 4
                     * yang dianggap valid setelah ditolak.
                     */
                    'temuan_project4' =>
                        null,

                    /*
                     * Audit final.
                     */
                    'status_audit' =>
                        HasilPelacakan::AUDIT_SALAH,

                    'catatan_audit' =>
                        $auditNote,

                    'audited_by' =>
                        $request->user()->id,

                    'audited_at' =>
                        now(),
                ]);


                /*
                |--------------------------------------------------------------------------
                | SYNC STATUS ALUMNI
                |--------------------------------------------------------------------------
                |
                | Jangan langsung set Tidak Ditemukan jika
                | ternyata alumni memiliki evidence lain
                | yang lebih kuat.
                |
                */

                $otherStrong =
                    $alumni
                        ->hasilPelacakans()
                        ->where(
                            'id',
                            '<>',
                            $pelacakan->id
                        )
                        ->where(
                            'status_pelacakan',
                            Alumni::STATUS_TERIDENTIFIKASI
                        )
                        ->where(function ($query) {
                            $query
                                ->whereNull(
                                    'status_audit'
                                )
                                ->orWhere(
                                    'status_audit',
                                    '<>',
                                    HasilPelacakan::AUDIT_SALAH
                                );
                        })
                        ->exists();


                $otherReview =
                    $alumni
                        ->hasilPelacakans()
                        ->where(
                            'id',
                            '<>',
                            $pelacakan->id
                        )
                        ->where(
                            'status_pelacakan',
                            Alumni::STATUS_PERLU_VERIFIKASI
                        )
                        ->where(function ($query) {
                            $query
                                ->whereNull(
                                    'status_audit'
                                )
                                ->orWhere(
                                    'status_audit',
                                    '<>',
                                    HasilPelacakan::AUDIT_SALAH
                                );
                        })
                        ->exists();


                if ($otherStrong) {
                    $newAlumniStatus =
                        Alumni::STATUS_TERIDENTIFIKASI;
                } elseif ($otherReview) {
                    $newAlumniStatus =
                        Alumni::STATUS_PERLU_VERIFIKASI;
                } else {
                    $newAlumniStatus =
                        Alumni::STATUS_TIDAK_DITEMUKAN;
                }


                /*
                 * Status hanya kita sinkronkan bila alumni
                 * memang sedang berada dalam workflow review.
                 *
                 * Ini mencegah downgrade data yang mungkin
                 * sudah diverifikasi lewat proses lain.
                 */
                if (
                    $alumni->status_verifikasi
                    === Alumni::STATUS_PERLU_VERIFIKASI
                ) {
                    $alumni->update([
                        'status_verifikasi' =>
                            $newAlumniStatus,
                    ]);
                }


                /*
                |--------------------------------------------------------------------------
                | SYNC BATCH ITEM
                |--------------------------------------------------------------------------
                */

                if ($batchId > 0) {
                    $item =
                        PelacakanBatchItem::query()
                            ->where(
                                'pelacakan_batch_id',
                                $batchId
                            )
                            ->where(
                                'alumni_id',
                                $alumni->id
                            )
                            ->first();


                    if (
                        $item
                        && $item->enrichment_status
                            === PelacakanBatchItem::ENRICHMENT_PERLU_VERIFIKASI
                    ) {
                        $item->update([
                            'enrichment_status' =>
                                PelacakanBatchItem::ENRICHMENT_TIDAK_DITEMUKAN,

                            'enrichment_error' =>
                                null,

                            'enrichment_finished_at' =>
                                now(),
                        ]);
                    }
                }
            }
        );


        /*
         * Hitung ulang counter batch setelah transaksi selesai.
         */
        if ($batchId > 0) {
            $enrichmentService
                ->syncBatchProgress(
                    $batchId
                );
        }


        return redirect()
            ->route(
                'alumni.show',
                $this->alumniRouteParameters(
                    $alumni,
                    $batchId
                )
            )
            ->with(
                'success',
                'Kandidat berhasil ditolak sebagai false positive. '
                .'Evidence tetap disimpan sebagai jejak audit.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | ROUTE PARAMETERS
    |--------------------------------------------------------------------------
    */

    private function alumniRouteParameters(
        Alumni $alumni,
        int $batchId
    ): array {
        $parameters = [
            'alumni' =>
                $alumni->id,
        ];


        if ($batchId > 0) {
            $parameters['from_batch'] =
                $batchId;
        }


        return $parameters;
    }
}