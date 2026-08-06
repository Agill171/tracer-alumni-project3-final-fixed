<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Services\PelacakanQueryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PelacakanQueryController extends Controller
{
    public function store(Request $request, Alumni $alumni, PelacakanQueryService $service)
    {
        $sourceKeys = array_keys($service->availableSources());

        $validated = $request->validate([
            'sources' => ['required', 'array', 'min:1'],
            'sources.*' => ['string', Rule::in($sourceKeys)],
        ]);

        $generated = $service->generate(
            $alumni,
            $validated['sources'],
            $request->user()->id
        );

        return redirect()->route('alumni.show', $alumni)
            ->with('success', $generated->count().' tautan pencarian berhasil disiapkan dan disimpan sebagai jejak query.');
    }
}
