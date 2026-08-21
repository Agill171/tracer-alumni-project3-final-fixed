<?php

namespace App\Jobs;

use App\Models\PelacakanBatch;
use App\Models\PelacakanBatchItem;
use App\Services\AutoEnrichmentService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DispatchAutoEnrichmentBatch implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public $tries = 2;

    public $timeout = 120;

    public $uniqueFor = 600;


    public function __construct(
        public int $batchId
    ) {
        //
    }


    public function uniqueId(): string
    {
        return 'auto-enrichment-batch-'
            .$this->batchId;
    }


    public function handle(
        AutoEnrichmentService $service
    ): void {
        $batch =
            PelacakanBatch::findOrFail(
                $this->batchId
            );


        $batch->update([
            'status' =>
                PelacakanBatch::STATUS_ENRICHMENT,

            'enrichment_started_at' =>
                $batch->enrichment_started_at
                ?? now(),

            'enrichment_finished_at' =>
                null,

            'finished_at' =>
                null,
        ]);


        /*
         * Hanya item yang query-nya sudah siap
         * dan belum sukses enrichment.
         */
        $items =
            PelacakanBatchItem::query()
                ->where(
                    'pelacakan_batch_id',
                    $batch->id
                )
                ->whereIn(
                    'status',
                    [
                        PelacakanBatchItem::STATUS_QUERY_SIAP,
                        PelacakanBatchItem::STATUS_SELESAI,
                    ]
                )
                ->where(
                    function ($query) {
                        $query
                            ->whereNull(
                                'enrichment_status'
                            )
                            ->orWhere(
                                'enrichment_status',
                                PelacakanBatchItem::ENRICHMENT_GAGAL
                            );
                    }
                )
                ->orderBy(
                    'id'
                )
                ->get();


        /*
         * Jika semuanya sudah diproses,
         * cukup sinkronkan.
         */
        if (
            $items->isEmpty()
        ) {
            $service->syncBatchProgress(
                $batch->id
            );

            return;
        }


        foreach (
            $items
            as $item
        ) {
            $item->update([
                'enrichment_status' =>
                    PelacakanBatchItem::ENRICHMENT_MENUNGGU,

                'enrichment_error' =>
                    null,
            ]);


            ProcessAutoEnrichmentItem::dispatch(
                $item->id
            );
        }
    }
}