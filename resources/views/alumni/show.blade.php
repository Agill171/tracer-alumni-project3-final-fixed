<x-layouts.app-tracer>
    <x-slot:title>Detail Alumni</x-slot:title>


    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">

        <div>
            <h2 class="text-3xl font-bold text-slate-900">
                Detail Alumni
            </h2>

            <p class="text-slate-600 mt-1">
                Profil target, pencarian, verifikasi evidence, dan data Project 4.
            </p>
        </div>


        <div class="flex flex-wrap gap-3">

            @if(request('from_batch'))

                <a
                    href="{{ route(
                        'pelacakan-batches.show',
                        request('from_batch')
                    ) }}"
                    class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium"
                >
                    ← Kembali ke Batch
                </a>

            @else

                <a
                    href="{{ route('alumni.index') }}"
                    class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium"
                >
                    ← Kembali
                </a>

            @endif


            <a
                href="{{ route(
                    'alumni.edit',
                    [
                        'alumni' => $alumni->id,
                        'from_batch' => request('from_batch'),
                    ]
                ) }}"
                class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium"
            >
                Edit Data Project 4
            </a>


            <a
                href="{{ route(
                    'pelacakan.create',
                    [
                        'alumni' => $alumni->id,
                        'from_batch' => request('from_batch'),
                    ]
                ) }}"
                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium"
            >
                + Catat Temuan
            </a>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- FLASH MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('success'))

        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">
            {{ session('success') }}
        </div>

    @endif


    @if($errors->any())

        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-4 text-red-700">

            <ul class="list-disc pl-5 space-y-1">

                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach

            </ul>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- WORKFLOW (MANUAL & AUTO ENRICHMENT) --}}
    {{-- ========================================================= --}}

    @php
        // Cek apakah ada hasil dari Auto Enrichment (AI)
        $autoResults = $alumni->hasilPelacakans->filter(fn($p) => filled($p->automation_key));
        $autoCount = $autoResults->count();
        $latestAuto = $autoResults->first();
        
        // Ambil item batch terkait untuk tombol review
        $batchItem = \App\Models\PelacakanBatchItem::where('alumni_id', $alumni->id)
            ->where('pelacakan_batch_id', request('from_batch'))
            ->first();
    @endphp

    <div class="mb-8 rounded-2xl border border-blue-200 bg-blue-50 p-5">
        <h3 class="font-semibold text-blue-900">
            Workflow Pelacakan Alumni (Manual & Otomatis)
        </h3>
        
        @if($autoCount > 0)
            <p class="text-sm text-blue-800 mt-2">
                <strong>Status Auto Enrichment (AI):</strong> 
                <span class="inline-block px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-semibold">
                    {{ $latestAuto->status_pelacakan }}
                </span>
                (Skor: {{ $latestAuto->confidence_score ?? '-' }}%)
            </p>

            @if($latestAuto->status_pelacakan === \App\Models\Alumni::STATUS_PERLU_VERIFIKASI && $batchItem)
                <a href="{{ route('pelacakan-batches.enrichment.review', $batchItem->id) }}" class="inline-flex mt-3 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold">
                    🔍 Periksa Kandidat AI di Batch
                </a>
            @endif
        @else
            <p class="text-sm text-blue-800 mt-2">
                Alumni ini belum diproses oleh Auto Enrichment (AI). Silakan jalankan dari menu <strong>Detail Batch</strong>.
            </p>
        @endif

        <p class="text-sm text-blue-800 mt-2">
            Untuk verifikasi manual, periksa evidence hasil pencarian, cocokkan nama, NIM, kampus/prodi, timeline, dan bidang. Kandidat yang belum kuat harus diverifikasi manual. False positive ditolak tanpa menghapus jejak evidence. Data Project 4 hanya diisi dari evidence yang benar.
        </p>
    </div>


    {{-- ========================================================= --}}
    {{-- PROFIL + PROJECT 4 --}}
    {{-- ========================================================= --}}

    <div class="grid lg:grid-cols-2 gap-6 mb-8">


        {{-- ===================================================== --}}
        {{-- PROFIL TARGET --}}
        {{-- ===================================================== --}}

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <h3 class="text-xl font-semibold mb-5">
                Profil Target Pencarian
            </h3>


            <dl class="grid grid-cols-[160px_1fr] gap-x-4 gap-y-3">

                <dt class="font-semibold">
                    Nama
                </dt>

                <dd>
                    {{ $alumni->nama ?: '-' }}
                </dd>


                <dt class="font-semibold">
                    NIM
                </dt>

                <dd>
                    {{ $alumni->nim ?: '-' }}
                </dd>


                <dt class="font-semibold">
                    Program Studi
                </dt>

                <dd>
                    {{ $alumni->prodi ?: '-' }}
                </dd>


                <dt class="font-semibold">
                    Angkatan
                </dt>

                <dd>
                    {{ $alumni->angkatan ?: '-' }}
                </dd>


                <dt class="font-semibold">
                    Tahun Lulus
                </dt>

                <dd>
                    {{ $alumni->tahun_lulus ?: '-' }}
                </dd>


                <dt class="font-semibold">
                    Email
                </dt>

                <dd>
                    {{ $alumni->email ?: '-' }}
                </dd>


                <dt class="font-semibold">
                    Nomor HP
                </dt>

                <dd>
                    {{ $alumni->no_hp ?: '-' }}
                </dd>


                <dt class="font-semibold">
                    Status
                </dt>

                <dd>

                    <span class="inline-block px-3 py-1 rounded-full bg-slate-100 text-slate-700">
                        {{ $alumni->status_verifikasi ?: '-' }}
                    </span>

                </dd>

            </dl>

        </section>


        {{-- ===================================================== --}}
        {{-- DATA PROJECT 4 --}}
        {{-- ===================================================== --}}

        <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <div class="flex items-start justify-between gap-4 mb-5">

                <h3 class="text-xl font-semibold">
                    Data Project 4
                </h3>


                <a
                    href="{{ route(
                        'alumni.edit',
                        [
                            'alumni' => $alumni->id,
                            'from_batch' => request('from_batch'),
                        ]
                    ) }}"
                    class="px-3 py-2 rounded-xl bg-amber-100 hover:bg-amber-200 text-amber-800 text-sm font-medium"
                >
                    Isi / Perbarui
                </a>

            </div>


            <div class="space-y-3">

                <p>
                    <strong>Email:</strong>
                    {{ $alumni->email ?: '-' }}
                </p>


                <p>
                    <strong>Nomor HP:</strong>
                    {{ $alumni->no_hp ?: '-' }}
                </p>


                <p>
                    <strong>Tempat Bekerja:</strong>
                    {{ $alumni->tempat_bekerja ?: '-' }}
                </p>


                <p>
                    <strong>Alamat Bekerja:</strong>
                    {{ $alumni->alamat_bekerja ?: '-' }}
                </p>


                <p>
                    <strong>Posisi:</strong>
                    {{ $alumni->posisi ?: '-' }}
                </p>


                <p>
                    <strong>Kategori Pekerjaan:</strong>
                    {{ $alumni->kategori_pekerjaan ?: '-' }}
                </p>

            </div>


            {{-- SOCIAL MEDIA ALUMNI --}}

            <div class="flex flex-wrap gap-2 mt-5">

                @if($alumni->linkedin)

                    <a
                        href="{{ $alumni->linkedin }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 text-sm font-medium"
                    >
                        LinkedIn ↗
                    </a>

                @endif


                @if($alumni->instagram)

                    <a
                        href="{{ $alumni->instagram }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 text-sm font-medium"
                    >
                        Instagram ↗
                    </a>

                @endif


                @if($alumni->facebook)

                    <a
                        href="{{ $alumni->facebook }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 text-sm font-medium"
                    >
                        Facebook ↗
                    </a>

                @endif


                @if($alumni->tiktok)

                    <a
                        href="{{ $alumni->tiktok }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 text-sm font-medium"
                    >
                        TikTok ↗
                    </a>

                @endif


                @if($alumni->sosmed_tempat_bekerja)

                    <a
                        href="{{ $alumni->sosmed_tempat_bekerja }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="px-3 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 text-sm font-medium"
                    >
                        Situs / Sosmed Tempat Kerja ↗
                    </a>

                @endif

            </div>


            @if($alumni->catatan)

                <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">

                    <p class="font-semibold mb-1">
                        Catatan
                    </p>

                    <p class="text-slate-700 whitespace-pre-line">
                        {{ $alumni->catatan }}
                    </p>

                </div>

            @endif

        </section>

    </div>


    {{-- ========================================================= --}}
    {{-- QUERY PENCARIAN --}}
    {{-- ========================================================= --}}

    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">

        <div class="mb-5">

            <h3 class="text-xl font-semibold">
                Query Pencarian
            </h3>

            <p class="text-slate-600 mt-1">
                Query dapat dibuka untuk pemeriksaan evidence secara manual.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full min-w-[850px] text-sm">

                <thead>

                    <tr class="bg-slate-100 text-left">

                        <th class="p-3">
                            Prioritas
                        </th>

                        <th class="p-3">
                            Sumber
                        </th>

                        <th class="p-3">
                            Query
                        </th>

                        <th class="p-3">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($alumni->queryPelacakans as $query)

                        <tr class="border-b border-slate-200">

                            <td class="p-3">
                                {{ $query->prioritas ?: '-' }}
                            </td>


                            <td class="p-3">

                                {{ data_get(
                                    config(
                                        'tracer.sources',
                                        []
                                    ),
                                    $query->sumber.'.label',
                                    $query->sumber
                                ) }}

                            </td>


                            <td class="p-3">
                                {{ $query->query }}
                            </td>


                            <td class="p-3">

                                <div class="flex flex-wrap gap-2">

                                    <a
                                        href="{{ $query->url_pencarian }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="px-3 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium"
                                    >
                                        Buka Pencarian ↗
                                    </a>


                                    <a
                                        href="{{ route(
                                            'pelacakan.create',
                                            [
                                                'alumni' => $alumni->id,
                                                'query' => $query->query,
                                                'source' => $query->sumber,
                                                'from_batch' => request('from_batch'),
                                            ]
                                        ) }}"
                                        class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-medium"
                                    >
                                        Catat Temuan
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="p-6 text-center text-slate-500"
                            >
                                Belum ada query pencarian.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </section>


    {{-- ========================================================= --}}
    {{-- EVIDENCE --}}
    {{-- ========================================================= --}}

    <section class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-5">

            <div>

                <h3 class="text-xl font-semibold">
                    Jejak Bukti dan Hasil Verifikasi
                </h3>

                <p class="text-slate-600 mt-1">
                    Setiap temuan menyimpan sumber, bukti, sinyal identitas,
                    confidence score, dan hasil audit.
                </p>

            </div>


            <a
                href="{{ route(
                    'pelacakan.create',
                    [
                        'alumni' => $alumni->id,
                        'from_batch' => request('from_batch'),
                    ]
                ) }}"
                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium"
            >
                + Catat Temuan
            </a>

        </div>


        @forelse($alumni->hasilPelacakans as $pelacakan)

            @php

                $signals =
                    $pelacakan->sinyal_identitas
                    ?? [];


                $isRejected =
                    $pelacakan->status_audit ===
                    \App\Models\HasilPelacakan::AUDIT_SALAH;


                $isAuto =
                    filled(
                        $pelacakan->automation_key
                    );


                $isReview =
                    $pelacakan->status_pelacakan ===
                    \App\Models\Alumni::STATUS_PERLU_VERIFIKASI;


                $canReject =
                    $isAuto
                    && $isReview
                    && ! $isRejected;


                $canApplyProject4 =
                    ! $isRejected
                    && $pelacakan->confidence_score !== null
                    && (int) $pelacakan->confidence_score >= 80
                    && $pelacakan->status_pelacakan ===
                        \App\Models\Alumni::STATUS_TERIDENTIFIKASI;

            @endphp


            <article class="border border-slate-200 rounded-2xl p-5 mb-5">


                {{-- ================================================= --}}
                {{-- HEADER EVIDENCE --}}
                {{-- ================================================= --}}

                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

                    <div>

                        <h4 class="text-lg font-semibold">
                            {{ $pelacakan->judul_temuan ?: 'Hasil Pelacakan' }}
                        </h4>


                        <div class="space-y-2 mt-3">

                            <p>
                                <strong>
                                    Status Alumni:
                                </strong>

                                {{ $pelacakan->status_pelacakan ?: '-' }}
                            </p>


                            <p>
                                <strong>
                                    Sumber:
                                </strong>

                                {{ $pelacakan->sumber_temuan ?: '-' }}
                            </p>


                            <p>
                                <strong>
                                    Tanggal:
                                </strong>

                                @if(filled($pelacakan->tanggal_ditemukan))

                                    {{ \Illuminate\Support\Carbon::parse(
                                        $pelacakan->tanggal_ditemukan
                                    )->format('d-m-Y') }}

                                @else

                                    -

                                @endif

                            </p>


                            <p>
                                <strong>
                                    Dicatat oleh:
                                </strong>

                                {{ $pelacakan->user?->name ?: '-' }}
                            </p>

                        </div>

                    </div>


                    <div class="flex flex-col items-start lg:items-end gap-2">


                        @if($pelacakan->confidence_score !== null)


                            @if((int) $pelacakan->confidence_score >= 80)

                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">

                                    {{ $pelacakan->kategori_kecocokan ?: 'Kuat' }}

                                    ·

                                    {{ $pelacakan->confidence_score }}%

                                </span>


                            @elseif((int) $pelacakan->confidence_score >= 50)

                                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-sm font-semibold">

                                    {{ $pelacakan->kategori_kecocokan ?: 'Perlu Verifikasi' }}

                                    ·

                                    {{ $pelacakan->confidence_score }}%

                                </span>


                            @else

                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-sm font-semibold">

                                    {{ $pelacakan->kategori_kecocokan ?: 'Tidak Cocok' }}

                                    ·

                                    {{ $pelacakan->confidence_score }}%

                                </span>

                            @endif


                        @else

                            <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-600 text-sm font-semibold">
                                Tidak ada kandidat kuat
                            </span>

                        @endif


                        @if($isRejected)

                            <span class="px-3 py-1 rounded-full border border-red-200 bg-red-50 text-red-700 text-sm font-semibold">
                                Ditolak Manual / False Positive
                            </span>

                        @endif

                    </div>

                </div>


                {{-- ================================================= --}}
                {{-- SINYAL IDENTITAS --}}
                {{-- ================================================= --}}

                @if(! empty($signals))

                    <div class="grid md:grid-cols-2 gap-5 mt-5">


                        <div>

                            <p class="font-semibold mb-2">
                                Sinyal Identitas
                            </p>


                            <div class="flex flex-wrap gap-2">

                                @foreach([
                                    'nama' => 'Nama',
                                    'nim' => 'NIM',
                                    'kampus' => 'Kampus',
                                    'afiliasi' => 'Afiliasi',
                                    'timeline' => 'Timeline',
                                    'bidang' => 'Bidang',
                                ] as $signalKey => $signalLabel)


                                    @if(
                                        (bool) data_get(
                                            $signals,
                                            $signalKey,
                                            false
                                        )
                                    )

                                        <span class="px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-sm">
                                            ✓ {{ $signalLabel }}
                                        </span>

                                    @else

                                        <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-500 text-sm">
                                            – {{ $signalLabel }}
                                        </span>

                                    @endif

                                @endforeach

                            </div>

                        </div>


                        <div>

                            <p class="font-semibold mb-2">
                                Query Pencarian
                            </p>

                            <p class="text-slate-700 break-words">
                                {{ $pelacakan->query_pencarian ?: '-' }}
                            </p>

                        </div>

                    </div>


                @elseif(filled($pelacakan->query_pencarian))

                    <div class="mt-5">

                        <p class="font-semibold mb-2">
                            Query Pencarian
                        </p>

                        <p class="text-slate-700 break-words">
                            {{ $pelacakan->query_pencarian }}
                        </p>

                    </div>

                @endif


                {{-- ================================================= --}}
                {{-- KANDIDAT PROJECT 4 --}}
                {{-- ================================================= --}}

                @if(
                    ! $isRejected
                    && ! empty(
                        $pelacakan->temuan_project4
                    )
                )

                    <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">

                        <p class="font-semibold text-emerald-800 mb-2">
                            Kandidat Data Project 4
                        </p>


                        <div class="space-y-1 text-sm text-emerald-800">

                            @foreach(
                                $pelacakan->temuan_project4
                                as $field => $value
                            )

                                @if(filled($value))

                                    <p>

                                        <strong>

                                            {{ ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $field
                                                )
                                            ) }}:

                                        </strong>

                                        {{ $value }}

                                    </p>

                                @endif

                            @endforeach

                        </div>

                    </div>

                @endif


                {{-- ================================================= --}}
                {{-- RINGKASAN --}}
                {{-- ================================================= --}}

                <div class="mt-5">

                    <p class="font-semibold">
                        Ringkasan:
                    </p>


                    <p class="mt-2 whitespace-pre-line text-slate-700">
                        {{ $pelacakan->ringkasan_hasil ?: '-' }}
                    </p>


                    <p class="mt-3">

                        <strong>
                            Bukti:
                        </strong>


                        @if($pelacakan->link_bukti)

                            <a
                                href="{{ $pelacakan->link_bukti }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-blue-600 hover:underline break-all"
                            >
                                {{ $pelacakan->link_bukti }} ↗
                            </a>

                        @else

                            -

                        @endif

                    </p>

                </div>


                {{-- ================================================= --}}
                {{-- AUDIT --}}
                {{-- ================================================= --}}

                @if(
                    filled($pelacakan->status_audit)
                    && $pelacakan->status_audit !==
                        \App\Models\HasilPelacakan::AUDIT_BELUM
                )

                    <div class="mt-5 rounded-xl border border-slate-200 bg-slate-50 p-4">

                        <p class="font-semibold text-slate-800">
                            Audit Evidence
                        </p>


                        <p class="mt-2 text-sm">

                            Status:

                            <strong>
                                {{ $pelacakan->status_audit }}
                            </strong>

                        </p>


                        @if($pelacakan->catatan_audit)

                            <p class="mt-2 text-sm whitespace-pre-line">
                                {{ $pelacakan->catatan_audit }}
                            </p>

                        @endif


                        @if($pelacakan->audited_at)

                            <p class="mt-2 text-xs text-slate-500">

                                Diaudit:

                                {{ \Illuminate\Support\Carbon::parse(
                                    $pelacakan->audited_at
                                )->format('d-m-Y H:i:s') }}

                            </p>

                        @endif

                    </div>

                @endif


                {{-- ================================================= --}}
                {{-- ACTION FINAL --}}
                {{-- ================================================= --}}

                <div
                    class="mt-5"
                    style="
                        display: flex;
                        flex-wrap: wrap;
                        align-items: center;
                        gap: 12px;
                    "
                >

                    {{-- EDIT HASIL --}}

                    <a
                        href="{{ route(
                            'pelacakan.edit',
                            [
                                'pelacakan' => $pelacakan->id,
                                'from_batch' => request('from_batch'),
                            ]
                        ) }}"
                        class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-medium"
                        style="display: inline-flex;"
                    >
                        Edit Hasil
                    </a>


                    {{-- TOLAK KANDIDAT --}}

                    @if($canReject)

                        <button
                            type="submit"
                            form="reject-pelacakan-{{ $pelacakan->id }}"
                            onclick="return confirm('Tolak kandidat ini sebagai false positive? Evidence tetap disimpan sebagai jejak audit dan tidak digunakan untuk Project 4.');"
                            class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold"
                            style="
                                display: inline-flex;
                                visibility: visible;
                                opacity: 1;
                                cursor: pointer;
                            "
                        >
                            Tolak Kandidat
                        </button>

                    @endif


                    {{-- SUDAH DITOLAK --}}

                    @if($isAuto && $isRejected)

                        <span
                            class="px-4 py-2 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm font-semibold"
                            style="display: inline-flex;"
                        >
                            Ditolak Manual / False Positive
                        </span>

                    @endif


                    {{-- ISI PROJECT 4 --}}

                    @if($canApplyProject4)

                        <a
                            href="{{ route(
                                'alumni.edit',
                                [
                                    'alumni' => $alumni->id,
                                    'from_batch' => request('from_batch'),
                                ]
                            ) }}"
                            class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium"
                            style="display: inline-flex;"
                        >
                            Isi Data Project 4
                        </a>

                    @endif


                    {{-- HAPUS HASIL --}}

                    <button
                        type="submit"
                        form="delete-pelacakan-{{ $pelacakan->id }}"
                        onclick="return confirm('Yakin ingin menghapus hasil pelacakan ini? Untuk false positive gunakan Tolak Kandidat agar jejak audit tetap tersimpan.');"
                        class="px-4 py-2 rounded-xl bg-slate-500 hover:bg-slate-600 text-white text-sm font-medium"
                        style="
                            display: inline-flex;
                            visibility: visible;
                            opacity: 1;
                            cursor: pointer;
                        "
                    >
                        Hapus Hasil
                    </button>

                </div>


                {{-- ================================================= --}}
                {{-- HIDDEN FORMS --}}
                {{-- ================================================= --}}

                @if($canReject)

                    <form
                        id="reject-pelacakan-{{ $pelacakan->id }}"
                        action="{{ route(
                            'pelacakan.enrichment.reject',
                            $pelacakan->id
                        ) }}"
                        method="POST"
                        style="display: none;"
                    >
                        @csrf


                        @if(request('from_batch'))

                            <input
                                type="hidden"
                                name="from_batch"
                                value="{{ request('from_batch') }}"
                            >

                        @endif

                    </form>

                @endif


                <form
                    id="delete-pelacakan-{{ $pelacakan->id }}"
                    action="{{ route(
                        'pelacakan.destroy',
                        $pelacakan->id
                    ) }}"
                    method="POST"
                    style="display: none;"
                >
                    @csrf
                    @method('DELETE')
                </form>

            </article>

        @empty

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-6 text-center text-slate-500">
                Belum ada hasil pelacakan.
            </div>

        @endforelse

    </section>

</x-layouts.app-tracer>