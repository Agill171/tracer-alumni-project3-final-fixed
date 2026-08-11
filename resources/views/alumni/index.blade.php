<x-layouts.app-tracer>
    <x-slot:title>Data Alumni</x-slot:title>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">Data Alumni</h2>
            <p class="text-slate-600 mt-1">Kelola profil target dan riwayat pelacakan alumni.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('alumni.create') }}"
               class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">
                + Tambah Alumni
            </a>

            <a href="{{ route('alumni.import.form') }}"
               class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                Import Excel
            </a>

            <a href="{{ route('alumni.export.excel') }}"
               class="px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-medium">
                Export Excel
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('alumni.index') }}"
          class="mb-6 bg-white rounded-2xl shadow-sm border border-slate-200 p-5 grid md:grid-cols-[1fr_280px_auto] gap-3">
        <div>
            <label for="q" class="sr-only">Kata kunci</label>
            <input id="q"
                   type="search"
                   name="q"
                   value="{{ request('q') }}"
                   placeholder="Cari nama, NIM, prodi, atau tempat bekerja..."
                   class="w-full rounded-xl border border-slate-300 px-4 py-3">
        </div>

        <div>
            <label for="status" class="sr-only">Status</label>
            <select id="status"
                    name="status"
                    class="w-full rounded-xl border border-slate-300 px-4 py-3">
                <option value="">Semua status</option>

                @foreach($statusOptions as $status)
                    <option value="{{ $status }}"
                            @selected(request('status') === $status)>
                        {{ $status }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit"
                    class="px-5 py-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-medium">
                Filter
            </button>

            @if(request()->hasAny(['q', 'status']))
                <a href="{{ route('alumni.index') }}"
                   class="px-4 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 font-medium">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($alumnis->count())
        <div class="grid gap-5">
            @foreach($alumnis as $alumni)
                @php
                    $statusClass = match($alumni->status_verifikasi) {
                        \App\Models\Alumni::STATUS_TERIDENTIFIKASI => 'bg-emerald-100 text-emerald-700',
                        \App\Models\Alumni::STATUS_PERLU_VERIFIKASI => 'bg-amber-100 text-amber-700',
                        \App\Models\Alumni::STATUS_TIDAK_DITEMUKAN => 'bg-red-100 text-red-700',
                        default => 'bg-slate-100 text-slate-700',
                    };
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                        <div class="space-y-2">
                            <h3 class="text-xl font-semibold text-slate-900">
                                {{ $alumni->nama }}
                            </h3>

                            <p>
                                <span class="font-medium">NIM:</span>
                                {{ $alumni->nim ?: '-' }}
                            </p>

                            <p>
                                <span class="font-medium">Program Studi:</span>
                                {{ $alumni->prodi ?: '-' }}
                            </p>

                            <p>
                                <span class="font-medium">Tempat Bekerja:</span>
                                {{ $alumni->tempat_bekerja ?: '-' }}
                            </p>

                            <p>
                                <span class="font-medium">Status:</span>

                                <span class="inline-block px-3 py-1 rounded-full text-sm {{ $statusClass }}">
                                    {{ $alumni->status_verifikasi ?: \App\Models\Alumni::STATUS_BELUM_DILACAK }}
                                </span>
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('alumni.show', $alumni) }}"
                               class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium">
                                Detail & Pelacakan
                            </a>

                            <a href="{{ route('alumni.edit', $alumni) }}"
                               class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium">
                                Edit
                            </a>

                            <form action="{{ route('alumni.destroy', $alumni) }}"
                                  method="POST"
                                  onsubmit="return confirm('Yakin ingin menghapus data alumni beserta seluruh jejak pelacakannya?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $alumnis->links() }}
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-8 text-center">
            <p class="text-lg font-medium text-slate-700">
                Data alumni tidak ditemukan.
            </p>

            <p class="text-slate-500 mt-2">
                Ubah filter, tambahkan data alumni, atau lakukan import Excel.
            </p>
        </div>
    @endif
</x-layouts.app-tracer>