<?php

namespace App\Jobs;

use App\Models\PelacakanBatchItem;
use App\Services\AutoEnrichmentService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessAutoEnrichmentItem implements
    ShouldQueue,
    ShouldBeUnique
{
    use Queueable;

    public $tries = 3;

    public $timeout = 180;

    public $failOnTimeout = true;

    public $uniqueFor = 600;


    public function __construct(
        public int $batchItemId
    ) {
        //
    }


    public function uniqueId(): string
    {
        return 'auto-enrichment-item-'
            .$this->batchItemId;
    }


    public function handle(
        AutoEnrichmentService $service
    ): void {
        $item =
            PelacakanBatchItem::findOrFail(
                $this->batchItemId
            );


        /*
         * Sudah terminal.
         */
        if (
            in_array(
                $item->enrichment_status,
                [
                    PelacakanBatchItem::ENRICHMENT_TERIDENTIFIKASI,
                    PelacakanBatchItem::ENRICHMENT_PERLU_VERIFIKASI,
                    PelacakanBatchItem::ENRICHMENT_TIDAK_DITEMUKAN,
                ],
                true
            )
        ) {
            return;
        }


        $service->processItem(
            $item
        );
    }


    public function failed(
        ?Throwable $exception
    ): void {
        $item =
            PelacakanBatchItem::find(
                $this->batchItemId
            );


        if (
            ! $item
        ) {
            return;
        }


        app(
            AutoEnrichmentService::class
        )->markFailed(
            $item,
            $exception?->getMessage()
        );
    }
}