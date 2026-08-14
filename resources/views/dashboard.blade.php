<x-layouts.app-tracer>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-900">
            Dashboard Tracer Alumni
        </h2>

        <p class="text-slate-600 mt-1">
            Ringkasan data alumni, progres Project 4, dan status pelacakan.
        </p>
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">
                Total Alumni
            </p>

            <h3 class="text-3xl font-bold text-slate-900">
                {{ number_format($totalAlumni, 0, ',', '.') }}
            </h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">
                Coverage Project 4
            </p>

            <h3 class="text-3xl font-bold text-blue-600">
                {{ number_format($coverage, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">
                {{ number_format($coveragePersen, 2, ',', '.') }}%
                dari seluruh alumni
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">
                Completeness ≥ 4 Field
            </p>

            <h3 class="text-3xl font-bold text-emerald-600">
                {{ number_format($completenessEmpat, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">
                {{ number_format($completenessPersen, 2, ',', '.') }}%
                dari alumni yang memiliki data Project 4
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">
                Belum Punya Data Project 4
            </p>

            <h3 class="text-3xl font-bold text-red-600">
                {{ number_format($belumPunyaDataProject4, 0, ',', '.') }}
            </h3>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-start justify-between gap-4 mb-5">
                <div>
                    <h3 class="text-xl font-semibold text-slate-900">
                        Target Coverage Project 4
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        Progres terhadap target internal 115.000 alumni.
                    </p>
                </div>

                <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">
                    {{ number_format($progressTargetAman, 2, ',', '.') }}%
                </span>
            </div>

            <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden">
                <div class="bg-blue-600 h-4 rounded-full"
                     style="width: {{ $progressTargetAman }}%">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 mt-6">
                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">
                        Target Rubrik Tertinggi
                    </p>

                    <p class="text-2xl font-bold text-slate-900 mt-1">
                        {{ number_format($targetCoverageRubrik, 0, ',', '.') }}
                    </p>

                    <p class="text-sm text-slate-500 mt-2">
                        Sisa:
                        {{ number_format($sisaTargetRubrik, 0, ',', '.') }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">
                    <p class="text-sm text-slate-500">
                        Target Aman Internal
                    </p>

                    <p class="text-2xl font-bold text-blue-600 mt-1">
                        {{ number_format($targetCoverageAman, 0, ',', '.') }}
                    </p>

                    <p class="text-sm text-slate-500 mt-2">
                        Sisa:
                        {{ number_format($sisaTargetAman, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-xl font-semibold text-slate-900 mb-5">
                Status Pelacakan
            </h3>

            <div class="space-y-4">
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 border border-slate-200 p-4">
                    <span class="text-slate-700">
                        Belum Dilacak
                    </span>

                    <span class="font-bold text-slate-900">
                        {{ number_format($belumDilacak, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-amber-50 border border-amber-200 p-4">
                    <span class="text-amber-700">
                        Perlu Verifikasi
                    </span>

                    <span class="font-bold text-amber-700">
                        {{ number_format($perluVerifikasi, 0, ',', '.') }}
                    </span>
                </div>

                <div class="flex items-center justify-between rounded-2xl bg-emerald-50 border border-emerald-200 p-4">
                    <span class="text-emerald-700">
                        Teridentifikasi
                    </span>

                    <span class="font-bold text-emerald-700">
                        {{ number_format($terverifikasi, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            <div class="mt-5 rounded-2xl border border-violet-200 bg-violet-50 p-4">
                <p class="text-sm font-semibold text-violet-800">
                    Accuracy
                </p>

                <p class="text-sm text-violet-700 mt-1">
                    Belum dihitung otomatis. Accuracy akan dihitung dari audit random 500 alumni.
                </p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-slate-900">
                    Alumni Terbaru
                </h3>

                <a href="{{ route('alumni.index') }}"
                   class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-medium">
                    Lihat Semua
                </a>
            </div>

            @if($alumniTerbaru->count())
                <div class="space-y-4">
                    @foreach($alumniTerbaru as $alumni)
                        <div class="border border-slate-200 rounded-2xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                            <div>
                                <h4 class="font-semibold text-slate-900">
                                    {{ $alumni->nama }}
                                </h4>

                                <p class="text-sm text-slate-600">
                                    NIM:
                                    {{ $alumni->nim ?: '-' }}
                                </p>

                                <p class="text-sm text-slate-600">
                                    Tempat Bekerja:
                                    {{ $alumni->tempat_bekerja ?: '-' }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="inline-block px-3 py-1 rounded-full text-sm bg-slate-100">
                                    {{ $alumni->status_verifikasi ?: 'Belum Dilacak' }}
                                </span>

                                <a href="{{ route('alumni.show', ['alumni' => $alumni->id]) }}"
                                   class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                                    Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-6 text-slate-600">
                    Belum ada data alumni.
                </div>
            @endif
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-xl font-semibold text-slate-900 mb-4">
                Aksi Cepat
            </h3>

            <div class="space-y-3">
                <a href="{{ route('alumni.create') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">
                    + Tambah Alumni
                </a>

                <a href="{{ route('alumni.import.form') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                    Import Excel
                </a>

                <a href="{{ route('alumni.export.excel') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-medium">
                    Export Excel
                </a>

                <a href="{{ route('alumni.export.download') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-medium">
                    Download Hasil Export
                </a>

                <a href="{{ route('alumni.index') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                    Kelola Data Alumni
                </a>
            </div>
        </div>
    </div>
</x-layouts.app-tracer>