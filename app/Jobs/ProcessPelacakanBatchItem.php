<?php

namespace App\Jobs;

use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use App\Services\PelacakanQueryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class ProcessPelacakanBatchItem implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    public $timeout = 120;

    public $failOnTimeout = true;

    public function __construct(
        public int $batchItemId
    ) {
        //
    }

    public function handle(
        PelacakanQueryService $queryService
    ): void {
        $item = PelacakanBatchItem::query()
            ->with([
                'batch',
                'alumni',
            ])
            ->findOrFail($this->batchItemId);

        /*
         * Query sudah pernah berhasil disiapkan.
         * Jangan membuat ulang query pada retry/job duplikat.
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
            'status' => PelacakanBatchItem::STATUS_DIPROSES,
            'attempts' => $item->attempts + 1,
            'last_error' => null,
        ]);

        if (
            $batch->status === PelacakanBatch::STATUS_DISIAPKAN
        ) {
            $batch->update([
                'status' => PelacakanBatch::STATUS_DIPROSES,
                'started_at' => $batch->started_at ?? now(),
            ]);
        }

        /*
         * Tahap pertama pipeline:
         * hanya menghasilkan query dan URL pencarian.
         *
         * Alumni BELUM dianggap selesai dilacak.
         */
        $queryService->generate(
            $alumni,
            $batch->sources ?? [],
            $batch->user_id
        );

        $item->update([
            'status' => PelacakanBatchItem::STATUS_QUERY_SIAP,
            'processed_at' => now(),
            'last_error' => null,
        ]);

        $this->syncBatchProgress(
            $batch->id
        );
    }

    public function failed(
        ?Throwable $exception
    ): void {
        $item = PelacakanBatchItem::find(
            $this->batchItemId
        );

        if (! $item) {
            return;
        }

        $item->update([
            'status' => PelacakanBatchItem::STATUS_GAGAL,
            'last_error' => $exception?->getMessage(),
            'processed_at' => now(),
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

        /*
         * Untuk tahap ini success_items berarti:
         * jumlah alumni yang query pencariannya sudah siap.
         */
        $queryReadyItems = PelacakanBatchItem::query()
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

        $failedItems = PelacakanBatchItem::query()
            ->where(
                'pelacakan_batch_id',
                $batchId
            )
            ->where(
                'status',
                PelacakanBatchItem::STATUS_GAGAL
            )
            ->count();

        $processedItems = $queryReadyItems
            + $failedItems;

        $isPreparationFinished = (
            $batch->total_items > 0
            && $processedItems >= $batch->total_items
        );

        if (
            $isPreparationFinished
            && $queryReadyItems === 0
        ) {
            $status = PelacakanBatch::STATUS_GAGAL;
        } elseif ($isPreparationFinished) {
            $status = PelacakanBatch::STATUS_QUERY_SIAP;
        } else {
            $status = PelacakanBatch::STATUS_DIPROSES;
        }

        $batch->update([
            'processed_items' => $processedItems,
            'success_items' => $queryReadyItems,
            'failed_items' => $failedItems,
            'status' => $status,

            /*
             * finished_at hanya dipakai ketika seluruh pipeline
             * Project 4 benar-benar selesai atau batch gagal total.
             */
            'finished_at' => $status === PelacakanBatch::STATUS_GAGAL
                ? now()
                : null,
        ]);
    }
}