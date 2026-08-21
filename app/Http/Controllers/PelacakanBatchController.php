<?php

namespace App\Http\Controllers;

use App\Jobs\DispatchPelacakanBatch;
use App\Models\PelacakanBatch;
use App\Services\PelacakanBatchService;
use App\Services\PelacakanQueryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class PelacakanBatchController extends Controller
{
    public function index(
        PelacakanQueryService $queryService,
        PelacakanBatchService $batchService
    ) {
        $batches = PelacakanBatch::query()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('pelacakan-batches.index', [
            'batches' => $batches,

            'sources' =>
                $queryService->availableSources(),

            'totalTanpaDataProject4' =>
                $batchService->countWithoutAnyProject4(),

            'totalTersediaBatch' =>
                $batchService->countAvailableForBatch(),
        ]);
    }

    public function show(
        PelacakanBatch $batch,
        PelacakanQueryService $queryService
    ) {
        $batch->load('user');

        $items = $batch->items()
            ->with([
                'alumni' => function ($query) {
                    $query->with([
                        'queryPelacakans' => function ($queryPelacakan) {
                            $queryPelacakan
                                ->orderBy('prioritas')
                                ->orderBy('id');
                        },

                        'hasilPelacakans' => function ($hasilPelacakan) {
                            $hasilPelacakan
                                ->latest('tanggal_ditemukan')
                                ->latest('id');
                        },
                    ]);
                },
            ])
            ->orderBy('id')
            ->paginate(25);

        return view('pelacakan-batches.show', [
            'batch' => $batch,
            'items' => $items,
            'sources' => $queryService->availableSources(),
        ]);
    }

    public function store(
        Request $request,
        PelacakanBatchService $batchService,
        PelacakanQueryService $queryService
    ) {
        $sourceKeys = array_keys(
            $queryService->availableSources()
        );

        $validated = $request->validate([
            'nama_batch' => [
                'nullable',
                'string',
                'max:150',
            ],

            'limit' => [
                'required',
                'integer',
                'min:1',
                'max:1000',
            ],

            'sources' => [
                'required',
                'array',
                'min:1',
            ],

            'sources.*' => [
                'string',
                Rule::in($sourceKeys),
            ],
        ]);

        try {
            $batch = $batchService->createBatch(
                limit: (int) $validated['limit'],
                sourceKeys: $validated['sources'],
                userId: $request->user()->id,
                namaBatch: $validated['nama_batch'] ?? null
            );

            DispatchPelacakanBatch::dispatch(
                $batch->id
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('pelacakan-batches.index')
                ->withErrors([
                    'batch' => $exception->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(
                'pelacakan-batches.show',
                $batch
            )
            ->with(
                'success',
                "Batch {$batch->nama_batch} berhasil dibuat. "
                ."Sebanyak {$batch->total_items} alumni masuk antrean penyiapan query."
            );
    }
}