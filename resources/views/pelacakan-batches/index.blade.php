<x-layouts.app-tracer>
    <x-slot:title>Batch Pelacakan</x-slot:title>

    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-900">
            Batch Pelacakan Project 4
        </h2>

        <p class="text-slate-600 mt-1">
            Siapkan query profiling alumni secara bertahap sebelum
            melakukan verifikasi evidence Project 4.
        </p>
    </div>


    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- STATUS PIPELINE --}}
    <div class="grid md:grid-cols-2 gap-5 mb-8">

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500">
                Belum Punya Data Project 4
            </p>

            <p class="text-3xl font-bold text-red-600 mt-2">
                {{ number_format($totalTanpaDataProject4, 0, ',', '.') }}
            </p>

            <p class="text-sm text-slate-500 mt-2">
                Alumni dengan seluruh field Project 4 masih kosong.
            </p>

        </div>


        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500">
                Tersedia untuk Batch Baru
            </p>

            <p class="text-3xl font-bold text-blue-600 mt-2">
                {{ number_format($totalTersediaBatch, 0, ',', '.') }}
            </p>

            <p class="text-sm text-slate-500 mt-2">
                Belum pernah berhasil disiapkan query pelacakannya.
            </p>

        </div>

    </div>


    <div class="grid xl:grid-cols-[420px_1fr] gap-6 mb-8">

        {{-- FORM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <h3 class="text-xl font-semibold text-slate-900 mb-2">
                Buat Batch Baru
            </h3>

            <p class="text-sm text-slate-500 mb-6">
                Untuk pengujian lokal, mulai dari 10–25 alumni.
                Setelah stabil dapat dinaikkan sampai 1.000 alumni per batch.
            </p>


            <form
                method="POST"
                action="{{ route('pelacakan-batches.store') }}"
                class="space-y-5">

                @csrf


                <div>
                    <label
                        for="nama_batch"
                        class="block text-sm font-medium text-slate-700 mb-2">

                        Nama Batch

                    </label>

                    <input
                        type="text"
                        id="nama_batch"
                        name="nama_batch"
                        value="{{ old('nama_batch') }}"
                        placeholder="Contoh: Batch Coverage 001"
                        class="w-full rounded-xl border border-slate-300 px-4 py-3">
                </div>


                <div>
                    <label
                        for="limit"
                        class="block text-sm font-medium text-slate-700 mb-2">

                        Jumlah Alumni

                    </label>

                    <input
                        type="number"
                        id="limit"
                        name="limit"
                        value="{{ old('limit', 25) }}"
                        min="1"
                        max="1000"
                        required
                        class="w-full rounded-xl border border-slate-300 px-4 py-3">

                    <p class="text-xs text-slate-500 mt-2">
                        Maksimal 1.000 alumni per batch.
                    </p>
                </div>


                <div>

                    <p class="block text-sm font-medium text-slate-700 mb-3">
                        Sumber Pelacakan
                    </p>


                    <div class="space-y-3">

                        @foreach($sources as $key => $source)

                            @php
                                $defaultSources = [
                                    'google',
                                    'linkedin',
                                    'company_web',
                                ];

                                $selectedSources = old(
                                    'sources',
                                    $defaultSources
                                );
                            @endphp


                            <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer">

                                <input
                                    type="checkbox"
                                    name="sources[]"
                                    value="{{ $key }}"
                                    @checked(
                                        in_array(
                                            $key,
                                            $selectedSources,
                                            true
                                        )
                                    )
                                    class="mt-1 rounded border-slate-300">


                                <span>

                                    <span class="block font-medium text-slate-800">
                                        {{ $source['label'] ?? $key }}
                                    </span>

                                    <span class="block text-xs text-slate-500 mt-1">
                                        Prioritas:
                                        {{ $source['priority'] ?? 99 }}
                                    </span>

                                </span>

                            </label>

                        @endforeach

                    </div>

                </div>


                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">

                    <p class="text-sm font-semibold text-blue-800">
                        Query Profiling
                    </p>

                    <p class="text-sm text-blue-700 mt-1">
                        Sistem memprioritaskan kombinasi Nama,
                        Program Studi, Fakultas jika tersedia,
                        dan Universitas Muhammadiyah Malang.
                    </p>

                </div>


                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">

                    <p class="text-sm font-semibold text-amber-800">
                        Penting
                    </p>

                    <p class="text-sm text-amber-700 mt-1">
                        Query Siap hanya berarti tautan pencarian sudah tersedia.
                        Coverage baru bertambah setelah data Project 4 yang benar-benar
                        ditemukan dan diverifikasi diisi pada alumni.
                    </p>

                </div>


                <button
                    type="submit"
                    onclick="return confirm('Buat dan jalankan batch pelacakan ini?');"
                    class="w-full px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold">

                    Buat & Jalankan Batch

                </button>

            </form>

        </div>


        {{-- RIWAYAT --}}
        <div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

                <div>

                    <h3 class="text-xl font-semibold text-slate-900">
                        Riwayat Batch
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        Batch berstatus Query Siap tidak menghalangi
                        pembuatan batch berikutnya.
                    </p>

                </div>


                <a
                    href="{{ route('dashboard') }}"
                    class="inline-flex justify-center px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-medium">

                    Kembali ke Dashboard

                </a>

            </div>


            @if($batches->count())

                <div class="space-y-5">

                    @foreach($batches as $batch)

                        @php
                            $progress = $batch->total_items > 0
                                ? min(
                                    100,
                                    round(
                                        (
                                            $batch->processed_items
                                            / $batch->total_items
                                        ) * 100,
                                        2
                                    )
                                )
                                : 0;

                            $statusClass = match($batch->status) {
                                \App\Models\PelacakanBatch::STATUS_SELESAI =>
                                    'bg-emerald-100 text-emerald-700',

                                \App\Models\PelacakanBatch::STATUS_QUERY_SIAP =>
                                    'bg-violet-100 text-violet-700',

                                \App\Models\PelacakanBatch::STATUS_DIPROSES =>
                                    'bg-blue-100 text-blue-700',

                                \App\Models\PelacakanBatch::STATUS_GAGAL =>
                                    'bg-red-100 text-red-700',

                                default =>
                                    'bg-slate-100 text-slate-700',
                            };
                        @endphp


                        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">

                                <div>

                                    <div class="flex flex-wrap items-center gap-3">

                                        <h4 class="text-lg font-semibold text-slate-900">
                                            {{ $batch->nama_batch }}
                                        </h4>

                                        <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                                            {{ $batch->status }}
                                        </span>

                                    </div>


                                    <p class="text-sm text-slate-500 mt-2">
                                        Dibuat:
                                        {{ $batch->created_at?->format('d-m-Y H:i:s') }}
                                    </p>

                                    <p class="text-sm text-slate-500 mt-1">
                                        Oleh:
                                        {{ $batch->user?->name ?? 'Sistem' }}
                                    </p>

                                </div>


                                <div class="text-left lg:text-right">

                                    <p class="text-sm text-slate-500">
                                        Progress Penyiapan Query
                                    </p>

                                    <p class="text-2xl font-bold text-slate-900">
                                        {{ number_format($progress, 2, ',', '.') }}%
                                    </p>

                                </div>

                            </div>


                            <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden mb-5">

                                <div
                                    class="h-3 rounded-full bg-blue-600"
                                    style="width: {{ $progress }}%">
                                </div>

                            </div>


                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">

                                <div class="rounded-xl bg-slate-50 border border-slate-200 p-4">

                                    <p class="text-xs text-slate-500">
                                        Total
                                    </p>

                                    <p class="text-xl font-bold text-slate-900 mt-1">
                                        {{ number_format($batch->total_items, 0, ',', '.') }}
                                    </p>

                                </div>


                                <div class="rounded-xl bg-blue-50 border border-blue-200 p-4">

                                    <p class="text-xs text-blue-600">
                                        Diproses
                                    </p>

                                    <p class="text-xl font-bold text-blue-700 mt-1">
                                        {{ number_format($batch->processed_items, 0, ',', '.') }}
                                    </p>

                                </div>


                                <div class="rounded-xl bg-violet-50 border border-violet-200 p-4">

                                    <p class="text-xs text-violet-600">
                                        Query Siap
                                    </p>

                                    <p class="text-xl font-bold text-violet-700 mt-1">
                                        {{ number_format($batch->success_items, 0, ',', '.') }}
                                    </p>

                                </div>


                                <div class="rounded-xl bg-red-50 border border-red-200 p-4">

                                    <p class="text-xs text-red-600">
                                        Gagal
                                    </p>

                                    <p class="text-xl font-bold text-red-700 mt-1">
                                        {{ number_format($batch->failed_items, 0, ',', '.') }}
                                    </p>

                                </div>

                            </div>


                            <div class="flex flex-wrap gap-3">

                                <a
                                    href="{{ route('pelacakan-batches.show', $batch) }}"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">

                                    Buka Detail Batch

                                </a>

                            </div>


                            @if(
                                $batch->status ===
                                \App\Models\PelacakanBatch::STATUS_QUERY_SIAP
                            )

                                <div class="mt-4 rounded-xl border border-violet-200 bg-violet-50 p-3 text-sm text-violet-700">

                                    Semua query batch sudah siap.
                                    Buka Detail Batch untuk mulai memeriksa alumni,
                                    membuka sumber pencarian, dan mencatat evidence.

                                </div>

                            @endif


                            @if($batch->catatan)

                                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-700">
                                    {{ $batch->catatan }}
                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>


                <div class="mt-6">
                    {{ $batches->links() }}
                </div>

            @else

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">

                    <p class="text-lg font-semibold text-slate-700">
                        Belum ada batch pelacakan.
                    </p>

                    <p class="text-slate-500 mt-2">
                        Buat batch pertama menggunakan form di sebelah kiri.
                    </p>

                </div>

            @endif

        </div>

    </div>

</x-layouts.app-tracer>