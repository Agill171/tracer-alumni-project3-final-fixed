<x-layouts.app-tracer>
    <x-slot:title>Dashboard</x-slot:title>


    {{-- HEADER --}}
    <div class="mb-8">

        <h2 class="text-3xl font-bold text-slate-900">
            Dashboard Tracer Alumni
        </h2>

        <p class="text-slate-600 mt-1">
            Ringkasan progres Daily Project 4 berdasarkan Coverage,
            Accuracy, dan Completeness.
        </p>

    </div>


    {{-- STATUS DATASET --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">

        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">

            <div>

                <h3 class="text-xl font-semibold text-slate-900">
                    Status Dataset Alumni
                </h3>

                <p class="text-sm text-slate-600 mt-1">
                    Hasil audit file sumber dosen terhadap database Tracer Alumni.
                </p>

            </div>


            @if($datasetUnikLengkap)

                <span class="shrink-0 px-4 py-2 rounded-full bg-emerald-100 text-emerald-700 text-sm font-semibold">
                    ✓ Seluruh NIM Unik Tersedia
                </span>

            @else

                <span class="shrink-0 px-4 py-2 rounded-full bg-red-100 text-red-700 text-sm font-semibold">
                    Dataset Belum Lengkap
                </span>

            @endif

        </div>


        <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- DATA SUMBER --}}
            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5">

                <p class="text-sm text-slate-500">
                    Data Sumber Dosen
                </p>

                <p class="text-2xl font-bold text-blue-600 mt-1">
                    {{ number_format($totalDatasetRubrik, 0, ',', '.') }}
                </p>

                <p class="text-xs text-slate-500 mt-2">
                    Total baris data pada file sumber
                </p>

            </div>


            {{-- NIM UNIK --}}
            <div class="rounded-2xl bg-slate-50 border border-slate-200 p-5">

                <p class="text-sm text-slate-500">
                    Alumni / NIM Unik
                </p>

                <p class="text-2xl font-bold text-emerald-600 mt-1">
                    {{ number_format($totalAlumniUnikSumber, 0, ',', '.') }}
                </p>

                <p class="text-xs text-slate-500 mt-2">
                    Seluruh NIM unik pada file sumber
                </p>

            </div>


            {{-- DUPLIKAT --}}
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5">

                <p class="text-sm text-amber-700">
                    Duplikat Berlebih
                </p>

                <p class="text-2xl font-bold text-amber-600 mt-1">
                    {{ number_format($totalDuplikatSumber, 0, ',', '.') }}
                </p>

                <p class="text-xs text-amber-600 mt-2">
                    Dari
                    {{ number_format($totalKelompokDuplikat, 0, ',', '.') }}
                    kelompok NIM duplikat
                </p>

            </div>


            {{-- MISSING NIM --}}
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">

                <p class="text-sm text-emerald-700">
                    NIM Unik Belum Masuk
                </p>

                <p class="text-2xl font-bold text-emerald-600 mt-1">
                    {{ number_format($totalMissingNimUnik, 0, ',', '.') }}
                </p>

                <p class="text-xs text-emerald-600 mt-2">
                    Tidak ada alumni unik yang hilang
                </p>

            </div>

        </div>


        {{-- KESIMPULAN AUDIT DATA --}}
        <div class="grid md:grid-cols-2 gap-4 mt-5">

            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-4">

                <p class="text-sm font-semibold text-blue-800">
                    Kesimpulan Import
                </p>

                <p class="text-sm text-blue-700 mt-2">

                    Database menyimpan

                    <strong>
                        {{ number_format($totalAlumni, 0, ',', '.') }}
                    </strong>

                    alumni unik dan sudah mencakup seluruh

                    <strong>
                        {{ number_format($totalAlumniUnikSumber, 0, ',', '.') }}
                    </strong>

                    NIM unik dari file sumber dosen.

                </p>

            </div>


            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4">

                <p class="text-sm font-semibold text-amber-800">
                    Catatan Kualitas Data
                </p>

                <p class="text-sm text-amber-700 mt-2">

                    Terdapat

                    <strong>
                        {{ number_format($totalKonflikSumber, 0, ',', '.') }}
                    </strong>

                    kelompok NIM duplikat yang memiliki perbedaan atribut
                    pada file sumber, terutama Program Studi dan
                    Tahun/Tanggal Lulus.

                    Kasus tersebut diperlakukan sebagai isu kualitas
                    data sumber, bukan sebagai alumni baru.

                </p>

            </div>

        </div>


        @if($datasetUnikLengkap)

            <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4">

                <p class="text-sm text-emerald-800">

                    <strong>Hasil audit:</strong>

                    tidak perlu melakukan import ulang

                    {{ number_format($totalDuplikatSumber, 0, ',', '.') }}

                    baris duplikat.

                    Seluruh NIM unik pada dataset dosen sudah tersedia
                    di database.

                </p>

            </div>

        @endif

    </div>


    {{-- STATISTIK UTAMA --}}
    <div class="grid md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

        {{-- TOTAL ALUMNI --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Total Alumni Unik
            </p>

            <h3 class="text-3xl font-bold text-slate-900">
                {{ number_format($totalAlumni, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">
                Alumni berdasarkan NIM unik di database
            </p>

        </div>


        {{-- COVERAGE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Coverage Project 4
            </p>

            <h3 class="text-3xl font-bold text-blue-600">
                {{ number_format($coverage, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">

                {{ number_format($coveragePersen, 2, ',', '.') }}%

                dari

                {{ number_format($totalDatasetRubrik, 0, ',', '.') }}

                data acuan rubrik

            </p>

            <p class="text-xs font-semibold text-blue-600 mt-3">
                Rentang skor rubrik:
                {{ $coverageRentangNilai }}
            </p>

        </div>


        {{-- COMPLETENESS --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Completeness ≥ 4 Kategori
            </p>

            <h3 class="text-3xl font-bold text-emerald-600">
                {{ number_format($completenessEmpat, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">

                {{ number_format($completenessPersen, 2, ',', '.') }}%

                dari alumni yang mempunyai data Project 4

            </p>

            <p class="text-xs font-semibold text-emerald-600 mt-3">
                Rubrik ≥ 4 field: skor 86 - 100
            </p>

        </div>


        {{-- BELUM PUNYA DATA --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <p class="text-sm text-slate-500 mb-2">
                Belum Punya Data Project 4
            </p>

            <h3 class="text-3xl font-bold text-red-600">
                {{ number_format($belumPunyaDataProject4, 0, ',', '.') }}
            </h3>

            <p class="text-sm text-slate-500 mt-2">
                Belum memiliki satu pun dari 8 kategori Project 4
            </p>

        </div>

    </div>


    {{-- COVERAGE 8 KATEGORI --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">

        <div class="mb-6">

            <h3 class="text-xl font-semibold text-slate-900">
                Coverage 8 Kategori Project 4
            </h3>

            <p class="text-sm text-slate-600 mt-1">
                Data yang dihitung sesuai kategori hasil pelacakan
                yang ditetapkan pada rubrik dosen.
            </p>


            <div class="mt-3 rounded-xl bg-blue-50 border border-blue-200 p-3">

                <p class="text-xs text-blue-700">

                    LinkedIn, Instagram, Facebook, dan TikTok dihitung
                    sebagai satu kategori yaitu

                    <strong>
                        Sosial Media Alumni
                    </strong>.

                </p>

            </div>

        </div>


        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">

            @foreach($fieldCoverageResult as $field => $total)

                @php

                    $persenField = $totalDatasetRubrik > 0
                        ? round(
                            ($total / $totalDatasetRubrik) * 100,
                            2
                        )
                        : 0;

                @endphp


                <div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">

                    <p class="text-sm font-medium text-slate-700 min-h-[40px]">
                        {{ $field }}
                    </p>


                    <p class="text-2xl font-bold text-blue-600 mt-2">
                        {{ number_format($total, 0, ',', '.') }}
                    </p>


                    <p class="text-xs text-slate-500 mt-1">

                        {{ number_format($persenField, 2, ',', '.') }}%

                        dari dataset acuan

                    </p>


                    <div class="w-full bg-slate-200 rounded-full h-2 mt-3 overflow-hidden">

                        <div
                            class="bg-blue-600 h-2 rounded-full"
                            style="width: {{ min(100, $persenField) }}%">
                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    </div>


    {{-- COMPLETENESS --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 mb-8">

        <div class="mb-5">

            <h3 class="text-xl font-semibold text-slate-900">
                Completeness Project 4
            </h3>

            <p class="text-sm text-slate-600 mt-1">
                Distribusi jumlah kategori Project 4 yang terisi
                pada setiap alumni.
            </p>

        </div>


        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div class="rounded-2xl bg-red-50 border border-red-200 p-5">

                <p class="text-sm font-semibold text-red-700">
                    &lt; 2 Field
                </p>

                <p class="text-3xl font-bold text-red-600 mt-2">
                    {{ number_format($completenessKurangDua, 0, ',', '.') }}
                </p>

                <p class="text-xs text-red-600 mt-2">
                    Rentang skor: 0 - 50
                </p>

            </div>


            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-5">

                <p class="text-sm font-semibold text-amber-700">
                    2 Field
                </p>

                <p class="text-3xl font-bold text-amber-600 mt-2">
                    {{ number_format($completenessDua, 0, ',', '.') }}
                </p>

                <p class="text-xs text-amber-600 mt-2">
                    Rentang skor: 51 - 70
                </p>

            </div>


            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-5">

                <p class="text-sm font-semibold text-blue-700">
                    3 Field
                </p>

                <p class="text-3xl font-bold text-blue-600 mt-2">
                    {{ number_format($completenessTiga, 0, ',', '.') }}
                </p>

                <p class="text-xs text-blue-600 mt-2">
                    Rentang skor: 71 - 85
                </p>

            </div>


            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-5">

                <p class="text-sm font-semibold text-emerald-700">
                    ≥ 4 Field
                </p>

                <p class="text-3xl font-bold text-emerald-600 mt-2">
                    {{ number_format($completenessEmpat, 0, ',', '.') }}
                </p>

                <p class="text-xs text-emerald-600 mt-2">
                    Rentang skor: 86 - 100
                </p>

            </div>

        </div>


        <div class="mt-5 rounded-xl bg-slate-50 border border-slate-200 p-4">

            <p class="text-sm font-medium text-slate-700 mb-2">
                Definisi kategori Completeness
            </p>

            <p class="text-xs leading-6 text-slate-600">

                1. Sosial Media Alumni,
                2. Email,
                3. No HP,
                4. Tempat Bekerja,
                5. Alamat Bekerja,
                6. Posisi/Jabatan,
                7. PNS/Swasta/Wirausaha,
                dan
                8. Sosial Media Tempat Bekerja.

            </p>

        </div>

    </div>


    {{-- TARGET COVERAGE + STATUS --}}
    <div class="grid lg:grid-cols-2 gap-6 mb-8">

        {{-- TARGET COVERAGE --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <div class="flex items-start justify-between gap-4 mb-5">

                <div>

                    <h3 class="text-xl font-semibold text-slate-900">
                        Target Coverage Project 4
                    </h3>

                    <p class="text-sm text-slate-500 mt-1">
                        Target rubrik tertinggi dan target aman internal.
                    </p>

                </div>


                <span class="shrink-0 px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">

                    {{ number_format($progressTargetAman, 2, ',', '.') }}%

                </span>

            </div>


            <div class="w-full bg-slate-200 rounded-full h-4 overflow-hidden">

                <div
                    class="bg-blue-600 h-4 rounded-full"
                    style="width: {{ min(100, $progressTargetAman) }}%">
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

                    <p class="text-xs text-slate-500 mt-2">
                        &gt; 106.720 data = rentang skor 91 - 100
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


            <div class="mt-5 rounded-2xl bg-slate-50 border border-slate-200 p-4">

                <p class="text-sm font-semibold text-slate-700 mb-3">
                    Posisi Coverage Saat Ini
                </p>


                <div class="flex items-center justify-between gap-4">

                    <span class="text-sm text-slate-600">

                        {{ number_format($coverage, 0, ',', '.') }}

                        data ditemukan

                    </span>


                    <span class="px-3 py-1 rounded-full bg-blue-100 text-blue-700 text-sm font-semibold">

                        Skor {{ $coverageRentangNilai }}

                    </span>

                </div>

            </div>

        </div>


        {{-- STATUS PELACAKAN --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <h3 class="text-xl font-semibold text-slate-900 mb-5">
                Status Pelacakan Alumni
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
                        Identitas Teridentifikasi
                    </span>

                    <span class="font-bold text-emerald-700">
                        {{ number_format($terverifikasi, 0, ',', '.') }}
                    </span>

                </div>

            </div>


            {{-- ACCURACY SAMPLING 500 --}}
            <div class="mt-5 rounded-2xl border border-violet-200 bg-violet-50 p-4">

                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">

                    <div>

                        <p class="text-sm font-semibold text-violet-800">
                            Accuracy Project 4
                        </p>

                        <p class="text-3xl font-bold text-violet-700 mt-1">

                            {{ number_format(
                                $accuracySementara,
                                2,
                                ',',
                                '.'
                            ) }}%

                        </p>

                        <p class="text-sm text-violet-700 mt-2">
                            Accuracy sementara
                        </p>

                        <p class="text-sm text-violet-700 mt-1">

                            Sampel:

                            <strong>

                                {{ number_format(
                                    $accuracyTotalSample,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                                /

                                {{ number_format(
                                    $accuracyTargetSample,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </strong>

                        </p>

                    </div>


                    <a
                        href="{{ route('accuracy-audit.index') }}"
                        class="shrink-0 inline-flex items-center justify-center px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-medium">

                        Buka Accuracy Audit

                    </a>

                </div>


                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5">

                    <div class="rounded-xl bg-white border border-emerald-200 p-3">

                        <p class="text-xs text-slate-500">
                            Benar
                        </p>

                        <p class="text-xl font-bold text-emerald-600 mt-1">
                            {{ number_format($accuracyBenar, 0, ',', '.') }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-white border border-red-200 p-3">

                        <p class="text-xs text-slate-500">
                            Salah
                        </p>

                        <p class="text-xl font-bold text-red-600 mt-1">
                            {{ number_format($accuracySalah, 0, ',', '.') }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-white border border-amber-200 p-3">

                        <p class="text-xs text-slate-500">
                            Perlu Verifikasi
                        </p>

                        <p class="text-xl font-bold text-amber-600 mt-1">
                            {{ number_format($accuracyPerluVerifikasi, 0, ',', '.') }}
                        </p>

                    </div>


                    <div class="rounded-xl bg-white border border-slate-200 p-3">

                        <p class="text-xs text-slate-500">
                            Belum Diaudit
                        </p>

                        <p class="text-xl font-bold text-slate-700 mt-1">
                            {{ number_format($accuracyBelumDiaudit, 0, ',', '.') }}
                        </p>

                    </div>

                </div>


                <div class="mt-4 grid sm:grid-cols-2 gap-3">

                    <div class="rounded-xl bg-white/70 border border-violet-200 p-3">

                        <p class="text-xs text-violet-600">
                            Sudah Diaudit
                        </p>

                        <p class="text-lg font-bold text-violet-800 mt-1">

                            {{ number_format($accuracyTotalDiaudit, 0, ',', '.') }}

                            /

                            {{ number_format($accuracyTotalSample, 0, ',', '.') }}

                        </p>

                    </div>


                    <div class="rounded-xl bg-white/70 border border-violet-200 p-3">

                        <p class="text-xs text-violet-600">
                            Rentang Nilai Rubrik
                        </p>

                        <p class="text-lg font-bold text-violet-800 mt-1">
                            {{ $accuracyRentangNilai }}
                        </p>

                    </div>

                </div>


                @if(!$accuracySamplingLengkap)

                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3">

                        <p class="text-xs text-amber-800">

                            Sampling belum lengkap.

                            Saat ini tersedia

                            <strong>
                                {{ number_format($accuracyTotalSample, 0, ',', '.') }}
                            </strong>

                            dari

                            <strong>
                                {{ number_format($accuracyTargetSample, 0, ',', '.') }}
                            </strong>

                            sampel.

                            Kekurangan:

                            <strong>
                                {{ number_format($accuracyKekuranganSample, 0, ',', '.') }}
                            </strong>.

                        </p>

                    </div>

                @elseif(!$accuracyAuditLengkap)

                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3">

                        <p class="text-xs text-amber-800">
                            Sampling 500 sudah lengkap,
                            tetapi seluruh sampel belum memiliki
                            keputusan final Benar atau Salah.
                        </p>

                    </div>

                @else

                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-3">

                        <p class="text-xs text-emerald-800">

                            Audit 500 sampel sudah lengkap.

                            Rentang nilai Accuracy:

                            <strong>
                                {{ $accuracyRentangNilai }}
                            </strong>.

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- ALUMNI TERBARU + AKSI CEPAT --}}
    <div class="grid lg:grid-cols-3 gap-6 mb-8">

        {{-- ALUMNI TERBARU --}}
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <div class="flex items-center justify-between mb-4">

                <h3 class="text-xl font-semibold text-slate-900">
                    Alumni Terbaru
                </h3>


                <a
                    href="{{ route('alumni.index') }}"
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


                                <p class="text-sm text-slate-600 mt-1">

                                    NIM:

                                    {{ $alumni->nim ?: '-' }}

                                </p>


                                <p class="text-sm text-slate-600">

                                    Tempat Bekerja:

                                    {{ $alumni->tempat_bekerja ?: '-' }}

                                </p>

                            </div>


                            <div class="flex items-center gap-2 shrink-0">

                                <span class="inline-block px-3 py-1 rounded-full text-sm bg-slate-100 text-slate-700">

                                    {{ $alumni->status_verifikasi ?: 'Belum Dilacak' }}

                                </span>


                                <a
                                    href="{{ route(
                                        'alumni.show',
                                        ['alumni' => $alumni->id]
                                    ) }}"
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


        {{-- AKSI CEPAT --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <h3 class="text-xl font-semibold text-slate-900 mb-4">
                Aksi Cepat
            </h3>


            <div class="space-y-3">

                <a
                    href="{{ route('alumni.create') }}"
                    class="block w-full text-center px-4 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">

                    + Tambah Alumni

                </a>


                <a
                    href="{{ route('alumni.import.form') }}"
                    class="block w-full text-center px-4 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium">

                    Import Excel

                </a>


                <a
                    href="{{ route('alumni.export.excel') }}"
                    class="block w-full text-center px-4 py-3 rounded-xl bg-violet-600 hover:bg-violet-700 text-white font-medium">

                    Export Excel

                </a>


                <a
                    href="{{ route('alumni.export.download') }}"
                    class="block w-full text-center px-4 py-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-medium">

                    Download Hasil Export

                </a>


                <a
                    href="{{ route('accuracy-audit.index') }}"
                    class="block w-full text-center px-4 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium">

                    Accuracy Audit

                </a>


                <a
                    href="{{ route('alumni.index') }}"
                    class="block w-full text-center px-4 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">

                    Kelola Data Alumni

                </a>

            </div>

        </div>

    </div>

</x-layouts.app-tracer>