<x-layouts.app-tracer>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="mb-8">
        <h2 class="text-3xl font-bold text-slate-900">Dashboard Tracer Alumni</h2>
        <p class="text-slate-600 mt-1">Ringkasan data alumni dan status pelacakan.</p>
    </div>

    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">Total Alumni</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ $totalAlumni }}</h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">Belum Dilacak</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ $belumDilacak }}</h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">Perlu Verifikasi</p>
            <h3 class="text-3xl font-bold text-amber-500">{{ $perluVerifikasi }}</h3>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <p class="text-sm text-slate-500 mb-2">Teridentifikasi</p>
            <h3 class="text-3xl font-bold text-emerald-600">{{ $terverifikasi }}</h3>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mb-8">
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-slate-900">Alumni Terbaru</h3>
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
                                <h4 class="font-semibold text-slate-900">{{ $alumni->nama }}</h4>
                                <p class="text-sm text-slate-600">NIM: {{ $alumni->nim ?: '-' }}</p>
                                <p class="text-sm text-slate-600">Tempat Bekerja: {{ $alumni->tempat_bekerja ?: '-' }}</p>
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
            <h3 class="text-xl font-semibold text-slate-900 mb-4">Aksi Cepat</h3>

            <div class="space-y-3">
                <a href="{{ route('alumni.create') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">
                    + Tambah Alumni
                </a>

                <a href="{{ route('alumni.import.form') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                    Import Excel
                </a>

                <a href="{{ route('alumni.index') }}"
                   class="block w-full text-center px-4 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                    Kelola Data Alumni
                </a>
            </div>
        </div>
    </div>
</x-layouts.app-tracer>