<x-layouts.app-tracer>
    <x-slot:title>Review Kandidat Auto Enrichment</x-slot:title>


    <div class="max-w-6xl mx-auto">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">

            <div>

                <h2 class="text-3xl font-bold text-slate-900">
                    Review Kandidat
                </h2>

                <p class="text-slate-600 mt-1">

                    Kandidat Auto Enrichment untuk

                    <strong>
                        {{ $alumni?->nama ?: '-' }}
                    </strong>

                </p>

            </div>


            <a
                href="{{ route(
                    'pelacakan-batches.show',
                    [
                        'batch' =>
                            $batch->id,
                    ]
                ) }}"
                class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">

                ← Kembali ke Detail Batch

            </a>

        </div>


        <div class="bg-white rounded-2xl border border-slate-200 p-6 mb-6">

            <div class="grid md:grid-cols-4 gap-4">

                <div>

                    <p class="text-sm text-slate-500">
                        Nama
                    </p>

                    <p class="font-semibold">
                        {{ $alumni?->nama ?: '-' }}
                    </p>

                </div>


                <div>

                    <p class="text-sm text-slate-500">
                        NIM
                    </p>

                    <p class="font-semibold">
                        {{ $alumni?->nim ?: '-' }}
                    </p>

                </div>


                <div>

                    <p class="text-sm text-slate-500">
                        Prodi
                    </p>

                    <p class="font-semibold">
                        {{ $alumni?->prodi ?: '-' }}
                    </p>

                </div>


                <div>

                    <p class="text-sm text-slate-500">
                        Status Enrichment
                    </p>

                    <p class="font-semibold">
                        {{ $item->enrichment_status ?: '-' }}
                    </p>

                </div>

            </div>

        </div>


        <div class="space-y-5">

            @forelse($candidates as $candidate)

                @php
                    $signals =
                        $candidate->identity_signals
                        ?? [];

                    $project4 =
                        $candidate->project4_candidates
                        ?? [];

                    $scoreClass =
                        match(true) {

                            $candidate->confidence_score >= 80 =>
                                'bg-emerald-100 text-emerald-700',

                            $candidate->confidence_score >= 50 =>
                                'bg-amber-100 text-amber-700',

                            default =>
                                'bg-red-100 text-red-700',
                        };
                @endphp


                <article class="bg-white rounded-2xl border border-slate-200 p-6">

                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                        <div class="min-w-0">

                            <h3 class="text-lg font-semibold text-slate-900">
                                {{ $candidate->title ?: 'Tanpa Judul' }}
                            </h3>

                            <p class="text-sm text-slate-500 mt-1">
                                {{ $candidate->domain ?: '-' }}
                                ·
                                {{ $candidate->provider }}
                            </p>

                        </div>


                        <span class="shrink-0 px-3 py-1 rounded-full text-sm font-semibold {{ $scoreClass }}">

                            Confidence
                            {{ $candidate->confidence_score }}%

                        </span>

                    </div>


                    @if($candidate->snippet)

                        <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-4">

                            <p class="text-sm text-slate-700">
                                {{ $candidate->snippet }}
                            </p>

                        </div>

                    @endif


                    <div class="grid md:grid-cols-2 gap-5 mt-5">

                        <div>

                            <p class="font-semibold text-slate-800 mb-2">
                                Sinyal Identitas
                            </p>

                            <div class="flex flex-wrap gap-2">

                                @foreach([
                                    'nama' => 'Nama',
                                    'afiliasi' => 'Afiliasi',
                                    'timeline' => 'Timeline',
                                    'bidang' => 'Bidang',
                                    'nim' => 'NIM',
                                    'kampus' => 'Kampus',
                                ] as $key => $label)

                                    <span class="px-3 py-1 rounded-full text-xs
                                        {{
                                            data_get(
                                                $signals,
                                                $key
                                            )
                                                ? 'bg-emerald-100 text-emerald-700'
                                                : 'bg-slate-100 text-slate-500'
                                        }}">

                                        {{
                                            data_get(
                                                $signals,
                                                $key
                                            )
                                                ? '✓'
                                                : '–'
                                        }}

                                        {{ $label }}

                                    </span>

                                @endforeach

                            </div>

                        </div>


                        <div>

                            <p class="font-semibold text-slate-800 mb-2">
                                Kandidat Project 4
                            </p>


                            @if($project4)

                                <div class="space-y-1 text-sm">

                                    @foreach($project4 as $field => $value)

                                        <p class="break-all">

                                            <strong>
                                                {{ $field }}:
                                            </strong>

                                            {{ $value }}

                                        </p>

                                    @endforeach

                                </div>

                            @else

                                <p class="text-sm text-slate-500">
                                    Belum ada field Project 4 yang dapat diekstrak langsung.
                                </p>

                            @endif

                        </div>

                    </div>


                    <div class="flex flex-wrap gap-3 mt-5">

                        <a
                            href="{{ $candidate->url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">

                            Buka Evidence ↗

                        </a>


                        <a
                            href="{{ route(
                                'pelacakan.create',
                                [
                                    'alumni' =>
                                        $alumni->id,

                                    'query' =>
                                        $candidate
                                            ->pelacakanQuery
                                            ?->query,

                                    'source' =>
                                        $candidate->source_key,

                                    'from_batch' =>
                                        $batch->id,
                                ]
                            ) }}"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium">

                            Verifikasi Manual

                        </a>

                    </div>

                </article>

            @empty

                <div class="rounded-2xl bg-white border border-slate-200 p-8 text-center text-slate-500">

                    Tidak ada kandidat yang tersimpan.

                </div>

            @endforelse

        </div>

    </div>

</x-layouts.app-tracer>