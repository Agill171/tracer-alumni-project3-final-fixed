<?php

namespace App\Jobs;

use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use App\Services\PelacakanQueryService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class ProcessPelacakanBatchItem implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public $tries = 3;

    public $timeout = 120;

    public $failOnTimeout = true;

    public int $uniqueFor = 600;

    public function __construct(
        public int $batchItemId
    ) {
        //
    }

    public function uniqueId(): string
    {
        return (string) $this->batchItemId;
    }

    public function backoff(): array
    {
        return [
            5,
            15,
            30,
        ];
    }

    public function handle(
        PelacakanQueryService $queryService
    ): void {
        $item =
            PelacakanBatchItem::query()
                ->with([
                    'batch',
                    'alumni',
                ])
                ->findOrFail(
                    $this->batchItemId
                );

        /*
         * Idempotent.
         */
        if (
            in_array(
                $item->status,
                [
                    PelacakanBatchItem::STATUS_QUERY_SIAP,
                    PelacakanBatchItem::STATUS_SELESAI,
                ],
                true
            )
        ) {
            return;
        }

        $batch = $item->batch;
        $alumni = $item->alumni;

        if (! $batch || ! $alumni) {
            throw new RuntimeException(
                'Batch atau data alumni tidak ditemukan.'
            );
        }

        $item->update([
            'status' =>
                PelacakanBatchItem::STATUS_DIPROSES,

            'attempts' =>
                ((int) $item->attempts) + 1,

            'last_error' =>
                null,
        ]);

        if (
            $batch->status
            === PelacakanBatch::STATUS_DISIAPKAN
        ) {
            $batch->update([
                'status' =>
                    PelacakanBatch::STATUS_DIPROSES,

                'started_at' =>
                    $batch->started_at ?? now(),
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | TAHAP QUERY PREPARATION
        |--------------------------------------------------------------------------
        |
        | Ini BELUM melakukan klaim bahwa data ditemukan.
        |
        | Hanya menyiapkan query dan URL pencarian untuk
        | verifikasi dari sumber publik.
        |
        */

        $generated =
            $queryService->generate(
                $alumni,
                $batch->sources ?? [],
                $batch->user_id
            );

        if ($generated->isEmpty()) {
            throw new RuntimeException(
                'Tidak ada query pencarian yang berhasil dibuat.'
            );
        }

        $item->update([
            'status' =>
                PelacakanBatchItem::STATUS_QUERY_SIAP,

            'processed_at' =>
                now(),

            'last_error' =>
                null,
        ]);

        $this->syncBatchProgress(
            $batch->id
        );
    }

    public function failed(
        ?Throwable $exception
    ): void {
        $item =
            PelacakanBatchItem::find(
                $this->batchItemId
            );

        if (! $item) {
            return;
        }

        $item->update([
            'status' =>
                PelacakanBatchItem::STATUS_GAGAL,

            'last_error' =>
                $exception?->getMessage(),

            'processed_at' =>
                now(),
        ]);

        $this->syncBatchProgress(
            $item->pelacakan_batch_id
        );
    }

    private function syncBatchProgress(
        int $batchId
    ): void {
        $batch = PelacakanBatch::find(
            $batchId
        );

        if (! $batch) {
            return;
        }

        $queryReadyItems =
            PelacakanBatchItem::query()
                ->where(
                    'pelacakan_batch_id',
                    $batchId
                )
                ->whereIn(
                    'status',
                    [
                        PelacakanBatchItem::STATUS_QUERY_SIAP,
                        PelacakanBatchItem::STATUS_SELESAI,
                    ]
                )
                ->count();

        $failedItems =
            PelacakanBatchItem::query()
                ->where(
                    'pelacakan_batch_id',
                    $batchId
                )
                ->where(
                    'status',
                    PelacakanBatchItem::STATUS_GAGAL
                )
                ->count();

        $processedItems =
            $queryReadyItems
            + $failedItems;

        $isFinished =
            $batch->total_items > 0
            && $processedItems
                >= $batch->total_items;

        if (
            $isFinished
            && $queryReadyItems === 0
        ) {
            $status =
                PelacakanBatch::STATUS_GAGAL;
        } elseif ($isFinished) {
            $status =
                PelacakanBatch::STATUS_QUERY_SIAP;
        } else {
            $status =
                PelacakanBatch::STATUS_DIPROSES;
        }

        $batch->update([
            'processed_items' =>
                $processedItems,

            'success_items' =>
                $queryReadyItems,

            'failed_items' =>
                $failedItems,

            'status' =>
                $status,

            /*
             * Query Siap belum berarti seluruh Project 4 selesai.
             */
            'finished_at' =>
                $status
                    === PelacakanBatch::STATUS_GAGAL
                        ? now()
                        : null,
        ]);
    }
}