<x-layouts.app-tracer>
    <x-slot:title>Detail Alumni</x-slot:title>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-slate-900">Detail Alumni</h2>
            <p class="text-slate-600 mt-1">Profil target, tautan pencarian, dan jejak bukti pelacakan.</p>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('alumni.index') }}"
               class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                ← Kembali
            </a>
            <a href="{{ route('alumni.edit', $alumni) }}"
               class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium">
                Edit Data
            </a>
            <a href="{{ route('pelacakan.create', $alumni) }}"
               class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">
                + Catat Temuan
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid lg:grid-cols-2 gap-6 mb-8">
        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-xl font-semibold mb-4">Profil Target Pencarian</h3>
            <dl class="grid grid-cols-[150px_1fr] gap-x-3 gap-y-2 text-sm md:text-base">
                <dt class="font-medium">Nama</dt><dd>{{ $alumni->nama }}</dd>
                <dt class="font-medium">NIM</dt><dd>{{ $alumni->nim ?: '-' }}</dd>
                <dt class="font-medium">Program Studi</dt><dd>{{ $alumni->prodi ?: '-' }}</dd>
                <dt class="font-medium">Angkatan</dt><dd>{{ $alumni->angkatan ?: '-' }}</dd>
                <dt class="font-medium">Tahun Lulus</dt><dd>{{ $alumni->tahun_lulus ?: '-' }}</dd>
                <dt class="font-medium">Email</dt><dd>{{ $alumni->email ?: '-' }}</dd>
                <dt class="font-medium">Nomor HP</dt><dd>{{ $alumni->no_hp ?: '-' }}</dd>
                <dt class="font-medium">Status</dt>
                <dd><span class="inline-block px-3 py-1 rounded-full text-sm bg-slate-100">{{ $alumni->status_verifikasi }}</span></dd>
            </dl>
        </section>

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-xl font-semibold mb-4">Pekerjaan dan Profil Publik</h3>
            <dl class="space-y-2 text-sm md:text-base">
                <div><dt class="font-medium inline">Tempat Bekerja:</dt> <dd class="inline">{{ $alumni->tempat_bekerja ?: '-' }}</dd></div>
                <div><dt class="font-medium inline">Alamat:</dt> <dd class="inline">{{ $alumni->alamat_bekerja ?: '-' }}</dd></div>
                <div><dt class="font-medium inline">Posisi:</dt> <dd class="inline">{{ $alumni->posisi ?: '-' }}</dd></div>
                <div><dt class="font-medium inline">Kategori:</dt> <dd class="inline">{{ $alumni->kategori_pekerjaan ?: '-' }}</dd></div>
            </dl>

            <div class="mt-5 flex flex-wrap gap-2">
                @foreach([
                    'linkedin' => 'LinkedIn',
                    'instagram' => 'Instagram',
                    'facebook' => 'Facebook',
                    'tiktok' => 'TikTok',
                    'sosmed_tempat_bekerja' => 'Situs Tempat Kerja',
                ] as $field => $label)
                    @if($alumni->{$field})
                        <a href="{{ $alumni->{$field} }}" target="_blank" rel="noopener noreferrer"
                           class="px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 text-sm font-medium">
                            {{ $label }} ↗
                        </a>
                    @endif
                @endforeach
            </div>

            @if($alumni->catatan)
                <div class="mt-5 rounded-xl bg-slate-50 border border-slate-200 p-4">
                    <p class="font-medium mb-1">Catatan</p>
                    <p class="text-slate-700 whitespace-pre-line">{{ $alumni->catatan }}</p>
                </div>
            @endif
        </section>
    </div>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5 mb-5">
            <div>
                <h3 class="text-xl font-semibold">Siapkan Query Pelacakan</h3>
                <p class="text-slate-600 mt-1">
                    Sistem membentuk query dari nama, kampus, program studi, tahun lulus, dan tempat bekerja. Tautan hanya membuka pencarian publik; sistem tidak melakukan scraping otomatis.
                </p>
            </div>
        </div>

        <form action="{{ route('pelacakan.query.store', $alumni) }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
                @foreach(config('tracer.sources') as $key => $source)
                    <label class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50 cursor-pointer">
                        <input type="checkbox" name="sources[]" value="{{ $key }}" checked
                               class="rounded border-slate-300 text-blue-600">
                        <span>
                            <span class="block font-medium">{{ $source['label'] }}</span>
                            <span class="text-xs text-slate-500">Prioritas {{ $source['priority'] }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            <button type="submit" class="px-5 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-medium">
                Buat dan Simpan Tautan Pencarian
            </button>
        </form>

        <div class="mt-6 overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm border-separate border-spacing-0">
                <thead>
                    <tr class="text-left bg-slate-100">
                        <th class="p-3 border-y border-l border-slate-200 rounded-l-xl">Prioritas</th>
                        <th class="p-3 border-y border-slate-200">Sumber</th>
                        <th class="p-3 border-y border-slate-200">Query</th>
                        <th class="p-3 border-y border-slate-200">Dibuat</th>
                        <th class="p-3 border-y border-r border-slate-200 rounded-r-xl">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alumni->queryPelacakans as $query)
                        <tr class="align-top">
                            <td class="p-3 border-b border-slate-200">{{ $query->prioritas }}</td>
                            <td class="p-3 border-b border-slate-200 font-medium">{{ data_get(config('tracer.sources'), $query->sumber.'.label', $query->sumber) }}</td>
                            <td class="p-3 border-b border-slate-200 max-w-xl break-words">{{ $query->query }}</td>
                            <td class="p-3 border-b border-slate-200">{{ optional($query->generated_at)->format('d-m-Y H:i') ?: '-' }}</td>
                            <td class="p-3 border-b border-slate-200">
                                <a href="{{ $query->url_pencarian }}" target="_blank" rel="noopener noreferrer"
                                   class="inline-block px-3 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-medium">
                                    Buka Pencarian ↗
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-6 text-center text-slate-500">Belum ada query yang disimpan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
        <div class="flex items-center justify-between gap-4 mb-5">
            <div>
                <h3 class="text-xl font-semibold">Jejak Bukti dan Hasil Verifikasi</h3>
                <p class="text-slate-600 mt-1">Setiap temuan menyimpan sumber, bukti, sinyal identitas, confidence score, dan petugas pencatat.</p>
            </div>
            <a href="{{ route('pelacakan.create', $alumni) }}"
               class="shrink-0 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">+ Catat Temuan</a>
        </div>

        @forelse ($alumni->hasilPelacakans as $pelacakan)
            @php
                $signals = $pelacakan->sinyal_identitas ?? [];
                $scoreClass = match($pelacakan->kategori_kecocokan) {
                    \App\Models\HasilPelacakan::KATEGORI_KUAT => 'bg-emerald-100 text-emerald-700',
                    \App\Models\HasilPelacakan::KATEGORI_VERIFIKASI => 'bg-amber-100 text-amber-700',
                    default => 'bg-red-100 text-red-700',
                };
            @endphp
            <article class="border border-slate-200 rounded-2xl p-5 mb-4">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="space-y-2">
                        <h4 class="text-lg font-semibold">{{ $pelacakan->judul_temuan ?: 'Temuan dari '.$pelacakan->sumber_temuan }}</h4>
                        <p><span class="font-medium">Status Alumni:</span> {{ $pelacakan->status_pelacakan }}</p>
                        <p><span class="font-medium">Sumber:</span> {{ $pelacakan->sumber_temuan ?: '-' }}</p>
                        <p><span class="font-medium">Tanggal:</span> {{ optional($pelacakan->tanggal_ditemukan)->format('d-m-Y') ?: '-' }}</p>
                        <p><span class="font-medium">Dicatat oleh:</span> {{ $pelacakan->user?->name ?: '-' }}</p>
                    </div>
                    <div class="text-left lg:text-right">
                        <span class="inline-block px-3 py-1 rounded-full text-sm font-medium {{ $scoreClass }}">
                            {{ $pelacakan->kategori_kecocokan }} · {{ $pelacakan->confidence_score }}%
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid md:grid-cols-2 gap-4">
                    <div>
                        <p class="font-medium mb-2">Sinyal Identitas</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['nama' => 'Nama', 'afiliasi' => 'Afiliasi', 'timeline' => 'Timeline', 'bidang' => 'Bidang'] as $key => $label)
                                <span class="px-3 py-1 rounded-full text-sm {{ data_get($signals, $key) ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ data_get($signals, $key) ? '✓' : '–' }} {{ $label }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <p class="font-medium mb-1">Query Pencarian</p>
                        <p class="text-slate-700 break-words">{{ $pelacakan->query_pencarian ?: '-' }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-2">
                    <p><span class="font-medium">Ringkasan:</span> {{ $pelacakan->ringkasan_hasil ?: '-' }}</p>
                    <p>
                        <span class="font-medium">Bukti:</span>
                        @if($pelacakan->link_bukti)
                            <a href="{{ $pelacakan->link_bukti }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 hover:underline break-all">
                                {{ $pelacakan->link_bukti }} ↗
                            </a>
                        @else
                            -
                        @endif
                    </p>
                </div>

                <div class="flex flex-wrap gap-2 mt-5">
                    <a href="{{ route('pelacakan.edit', $pelacakan) }}"
                       class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium">Edit Hasil</a>
                    <form action="{{ route('pelacakan.destroy', $pelacakan) }}" method="POST"
                          onsubmit="return confirm('Yakin ingin menghapus hasil pelacakan ini?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-medium">Hapus Hasil</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-6 text-slate-600">Belum ada hasil pelacakan.</div>
        @endforelse
    </section>
</x-layouts.app-tracer>
