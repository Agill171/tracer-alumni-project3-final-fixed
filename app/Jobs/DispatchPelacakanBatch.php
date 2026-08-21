<?php

namespace App\Jobs;

use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class DispatchPelacakanBatch implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public $tries = 2;

    public $timeout = 120;

    public $failOnTimeout = true;

    public int $uniqueFor = 300;

    public function __construct(
        public int $batchId
    ) {
        //
    }

    public function uniqueId(): string
    {
        return (string) $this->batchId;
    }

    public function handle(): void
    {
        $batch = PelacakanBatch::findOrFail(
            $this->batchId
        );

        if (
            in_array(
                $batch->status,
                [
                    PelacakanBatch::STATUS_QUERY_SIAP,
                    PelacakanBatch::STATUS_SELESAI,
                ],
                true
            )
        ) {
            return;
        }

        $batch->update([
            'status' =>
                PelacakanBatch::STATUS_DIPROSES,

            'started_at' =>
                $batch->started_at ?? now(),

            'finished_at' =>
                null,

            'catatan' =>
                null,
        ]);

        $itemIds =
            PelacakanBatchItem::query()
                ->where(
                    'pelacakan_batch_id',
                    $batch->id
                )
                ->where(
                    'status',
                    PelacakanBatchItem::STATUS_MENUNGGU
                )
                ->orderBy('id')
                ->pluck('id');

        /*
         * Aman terhadap retry.
         *
         * Kalau tidak ada item Menunggu, cek ulang progres
         * alih-alih menandai batch gagal secara otomatis.
         */
        if ($itemIds->isEmpty()) {
            $this->syncBatchProgress(
                $batch->id
            );

            return;
        }

        foreach (
            $itemIds
            as $itemId
        ) {
            ProcessPelacakanBatchItem::dispatch(
                $itemId
            );
        }
    }

    public function failed(
        ?Throwable $exception
    ): void {
        $batch = PelacakanBatch::find(
            $this->batchId
        );

        if (! $batch) {
            return;
        }

        $batch->update([
            'status' =>
                PelacakanBatch::STATUS_GAGAL,

            'finished_at' =>
                now(),

            'catatan' =>
                $exception?->getMessage(),
        ]);
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

        $finished =
            $batch->total_items > 0
            && $processedItems
                >= $batch->total_items;

        if (
            $finished
            && $queryReadyItems === 0
        ) {
            $status =
                PelacakanBatch::STATUS_GAGAL;
        } elseif ($finished) {
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

            'finished_at' =>
                $status
                    === PelacakanBatch::STATUS_GAGAL
                        ? now()
                        : null,
        ]);
    }
}