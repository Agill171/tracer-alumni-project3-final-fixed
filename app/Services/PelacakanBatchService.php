<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PelacakanBatchService
{
    /*
    |--------------------------------------------------------------------------
    | FIELD FISIK PROJECT 4
    |--------------------------------------------------------------------------
    |
    | Sosial media alumni mempunyai empat kolom fisik,
    | tetapi pada rubrik tetap dihitung satu kategori.
    |
    */

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
    | JUMLAH ALUMNI TANPA DATA PROJECT 4
    |--------------------------------------------------------------------------
    */

    public function countWithoutAnyProject4(): int
    {
        return $this->withoutAnyProject4Query()
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | JUMLAH ALUMNI YANG MASIH BISA MASUK BATCH
    |--------------------------------------------------------------------------
    */

    public function countAvailableForBatch(): int
    {
        return $this->availableForBatchQuery()
            ->count();
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE BATCH
    |--------------------------------------------------------------------------
    */

    public function createBatch(
        int $limit = 100,
        array $sourceKeys = [],
        ?int $userId = null,
        ?string $namaBatch = null
    ): PelacakanBatch {
        if ($limit < 1 || $limit > 1000) {
            throw ValidationException::withMessages([
                'limit' =>
                    'Jumlah alumni per batch harus antara 1 sampai 1000.',
            ]);
        }

        $availableSources = array_keys(
            config('tracer.sources', [])
        );

        if ($sourceKeys === []) {
            $sourceKeys = [
                'google',
                'linkedin',
                'company_web',
            ];
        }

        $sourceKeys = array_values(
            array_unique(
                $sourceKeys
            )
        );

        $invalidSources = array_diff(
            $sourceKeys,
            $availableSources
        );

        if ($invalidSources !== []) {
            throw ValidationException::withMessages([
                'sources' =>
                    'Sumber pelacakan tidak valid: '
                    .implode(', ', $invalidSources),
            ]);
        }

        return DB::transaction(function () use (
            $limit,
            $sourceKeys,
            $userId,
            $namaBatch
        ) {
            /*
             * Hanya batch yang sedang benar-benar menyiapkan query
             * yang dianggap aktif.
             *
             * STATUS_QUERY_SIAP TIDAK lagi menghalangi pembuatan
             * batch berikutnya.
             */
            $activeBatchExists = PelacakanBatch::query()
                ->whereIn(
                    'status',
                    [
                        PelacakanBatch::STATUS_DISIAPKAN,
                        PelacakanBatch::STATUS_DIPROSES,
                    ]
                )
                ->exists();

            if ($activeBatchExists) {
                throw new RuntimeException(
                    'Masih ada batch yang sedang menyiapkan query. '
                    .'Tunggu sampai batch tersebut mencapai status Query Siap '
                    .'atau Gagal sebelum membuat batch berikutnya.'
                );
            }

            $alumniIds = $this
                ->availableForBatchQuery()
                ->orderBy('id')
                ->limit($limit)
                ->pluck('id');

            if ($alumniIds->isEmpty()) {
                throw new RuntimeException(
                    'Tidak ada alumni baru yang memenuhi syarat untuk batch. '
                    .'Semua alumni tanpa data Project 4 mungkin sudah pernah '
                    .'mendapat query pencarian.'
                );
            }

            $batch = PelacakanBatch::create([
                'user_id' => $userId,

                'nama_batch' =>
                    $namaBatch
                    ?: 'Batch Project 4 '
                    .now()->format('Y-m-d H:i:s'),

                'status' =>
                    PelacakanBatch::STATUS_DISIAPKAN,

                'total_items' =>
                    $alumniIds->count(),

                'processed_items' => 0,
                'success_items' => 0,
                'failed_items' => 0,

                'sources' =>
                    $sourceKeys,

                'started_at' => null,
                'finished_at' => null,
                'catatan' => null,
            ]);

            $now = now();

            $items = $alumniIds
                ->map(
                    fn ($alumniId) => [
                        'pelacakan_batch_id' =>
                            $batch->id,

                        'alumni_id' =>
                            $alumniId,

                        'status' =>
                            PelacakanBatchItem::STATUS_MENUNGGU,

                        'attempts' => 0,

                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                )
                ->all();

            PelacakanBatchItem::insert(
                $items
            );

            return $batch->refresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY: BELUM ADA SATU PUN DATA PROJECT 4
    |--------------------------------------------------------------------------
    */

    private function withoutAnyProject4Query(): Builder
    {
        $query = Alumni::query();

        foreach (
            self::PROJECT4_FIELDS
            as $field
        ) {
            $query->whereRaw(
                "TRIM(COALESCE({$field}, '')) = ''"
            );
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY: TERSEDIA UNTUK BATCH BARU
    |--------------------------------------------------------------------------
    |
    | Alumni:
    |
    | 1. Belum mempunyai satu pun data Project 4.
    | 2. Belum pernah mempunyai batch item yang berhasil/aktif.
    |
    | Item Gagal diperbolehkan dicoba kembali.
    |
    */

    private function availableForBatchQuery(): Builder
    {
        $query = $this
            ->withoutAnyProject4Query();

        $query->whereNotExists(
            function ($subQuery) {
                $subQuery
                    ->selectRaw('1')
                    ->from('pelacakan_batch_items')
                    ->whereColumn(
                        'pelacakan_batch_items.alumni_id',
                        'alumnis.id'
                    )
                    ->where(
                        'pelacakan_batch_items.status',
                        '!=',
                        PelacakanBatchItem::STATUS_GAGAL
                    );
            }
        );

        return $query;
    }
}