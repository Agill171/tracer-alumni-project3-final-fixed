<?php

namespace App\Jobs;

use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use App\Services\PelacakanQueryService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
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
         * Jika sebelumnya sudah berhasil,
         * jangan diproses ulang.
         */
        if (
            $item->status === PelacakanBatchItem::STATUS_SELESAI
        ) {
            return;
        }

        $batch = $item->batch;
        $alumni = $item->alumni;

        if (! $batch || ! $alumni) {
            throw new \RuntimeException(
                'Batch atau data alumni tidak ditemukan.'
            );
        }

        /*
         * Tandai item sedang diproses.
         */
        $item->update([
            'status' => PelacakanBatchItem::STATUS_DIPROSES,
            'attempts' => $item->attempts + 1,
            'last_error' => null,
        ]);

        /*
         * Batch mulai berjalan ketika item pertama diproses.
         */
        if (
            $batch->status === PelacakanBatch::STATUS_DISIAPKAN
        ) {
            $batch->update([
                'status' => PelacakanBatch::STATUS_DIPROSES,
                'started_at' => $batch->started_at ?? now(),
            ]);
        }

        /*
         * Tahap ini hanya membuat dan menyimpan query pencarian.
         * Belum mengambil hasil dari internet.
         */
        $queryService->generate(
            $alumni,
            $batch->sources ?? [],
            $batch->user_id
        );

        $item->update([
            'status' => PelacakanBatchItem::STATUS_SELESAI,
            'processed_at' => now(),
            'last_error' => null,
        ]);

        $this->syncBatchProgress($batch->id);
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
        $batch = PelacakanBatch::find($batchId);

        if (! $batch) {
            return;
        }

        $successItems = PelacakanBatchItem::query()
            ->where('pelacakan_batch_id', $batchId)
            ->where(
                'status',
                PelacakanBatchItem::STATUS_SELESAI
            )
            ->count();

        $failedItems = PelacakanBatchItem::query()
            ->where('pelacakan_batch_id', $batchId)
            ->where(
                'status',
                PelacakanBatchItem::STATUS_GAGAL
            )
            ->count();

        $processedItems = $successItems + $failedItems;

        $isFinished = (
            $batch->total_items > 0
            && $processedItems >= $batch->total_items
        );

        $batch->update([
            'processed_items' => $processedItems,
            'success_items' => $successItems,
            'failed_items' => $failedItems,
            'status' => $isFinished
                ? PelacakanBatch::STATUS_SELESAI
                : PelacakanBatch::STATUS_DIPROSES,
            'finished_at' => $isFinished
                ? now()
                : null,
        ]);
    }
}