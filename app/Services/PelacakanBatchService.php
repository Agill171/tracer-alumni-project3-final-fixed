<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class PelacakanBatchService
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

    public function createBatch(
        int $limit = 100,
        array $sourceKeys = [],
        ?int $userId = null,
        ?string $namaBatch = null
    ): PelacakanBatch {
        if ($limit < 1 || $limit > 1000) {
            throw ValidationException::withMessages([
                'limit' => 'Jumlah alumni per batch harus antara 1 sampai 1000.',
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
            array_unique($sourceKeys)
        );

        $invalidSources = array_diff(
            $sourceKeys,
            $availableSources
        );

        if ($invalidSources !== []) {
            throw ValidationException::withMessages([
                'sources' => 'Sumber pelacakan tidak valid: '
                    .implode(', ', $invalidSources),
            ]);
        }

        return DB::transaction(function () use (
            $limit,
            $sourceKeys,
            $userId,
            $namaBatch
        ) {
            $activeBatchExists = PelacakanBatch::query()
                ->whereIn('status', [
                    PelacakanBatch::STATUS_DISIAPKAN,
                    PelacakanBatch::STATUS_DIPROSES,
                ])
                ->exists();

            if ($activeBatchExists) {
                throw new RuntimeException(
                    'Masih ada batch pelacakan yang aktif. '
                    .'Selesaikan batch tersebut sebelum membuat batch baru.'
                );
            }

            $alumniQuery = Alumni::query();

            foreach (self::PROJECT4_FIELDS as $field) {
                $alumniQuery->whereRaw(
                    "TRIM(COALESCE({$field}, '')) = ''"
                );
            }

            $alumniIds = $alumniQuery
                ->whereNotExists(function ($query) {
                    $query
                        ->selectRaw('1')
                        ->from('pelacakan_batch_items')
                        ->whereColumn(
                            'pelacakan_batch_items.alumni_id',
                            'alumnis.id'
                        );
                })
                ->orderBy('id')
                ->limit($limit)
                ->pluck('id');

            if ($alumniIds->isEmpty()) {
                throw new RuntimeException(
                    'Tidak ada alumni baru yang memenuhi syarat untuk dimasukkan ke batch.'
                );
            }

            $batch = PelacakanBatch::create([
                'user_id' => $userId,
                'nama_batch' => $namaBatch
                    ?: 'Batch Project 4 '.now()->format('Y-m-d H:i:s'),
                'status' => PelacakanBatch::STATUS_DISIAPKAN,
                'total_items' => $alumniIds->count(),
                'processed_items' => 0,
                'success_items' => 0,
                'failed_items' => 0,
                'sources' => $sourceKeys,
            ]);

            $now = now();

            $items = $alumniIds
                ->map(fn ($alumniId) => [
                    'pelacakan_batch_id' => $batch->id,
                    'alumni_id' => $alumniId,
                    'status' => PelacakanBatchItem::STATUS_MENUNGGU,
                    'attempts' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])
                ->all();

            PelacakanBatchItem::insert($items);

            return $batch->refresh();
        });
    }
}