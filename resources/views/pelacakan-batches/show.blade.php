<x-layouts.app-tracer>
    <x-slot:title>Detail Batch Pelacakan</x-slot:title>

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="mb-8">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
            <div>
                <h2 class="text-3xl font-bold text-slate-900">
                    {{ $batch->nama_batch }}
                </h2>
                <p class="text-slate-600 mt-1">
                    Detail alumni, query pencarian, dan progres enrichment Project 4.
                </p>
            </div>
            <a href="{{ route('pelacakan-batches.index') }}" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                ← Kembali ke Batch
            </a>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- FLASH --}}
    {{-- ========================================================= --}}

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">
            <p class="font-semibold mb-2">Proses belum dapat dijalankan:</p>
            <ul class="list-disc pl-5 space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- INFO BATCH --}}
    {{-- ========================================================= --}}

    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-slate-500">Status</p>
                <p class="text-xl font-bold text-slate-900 mt-1">{{ $batch->status }}</p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Total Alumni</p>
                <p class="text-xl font-bold text-slate-900 mt-1">
                    {{ number_format($batch->total_items, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Query Siap</p>
                <p class="text-xl font-bold text-violet-600 mt-1">
                    {{ number_format($batch->success_items, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <p class="text-sm text-slate-500">Gagal Query</p>
                <p class="text-xl font-bold text-red-600 mt-1">
                    {{ number_format($batch->failed_items, 0, ',', '.') }}
                </p>
            </div>
        </div>
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">
            <p class="text-sm text-amber-800">
                <strong>Query Siap ≠ data ditemukan.</strong>
                Coverage Project 4 hanya meningkat setelah evidence benar-benar terverifikasi dan minimal satu kategori Project 4 terisi pada alumni.
            </p>
        </div>
    </div>

    {{-- ========================================================= --}}
    {{-- AUTO ENRICHMENT (DIUBAH AGAR MEMBACA ENV LANGSUNG) --}}
    {{-- ========================================================= --}}

    @php
        // BACA LANGSUNG ENV UNTUK MENGHINDARI CACHE LAMA
        $enrichmentEnabledRaw = env('AUTO_ENRICHMENT_ENABLED', config('enrichment.enabled', false));
        $enrichmentEnabled = filter_var($enrichmentEnabledRaw, FILTER_VALIDATE_BOOLEAN);

        $tavilyKey = env('TAVILY_API_KEY', config('enrichment.tavily.api_key'));
        $tavilyReady = filled($tavilyKey);

        $enrichmentRunning = $batch->status === \App\Models\PelacakanBatch::STATUS_ENRICHMENT;
        $enrichmentCompleted = (int) $batch->total_items > 0 && (int) ($batch->enrichment_processed_items ?? 0) >= (int) $batch->total_items;

        // DEBUG: Tampilkan status Environment di layar
        $debugEnabled = $enrichmentEnabled ? 'TRUE' : 'FALSE';
        $debugKey = $tavilyReady ? 'ADA' : 'KOSONG';
    @endphp

    {{-- KOTAK DEBUG (Hapus setelah tombol muncul) --}}
    <div class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 text-sm font-bold rounded">
        [DEBUG] Env: {{ $debugEnabled }} | Key: {{ $debugKey }}
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-violet-200 p-6 mb-8">
        <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">
            <div class="max-w-3xl">
                <div class="flex flex-wrap items-center gap-3">
                    <h3 class="text-xl font-semibold text-slate-900">Auto Enrichment Project 4</h3>
                    @if($enrichmentEnabled && $tavilyReady)
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Tavily Ready</span>
                    @else
                        <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Belum Siap</span>
                    @endif
                </div>
                <p class="text-sm text-slate-600 mt-2">
                    Sistem mengambil query alumni yang sudah disiapkan, menjalankan pencarian melalui Tavily Search API, mencocokkan identitas kandidat, kemudian mengelompokkan hasil menjadi Teridentifikasi, Perlu Verifikasi, Tidak Ditemukan, atau Gagal.
                </p>
                <div class="mt-4 flex flex-wrap gap-2 text-xs">
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700">Provider: <strong>{{ strtoupper(env('AUTO_ENRICHMENT_PROVIDER', config('enrichment.provider', 'tavily'))) }}</strong></span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700">Max Query: <strong>{{ env('AUTO_ENRICHMENT_MAX_QUERIES', config('enrichment.max_queries_per_alumni', 4)) }}</strong></span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700">Hasil / Query: <strong>{{ env('AUTO_ENRICHMENT_RESULTS_PER_QUERY', config('enrichment.results_per_query', 5)) }}</strong></span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700">Strong: <strong>≥ {{ env('AUTO_ENRICHMENT_STRONG_THRESHOLD', config('enrichment.strong_threshold', 80)) }}%</strong></span>
                    <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700">Review: <strong>≥ {{ env('AUTO_ENRICHMENT_REVIEW_THRESHOLD', config('enrichment.review_threshold', 50)) }}%</strong></span>
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- BUTTON --}}
            {{-- ================================================= --}}
            <div class="shrink-0">
                @if(!$enrichmentEnabled)
                    <button type="button" disabled class="px-5 py-3 rounded-xl bg-slate-300 text-slate-600 font-semibold cursor-not-allowed">
                        Auto Enrichment Nonaktif
                    </button>
                    <p class="text-xs text-red-600 mt-2 max-w-xs">
                        Cek ENV: AUTO_ENRICHMENT_ENABLED harus "true" di Web Service Railway.
                    </p>
                @elseif(!$tavilyReady)
                    <button type="button" disabled class="px-5 py-3 rounded-xl bg-slate-300 text-slate-600 font-semibold cursor-not-allowed">
                        Tavily API Key Belum Ada
                    </button>
                    <p class="text-xs text-red-600 mt-2 max-w-xs">
                        Cek ENV: TAVILY_API_KEY harus diisi di Web Service Railway.
                    </p>
                @elseif($enrichmentCompleted)
                    <button type="button" disabled class="px-5 py-3 rounded-xl bg-emerald-100 text-emerald-700 font-semibold cursor-not-allowed">
                        ✓ Auto Enrichment Selesai
                    </button>
                    <p class="text-xs text-emerald-600 mt-2 max-w-xs">Semua item pada batch ini sudah diproses.</p>
                @elseif($enrichmentRunning)
                    <button type="button" disabled class="px-5 py-3 rounded-xl bg-blue-100 text-blue-700 font-semibold cursor-not-allowed">
                        Auto Enrichment Diproses...
                    </button>
                @else
                    <form action="{{ route('pelacakan-batches.enrichment.start', $batch) }}" method="POST" onsubmit="return confirm('Mulai Auto Enrichment untuk batch ini? Tavily API credits akan digunakan.');">
                        @csrf
                        <button type="submit" class="px-5 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-semibold shadow-sm">
                            🚀 Mulai Auto Enrichment
                        </button>
                    </form>
                    <p class="text-xs text-slate-500 mt-2 max-w-xs">
                        Setelah ditekan, jalankan queue worker khusus local-enrichment.
                    </p>
                @endif
            </div>
        </div>

        {{-- ===================================================== --}}
        {{-- PROGRESS --}}
        {{-- ===================================================== --}}

        <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-3 mt-6">
            <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                <p class="text-xs text-blue-600">Diproses</p>
                <p class="text-2xl font-bold text-blue-700 mt-1">{{ number_format($batch->enrichment_processed_items ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                <p class="text-xs text-emerald-600">Teridentifikasi</p>
                <p class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($batch->identified_items ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-xs text-amber-600">Perlu Verifikasi</p>
                <p class="text-2xl font-bold text-amber-700 mt-1">{{ number_format($batch->review_items ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <p class="text-xs text-slate-600">Tidak Ditemukan</p>
                <p class="text-2xl font-bold text-slate-700 mt-1">{{ number_format($batch->not_found_items ?? 0, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-xl border border-red-200 bg-red-50 p-4">
                <p class="text-xs text-red-600">Gagal Enrichment</p>
                <p class="text-2xl font-bold text-red-700 mt-1">{{ number_format($batch->enrichment_failed_items ?? 0, 0, ',', '.') }}</p>
            </div>
        </div>

        @if($batch->enrichment_started_at || $batch->enrichment_finished_at)
            <div class="grid md:grid-cols-2 gap-3 mt-4 text-sm text-slate-500">
                <p>Mulai Enrichment: <strong class="text-slate-700">{{ $batch->enrichment_started_at?->format('d-m-Y H:i:s') ?? '-' }}</strong></p>
                <p>Selesai Enrichment: <strong class="text-slate-700">{{ $batch->enrichment_finished_at?->format('d-m-Y H:i:s') ?? '-' }}</strong></p>
            </div>
        @endif
    </div>

    {{-- ========================================================= --}}
    {{-- ITEMS --}}
    {{-- ========================================================= --}}

    <div class="space-y-6">
        @forelse($items as $item)
            @php
                $alumni = $item->alumni;
                $socialMediaAda = filled($alumni?->linkedin) || filled($alumni?->instagram) || filled($alumni?->facebook) || filled($alumni?->tiktok);
                $jumlahKategori = $socialMediaAda ? 1 : 0;
                foreach(['email', 'no_hp', 'tempat_bekerja', 'alamat_bekerja', 'posisi', 'kategori_pekerjaan', 'sosmed_tempat_bekerja'] as $field) {
                    if (filled($alumni?->{$field})) { $jumlahKategori++; }
                }
                $queries = $alumni ? $alumni->queryPelacakans->filter(fn ($query) => in_array($query->sumber, $batch->sources ?? [], true)) : collect();
                $statusClass = match($item->status) {
                    \App\Models\PelacakanBatchItem::STATUS_QUERY_SIAP => 'bg-violet-100 text-violet-700',
                    \App\Models\PelacakanBatchItem::STATUS_SELESAI => 'bg-emerald-100 text-emerald-700',
                    \App\Models\PelacakanBatchItem::STATUS_DIPROSES => 'bg-blue-100 text-blue-700',
                    \App\Models\PelacakanBatchItem::STATUS_GAGAL => 'bg-red-100 text-red-700',
                    default => 'bg-slate-100 text-slate-700',
                };
                $enrichmentClass = match($item->enrichment_status) {
                    \App\Models\PelacakanBatchItem::ENRICHMENT_DIPROSES => 'bg-blue-100 text-blue-700',
                    \App\Models\PelacakanBatchItem::ENRICHMENT_TERIDENTIFIKASI => 'bg-emerald-100 text-emerald-700',
                    \App\Models\PelacakanBatchItem::ENRICHMENT_PERLU_VERIFIKASI => 'bg-amber-100 text-amber-700',
                    \App\Models\PelacakanBatchItem::ENRICHMENT_TIDAK_DITEMUKAN => 'bg-slate-200 text-slate-700',
                    \App\Models\PelacakanBatchItem::ENRICHMENT_GAGAL => 'bg-red-100 text-red-700',
                    \App\Models\PelacakanBatchItem::ENRICHMENT_MENUNGGU => 'bg-indigo-100 text-indigo-700',
                    default => 'bg-slate-100 text-slate-500',
                };
            @endphp

            <article class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div>
                        <div class="flex flex-wrap items-center gap-3">
                            <h3 class="text-xl font-semibold text-slate-900">{{ $alumni?->nama ?: 'Alumni tidak ditemukan' }}</h3>
                            <span class="px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">{{ $item->status }}</span>
                            @if($item->enrichment_status)
                                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $enrichmentClass }}">Auto: {{ $item->enrichment_status }}</span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-600 mt-2">
                            NIM: <strong>{{ $alumni?->nim ?: '-' }}</strong> · Prodi: <strong>{{ $alumni?->prodi ?: '-' }}</strong> · Lulus: <strong>{{ $alumni?->tahun_lulus ?: '-' }}</strong>
                        </p>
                    </div>
                    <div class="text-left lg:text-right">
                        <p class="text-sm text-slate-500">Completeness Project 4</p>
                        <p class="text-2xl font-bold {{ $jumlahKategori >= 4 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $jumlahKategori }} / 8</p>
                    </div>
                </div>

                @if($item->enrichment_status)
                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <p class="text-xs uppercase tracking-wide font-semibold text-slate-500">Status Auto Enrichment</p>
                                <p class="font-semibold text-slate-800 mt-1">{{ $item->enrichment_status }}</p>
                                <p class="text-xs text-slate-500 mt-1">Percobaan: {{ $item->enrichment_attempts ?? 0 }}</p>
                            </div>
                            @if($item->enrichment_status === \App\Models\PelacakanBatchItem::ENRICHMENT_PERLU_VERIFIKASI)
                                <a href="{{ route('pelacakan-batches.enrichment.review', $item) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold">Periksa Evidence</a>
                            @endif
                        </div>
                        @if($item->enrichment_error)
                            <div class="mt-3 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $item->enrichment_error }}</div>
                        @endif
                    </div>
                @endif

                <div class="mt-5">
                    <h4 class="font-semibold text-slate-900 mb-3">Query Pencarian</h4>
                    @if($queries->count())
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[900px] text-sm">
                                <thead>
                                    <tr class="bg-slate-100 text-left">
                                        <th class="p-3">Prioritas</th>
                                        <th class="p-3">Sumber</th>
                                        <th class="p-3">Query</th>
                                        <th class="p-3">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($queries as $query)
                                        <tr class="border-b border-slate-200">
                                            <td class="p-3">{{ $query->prioritas }}</td>
                                            <td class="p-3">{{ $sources[$query->sumber]['label'] ?? $query->sumber }}</td>
                                            <td class="p-3 text-slate-700">{{ $query->query }}</td>
                                            <td class="p-3">
                                                <a href="{{ $query->url_pencarian }}" target="_blank" rel="noopener noreferrer" class="inline-flex px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium">Buka Pencarian ↗</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="rounded-xl bg-slate-50 border border-slate-200 p-4 text-slate-500">Query belum tersedia.</div>
                    @endif
                </div>

                @if($alumni)
                    <div class="flex flex-wrap gap-3 mt-5">
                        <a href="{{ route('alumni.show', ['alumni' => $alumni, 'from_batch' => $batch->id]) }}" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">Detail Alumni</a>
                        <a href="{{ route('pelacakan.create', ['alumni' => $alumni, 'from_batch' => $batch->id]) }}" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium">+ Catat Evidence / Temuan</a>
                        <a href="{{ route('alumni.edit', ['alumni' => $alumni, 'from_batch' => $batch->id]) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium">Isi Data Project 4</a>
                    </div>
                @endif

                @if($item->last_error)
                    <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                        <strong>Error Query:</strong> {{ $item->last_error }}
                    </div>
                @endif
            </article>
        @empty
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center text-slate-600">Batch ini belum mempunyai item.</div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $items->links() }}
    </div>
</x-layouts.app-tracer>