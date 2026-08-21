<x-layouts.app-tracer>
    <x-slot:title>Accuracy Audit</x-slot:title>


    {{-- HEADER --}}
    <div class="mb-8">

        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">

            <div>

                <h2 class="text-3xl font-bold text-slate-900">
                    Accuracy Audit Project 4
                </h2>

                <p class="text-slate-600 mt-1">
                    Sampling acak permanen maksimal 500 alumni
                    sesuai rubrik Daily Project 4.
                </p>

            </div>


            <a
                href="{{ route('dashboard') }}"
                class="inline-flex items-center justify-center px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">

                ← Kembali ke Dashboard

            </a>

        </div>

    </div>


    {{-- SUCCESS --}}
    @if(session('success'))

        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-700">

            {{ session('success') }}

        </div>

    @endif


    {{-- ERROR --}}
    @if($errors->any())

        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">

            <ul class="list-disc list-inside">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- STATUS SAMPLING --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">

        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

            <div>

                <h3 class="text-xl font-semibold text-slate-900">
                    Sampling Accuracy Rubrik
                </h3>

                <p class="text-sm text-slate-600 mt-1">
                    Hanya alumni yang mempunyai minimal satu dari
                    8 kategori Project 4 yang dapat menjadi sampel.
                </p>

            </div>


            @if($samplingLengkap)

                <span class="px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">
                    Sampling 500 Lengkap
                </span>

            @else

                <span class="px-4 py-2 rounded-full bg-amber-100 text-amber-700 text-sm font-semibold">
                    Sampling Belum Lengkap
                </span>

            @endif

        </div>


        <div class="grid md:grid-cols-3 gap-4 mt-6">

            {{-- ELIGIBLE --}}
            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5">

                <p class="text-sm text-slate-500">
                    Alumni Eligible
                </p>

                <p class="text-3xl font-bold text-slate-900 mt-1">
                    {{ number_format($totalEligible, 0, ',', '.') }}
                </p>

                <p class="text-xs text-slate-500 mt-2">
                    Memiliki minimal 1 kategori Project 4
                </p>

            </div>


            {{-- SAMPLE --}}
            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-5">

                <p class="text-sm text-blue-700">
                    Sampel Terpilih
                </p>

                <p class="text-3xl font-bold text-blue-700 mt-1">

                    {{ number_format($totalSample, 0, ',', '.') }}

                    <span class="text-lg font-semibold">
                        /
                        {{ number_format($targetSample, 0, ',', '.') }}
                    </span>

                </p>

                <p class="text-xs text-blue-600 mt-2">
                    Sampel bersifat permanen
                </p>

            </div>


            {{-- KEKURANGAN --}}
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5">

                <p class="text-sm text-amber-700">
                    Kekurangan Sampel
                </p>

                <p class="text-3xl font-bold text-amber-700 mt-1">
                    {{ number_format($kekuranganSample, 0, ',', '.') }}
                </p>

                <p class="text-xs text-amber-600 mt-2">
                    Menuju target 500
                </p>

            </div>

        </div>


        @if(!$samplingLengkap)

            <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 p-4">

                <p class="text-sm text-amber-800">

                    Saat ini baru tersedia

                    <strong>
                        {{ number_format($totalEligible, 0, ',', '.') }}
                    </strong>

                    alumni yang memiliki data Project 4.

                    Sistem tidak akan membuat sampel palsu untuk memenuhi angka 500.

                    Ketika data Project 4 bertambah, daftar sampel akan otomatis
                    dilengkapi sampai maksimal 500 alumni.

                </p>

            </div>

        @endif

    </div>


    {{-- STATISTIK ACCURACY --}}
    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        {{-- SUDAH DIAUDIT --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Sudah Diaudit
            </p>

            <h3 class="text-3xl font-bold text-blue-600">
                {{ number_format($totalDiaudit, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">
                dari
                {{ number_format($totalSample, 0, ',', '.') }}
                sampel tersedia
            </p>

        </div>


        {{-- BELUM --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Belum Diaudit
            </p>

            <h3 class="text-3xl font-bold text-amber-600">
                {{ number_format($belumDiaudit, 0, ',', '.') }}
            </h3>

        </div>


        {{-- FINAL --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Keputusan Final
            </p>

            <h3 class="text-3xl font-bold text-emerald-600">
                {{ number_format($totalFinal, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">
                Benar + Salah
            </p>

        </div>


        {{-- ACCURACY --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Accuracy Sementara
            </p>

            <h3 class="text-3xl font-bold text-violet-600">
                {{ number_format($accuracySementara, 2, ',', '.') }}%
            </h3>

            <p class="text-sm text-slate-500 mt-2">
                Benar / (Benar + Salah)
            </p>

        </div>

    </div>


    {{-- DETAIL STATUS --}}
    <div class="grid md:grid-cols-4 gap-4 mb-8">

        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">

            <p class="text-sm text-emerald-700">
                Benar
            </p>

            <p class="text-3xl font-bold text-emerald-700 mt-1">
                {{ number_format($benar, 0, ',', '.') }}
            </p>

        </div>


        <div class="rounded-2xl border border-red-200 bg-red-50 p-5">

            <p class="text-sm text-red-700">
                Salah
            </p>

            <p class="text-3xl font-bold text-red-700 mt-1">
                {{ number_format($salah, 0, ',', '.') }}
            </p>

        </div>


        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">

            <p class="text-sm text-amber-700">
                Perlu Verifikasi
            </p>

            <p class="text-3xl font-bold text-amber-700 mt-1">
                {{ number_format($perluVerifikasi, 0, ',', '.') }}
            </p>

        </div>


        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

            <p class="text-sm text-slate-700">
                Belum Diaudit
            </p>

            <p class="text-3xl font-bold text-slate-700 mt-1">
                {{ number_format($belumDiaudit, 0, ',', '.') }}
            </p>

        </div>

    </div>


    {{-- RUBRIK ACCURACY --}}
    <div class="rounded-2xl border border-violet-200 bg-violet-50 p-6 mb-8">

        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">

            <div>

                <h3 class="font-semibold text-violet-900 text-lg">
                    Rubrik Accuracy Daily Project 4
                </h3>

                <p class="text-sm text-violet-800 mt-2">
                    Penilaian menggunakan jumlah data benar dari
                    500 sampel acak.
                </p>

            </div>


            <div>

                <p class="text-xs uppercase tracking-wide text-violet-600">
                    Rentang Nilai Saat Ini
                </p>

                <p class="text-xl font-bold text-violet-900 mt-1">
                    {{ $accuracyRentangNilai }}
                </p>

            </div>

        </div>


        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-5">

            <div class="rounded-xl bg-white border border-violet-200 p-4">

                <p class="font-semibold text-slate-900">
                    &lt; 350 benar
                </p>

                <p class="text-sm text-slate-600 mt-1">
                    Nilai 0 - 50
                </p>

            </div>


            <div class="rounded-xl bg-white border border-violet-200 p-4">

                <p class="font-semibold text-slate-900">
                    350 - 425 benar
                </p>

                <p class="text-sm text-slate-600 mt-1">
                    Nilai 51 - 75
                </p>

            </div>


            <div class="rounded-xl bg-white border border-violet-200 p-4">

                <p class="font-semibold text-slate-900">
                    426 - 475 benar
                </p>

                <p class="text-sm text-slate-600 mt-1">
                    Nilai 76 - 90
                </p>

            </div>


            <div class="rounded-xl bg-white border border-violet-200 p-4">

                <p class="font-semibold text-slate-900">
                    &gt; 475 benar
                </p>

                <p class="text-sm text-slate-600 mt-1">
                    Nilai 91 - 100
                </p>

            </div>

        </div>


        @if(!$auditLengkap)

            <div class="mt-5 rounded-xl bg-white/70 border border-violet-200 p-4">

                <p class="text-sm text-violet-800">

                    <strong>
                        Nilai Accuracy belum dianggap final.
                    </strong>

                    Sistem membutuhkan 500 sampel dan seluruh sampel
                    harus memiliki keputusan final Benar atau Salah.

                </p>

            </div>

        @else

            <div class="mt-5 rounded-xl bg-emerald-50 border border-emerald-200 p-4">

                <p class="text-sm text-emerald-800">

                    Audit 500 sampel sudah lengkap.

                    Rentang nilai Accuracy:

                    <strong>
                        {{ $accuracyRentangNilai }}
                    </strong>

                </p>

            </div>

        @endif

    </div>


    {{-- DAFTAR SAMPEL --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3 mb-6">

            <div>

                <h3 class="text-xl font-semibold text-slate-900">
                    Daftar Sampel Accuracy
                </h3>

                <p class="text-sm text-slate-600 mt-1">
                    Periksa data Project 4 dan evidence sebelum
                    menentukan hasil audit.
                </p>

            </div>


            <span class="text-sm text-slate-500">

                {{ number_format($samples->total(), 0, ',', '.') }}

                sampel

            </span>

        </div>


        <div class="space-y-6">

            @forelse($samples as $sample)

                @php

                    $alumni = $sample->alumni;

                    $pelacakan = $alumni
                        ? $alumni->hasilPelacakans->first()
                        : null;


                    $auditClass = match($sample->status_audit) {

                        \App\Models\AccuracyAuditSample::STATUS_BENAR =>
                            'bg-emerald-100 text-emerald-700',

                        \App\Models\AccuracyAuditSample::STATUS_SALAH =>
                            'bg-red-100 text-red-700',

                        \App\Models\AccuracyAuditSample::STATUS_PERLU_VERIFIKASI =>
                            'bg-amber-100 text-amber-700',

                        default =>
                            'bg-slate-100 text-slate-700',
                    };


                    $socialMediaAda =
                        filled($alumni?->linkedin)
                        || filled($alumni?->instagram)
                        || filled($alumni?->facebook)
                        || filled($alumni?->tiktok);

                @endphp


                <article class="border border-slate-200 rounded-2xl p-5">


                    {{-- HEADER --}}
                    <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-4">

                        <div>

                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">
                                Sampel #{{ $sample->sample_order }}
                            </p>

                            <h4 class="text-lg font-semibold text-slate-900 mt-1">
                                {{ $alumni?->nama ?: 'Alumni tidak ditemukan' }}
                            </h4>

                            <p class="text-sm text-slate-600 mt-1">

                                NIM:
                                {{ $alumni?->nim ?: '-' }}

                                ·

                                Program Studi:
                                {{ $alumni?->prodi ?: '-' }}

                            </p>

                        </div>


                        <span class="px-3 py-1 rounded-full text-sm font-medium {{ $auditClass }}">

                            {{ $sample->status_audit }}

                        </span>

                    </div>


                    {{-- 8 DATA PROJECT 4 --}}
                    <div class="mt-5">

                        <h5 class="font-semibold text-slate-900 mb-3">
                            Data Project 4
                        </h5>


                        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-3">


                            {{-- SOSMED --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    1. Sosial Media
                                </p>

                                @if($socialMediaAda)

                                    <div class="text-sm mt-2 space-y-1">

                                        @if($alumni?->linkedin)

                                            <a
                                                href="{{ $alumni->linkedin }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block text-blue-600 hover:underline break-all">
                                                LinkedIn ↗
                                            </a>

                                        @endif


                                        @if($alumni?->instagram)

                                            <a
                                                href="{{ $alumni->instagram }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block text-blue-600 hover:underline break-all">
                                                Instagram ↗
                                            </a>

                                        @endif


                                        @if($alumni?->facebook)

                                            <a
                                                href="{{ $alumni->facebook }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block text-blue-600 hover:underline break-all">
                                                Facebook ↗
                                            </a>

                                        @endif


                                        @if($alumni?->tiktok)

                                            <a
                                                href="{{ $alumni->tiktok }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block text-blue-600 hover:underline break-all">
                                                TikTok ↗
                                            </a>

                                        @endif

                                    </div>

                                @else

                                    <p class="text-sm text-slate-500 mt-2">
                                        -
                                    </p>

                                @endif

                            </div>


                            {{-- EMAIL --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    2. Email
                                </p>

                                <p class="text-sm text-slate-800 mt-2 break-all">
                                    {{ $alumni?->email ?: '-' }}
                                </p>

                            </div>


                            {{-- NO HP --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    3. No HP
                                </p>

                                <p class="text-sm text-slate-800 mt-2">
                                    {{ $alumni?->no_hp ?: '-' }}
                                </p>

                            </div>


                            {{-- TEMPAT KERJA --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    4. Tempat Bekerja
                                </p>

                                <p class="text-sm text-slate-800 mt-2">
                                    {{ $alumni?->tempat_bekerja ?: '-' }}
                                </p>

                            </div>


                            {{-- ALAMAT KERJA --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    5. Alamat Bekerja
                                </p>

                                <p class="text-sm text-slate-800 mt-2">
                                    {{ $alumni?->alamat_bekerja ?: '-' }}
                                </p>

                            </div>


                            {{-- POSISI --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    6. Posisi / Jabatan
                                </p>

                                <p class="text-sm text-slate-800 mt-2">
                                    {{ $alumni?->posisi ?: '-' }}
                                </p>

                            </div>


                            {{-- KATEGORI --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    7. PNS / Swasta / Wirausaha
                                </p>

                                <p class="text-sm text-slate-800 mt-2">
                                    {{ $alumni?->kategori_pekerjaan ?: '-' }}
                                </p>

                            </div>


                            {{-- SOSMED TEMPAT KERJA --}}
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">

                                <p class="text-xs font-semibold text-slate-500">
                                    8. Sosmed Tempat Bekerja
                                </p>

                                @if($alumni?->sosmed_tempat_bekerja)

                                    <a
                                        href="{{ $alumni->sosmed_tempat_bekerja }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-sm text-blue-600 hover:underline break-all mt-2 block">

                                        Buka Link ↗

                                    </a>

                                @else

                                    <p class="text-sm text-slate-500 mt-2">
                                        -
                                    </p>

                                @endif

                            </div>

                        </div>

                    </div>


                    {{-- EVIDENCE TERBARU --}}
                    <div class="mt-5 rounded-2xl bg-slate-50 border border-slate-200 p-4">

                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">
                            Evidence Pelacakan Terbaru
                        </p>


                        @if($pelacakan)

                            <div class="grid lg:grid-cols-2 gap-4">

                                <div>

                                    <p class="text-sm font-semibold text-slate-800">
                                        {{ $pelacakan->judul_temuan ?: 'Hasil Pelacakan' }}
                                    </p>

                                    <p class="text-sm text-slate-600 mt-2">
                                        Sumber:
                                        {{ $pelacakan->sumber_temuan ?: '-' }}
                                    </p>


                                    @if($pelacakan->link_bukti)

                                        <a
                                            href="{{ $pelacakan->link_bukti }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="text-sm text-blue-600 hover:underline break-all mt-1 block">

                                            Buka Evidence ↗

                                        </a>

                                    @endif

                                </div>


                                <div>

                                    <p class="text-sm text-slate-600">
                                        Confidence:
                                        {{ number_format($pelacakan->confidence_score ?? 0, 0, ',', '.') }}%
                                    </p>

                                    <p class="text-sm text-slate-600 mt-1">
                                        Kategori:
                                        {{ $pelacakan->kategori_kecocokan ?: '-' }}
                                    </p>

                                    <p class="text-sm text-slate-600 mt-1">
                                        Dicatat:
                                        {{ $pelacakan->user?->name ?: '-' }}
                                    </p>

                                </div>

                            </div>


                            <div class="mt-4">

                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Ringkasan
                                </p>

                                <p class="text-sm text-slate-700 mt-2 whitespace-pre-line">
                                    {{ $pelacakan->ringkasan_hasil ?: '-' }}
                                </p>

                            </div>

                        @else

                            <p class="text-sm text-slate-500">
                                Belum terdapat evidence pelacakan tersimpan untuk alumni ini.
                            </p>

                        @endif

                    </div>


                    {{-- FORM AUDIT --}}
                    <form
                        action="{{ route('accuracy-audit.update', $sample) }}"
                        method="POST"
                        class="mt-5 rounded-2xl border border-slate-200 p-4">

                        @csrf
                        @method('PUT')


                        <div class="grid lg:grid-cols-3 gap-4">

                            <div>

                                <label
                                    for="status_audit_{{ $sample->id }}"
                                    class="block text-sm font-medium text-slate-700 mb-2">

                                    Status Audit

                                </label>


                                <select
                                    id="status_audit_{{ $sample->id }}"
                                    name="status_audit"
                                    required
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3">

                                    <option value="">
                                        -- Pilih hasil audit --
                                    </option>


                                    <option
                                        value="{{ \App\Models\AccuracyAuditSample::STATUS_BENAR }}"
                                        @selected(
                                            $sample->status_audit ===
                                            \App\Models\AccuracyAuditSample::STATUS_BENAR
                                        )>

                                        Benar

                                    </option>


                                    <option
                                        value="{{ \App\Models\AccuracyAuditSample::STATUS_SALAH }}"
                                        @selected(
                                            $sample->status_audit ===
                                            \App\Models\AccuracyAuditSample::STATUS_SALAH
                                        )>

                                        Salah

                                    </option>


                                    <option
                                        value="{{ \App\Models\AccuracyAuditSample::STATUS_PERLU_VERIFIKASI }}"
                                        @selected(
                                            $sample->status_audit ===
                                            \App\Models\AccuracyAuditSample::STATUS_PERLU_VERIFIKASI
                                        )>

                                        Perlu Verifikasi

                                    </option>

                                </select>

                            </div>


                            <div class="lg:col-span-2">

                                <label
                                    for="catatan_audit_{{ $sample->id }}"
                                    class="block text-sm font-medium text-slate-700 mb-2">

                                    Catatan Audit

                                </label>


                                <textarea
                                    id="catatan_audit_{{ $sample->id }}"
                                    name="catatan_audit"
                                    rows="3"
                                    maxlength="3000"
                                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                                    placeholder="Contoh: LinkedIn dan email sesuai dengan identitas serta evidence alumni.">{{ $sample->catatan_audit }}</textarea>

                            </div>

                        </div>


                        <div class="flex flex-wrap items-center gap-3 mt-4">

                            <button
                                type="submit"
                                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">

                                Simpan Audit

                            </button>


                            @if(
                                $sample->status_audit !==
                                \App\Models\AccuracyAuditSample::STATUS_BELUM
                            )

                                <button
                                    type="submit"
                                    form="reset-audit-{{ $sample->id }}"
                                    class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">

                                    Reset Audit

                                </button>

                            @endif


                            @if($sample->audited_at)

                                <span class="text-sm text-slate-500">

                                    Diaudit

                                    {{ $sample->audited_at->format('d-m-Y H:i') }}


                                    @if($sample->auditor)

                                        oleh

                                        {{ $sample->auditor->name }}

                                    @endif

                                </span>

                            @endif

                        </div>

                    </form>


                    {{-- RESET FORM --}}
                    @if(
                        $sample->status_audit !==
                        \App\Models\AccuracyAuditSample::STATUS_BELUM
                    )

                        <form
                            id="reset-audit-{{ $sample->id }}"
                            action="{{ route('accuracy-audit.reset', $sample) }}"
                            method="POST"
                            class="hidden">

                            @csrf
                            @method('DELETE')

                        </form>

                    @endif

                </article>

            @empty

                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-8 text-center">

                    <p class="text-slate-700 font-medium">
                        Belum ada alumni yang eligible menjadi sampel Accuracy.
                    </p>

                    <p class="text-sm text-slate-500 mt-2">
                        Isi minimal satu dari 8 kategori Project 4 terlebih dahulu.
                    </p>

                </div>

            @endforelse

        </div>


        <div class="mt-6">
            {{ $samples->links() }}
        </div>

    </div>

</x-layouts.app-tracer>