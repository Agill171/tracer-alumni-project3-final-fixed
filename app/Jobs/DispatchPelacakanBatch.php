<?php

namespace App\Jobs;

use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use RuntimeException;
use Throwable;

class DispatchPelacakanBatch implements ShouldQueue
{
    use Queueable;

    public $tries = 2;

    public $timeout = 120;

    public $failOnTimeout = true;

    public function __construct(
        public int $batchId
    ) {
        //
    }

    public function handle(): void
    {
        $batch = PelacakanBatch::findOrFail(
            $this->batchId
        );

        if (
            $batch->status === PelacakanBatch::STATUS_SELESAI
        ) {
            return;
        }

        $batch->update([
            'status' => PelacakanBatch::STATUS_DIPROSES,
            'started_at' => $batch->started_at ?? now(),
            'finished_at' => null,
        ]);

        $itemIds = PelacakanBatchItem::query()
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

        if ($itemIds->isEmpty()) {
            throw new RuntimeException(
                'Batch tidak memiliki item berstatus Menunggu.'
            );
        }

        foreach ($itemIds as $itemId) {
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
            'status' => PelacakanBatch::STATUS_GAGAL,
            'finished_at' => now(),
            'catatan' => $exception?->getMessage(),
        ]);
    }
}