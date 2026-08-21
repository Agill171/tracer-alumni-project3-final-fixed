@php
    $record = $pelacakan ?? null;

    $signals =
        data_get(
            $record,
            'sinyal_identitas',
            []
        ) ?? [];

    $temuanProject4 =
        old(
            'project4',
            data_get(
                $record,
                'temuan_project4',
                []
            ) ?? []
        );

    $defaultQuery =
        data_get(
            $record,
            'query_pencarian',
            $queryPencarian ?? null
        );

    $defaultSource =
        data_get(
            $record,
            'sumber_temuan'
        );

    if (
        blank($defaultSource)
        && filled($sumberPencarian ?? null)
    ) {
        $defaultSource =
            data_get(
                config('tracer.sources'),
                $sumberPencarian.'.label',
                $sumberPencarian
            );
    }

    $tanggalRecord =
        data_get(
            $record,
            'tanggal_ditemukan'
        );

    $tanggalValue =
        old(
            'tanggal_ditemukan',
            $tanggalRecord
                ? $tanggalRecord->format('Y-m-d')
                : now()->format('Y-m-d')
        );
@endphp


{{-- STATUS --}}
<div>

    <label
        for="status_pelacakan"
        class="block mb-2 font-medium">

        Status Pelacakan
        <span class="text-red-500">*</span>

    </label>


    <select
        id="status_pelacakan"
        name="status_pelacakan"
        required
        class="w-full rounded-xl border border-slate-300 px-4 py-3">

        <option value="">
            -- Pilih status --
        </option>


        @foreach($statusOptions as $status)

            <option
                value="{{ $status }}"
                @selected(
                    old(
                        'status_pelacakan',
                        data_get(
                            $record,
                            'status_pelacakan'
                        )
                    ) === $status
                )>

                {{ $status }}

            </option>

        @endforeach

    </select>

</div>


{{-- JUDUL + SUMBER --}}
<div class="grid md:grid-cols-2 gap-5">

    <div>

        <label
            for="judul_temuan"
            class="block mb-2 font-medium">

            Judul Temuan

        </label>

        <input
            id="judul_temuan"
            type="text"
            name="judul_temuan"
            value="{{ old(
                'judul_temuan',
                data_get(
                    $record,
                    'judul_temuan'
                )
            ) }}"
            placeholder="Contoh: Profil LinkedIn alumni"
            class="w-full rounded-xl border border-slate-300 px-4 py-3">

    </div>


    <div>

        <label
            for="sumber_temuan"
            class="block mb-2 font-medium">

            Sumber Temuan

        </label>

        <input
            id="sumber_temuan"
            type="text"
            name="sumber_temuan"
            value="{{ old(
                'sumber_temuan',
                $defaultSource
            ) }}"
            placeholder="LinkedIn, Instagram, Website Perusahaan, dll."
            class="w-full rounded-xl border border-slate-300 px-4 py-3">

    </div>

</div>


{{-- LINK EVIDENCE --}}
<div>

    <label
        for="link_bukti"
        class="block mb-2 font-medium">

        Link Bukti / Evidence

    </label>

    <input
        id="link_bukti"
        type="url"
        name="link_bukti"
        placeholder="https://..."
        value="{{ old(
            'link_bukti',
            data_get(
                $record,
                'link_bukti'
            )
        ) }}"
        class="w-full rounded-xl border border-slate-300 px-4 py-3">


    <p class="text-xs text-slate-500 mt-2">
        Wajib jika Anda mengisi salah satu temuan Project 4.
    </p>

</div>


{{-- QUERY --}}
<div>

    <label
        for="query_pencarian"
        class="block mb-2 font-medium">

        Query Pencarian

    </label>

    <textarea
        id="query_pencarian"
        name="query_pencarian"
        rows="2"
        class="w-full rounded-xl border border-slate-300 px-4 py-3"
    >{{ old(
        'query_pencarian',
        $defaultQuery
    ) }}</textarea>

</div>


{{-- TANGGAL --}}
<div>

    <label
        for="tanggal_ditemukan"
        class="block mb-2 font-medium">

        Tanggal Ditemukan

    </label>

    <input
        id="tanggal_ditemukan"
        type="date"
        name="tanggal_ditemukan"
        max="{{ now()->format('Y-m-d') }}"
        value="{{ $tanggalValue }}"
        class="w-full rounded-xl border border-slate-300 px-4 py-3">

</div>


{{-- RINGKASAN --}}
<div>

    <label
        for="ringkasan_hasil"
        class="block mb-2 font-medium">

        Ringkasan Hasil

    </label>

    <textarea
        id="ringkasan_hasil"
        name="ringkasan_hasil"
        rows="4"
        placeholder="Jelaskan alasan kandidat dianggap cocok atau tidak cocok..."
        class="w-full rounded-xl border border-slate-300 px-4 py-3"
    >{{ old(
        'ringkasan_hasil',
        data_get(
            $record,
            'ringkasan_hasil'
        )
    ) }}</textarea>

</div>


{{-- SINYAL IDENTITAS --}}
<div class="rounded-2xl border border-violet-200 bg-violet-50 p-5">

    <div class="mb-4">

        <h3 class="font-semibold text-violet-900">
            Sinyal Kecocokan Identitas
        </h3>

        <p class="text-sm text-violet-700 mt-1">
            Sistem menghitung confidence otomatis.
        </p>

    </div>


    <div class="grid sm:grid-cols-2 gap-4">

        {{-- NAMA --}}
        <label class="rounded-xl border border-violet-200 bg-white p-4 cursor-pointer">

            <input
                type="hidden"
                name="cocok_nama"
                value="0">

            <div class="flex items-start gap-3">

                <input
                    type="checkbox"
                    name="cocok_nama"
                    value="1"
                    @checked(
                        old(
                            'cocok_nama',
                            data_get(
                                $signals,
                                'nama',
                                false
                            )
                        )
                    )
                    class="mt-1 rounded border-slate-300">

                <div>

                    <p class="font-medium">
                        Nama Cocok
                    </p>

                    <p class="text-xs text-slate-500">
                        Bobot 40
                    </p>

                </div>

            </div>

        </label>


        {{-- AFILIASI --}}
        <label class="rounded-xl border border-violet-200 bg-white p-4 cursor-pointer">

            <input
                type="hidden"
                name="cocok_afiliasi"
                value="0">

            <div class="flex items-start gap-3">

                <input
                    type="checkbox"
                    name="cocok_afiliasi"
                    value="1"
                    @checked(
                        old(
                            'cocok_afiliasi',
                            data_get(
                                $signals,
                                'afiliasi',
                                false
                            )
                        )
                    )
                    class="mt-1 rounded border-slate-300">

                <div>

                    <p class="font-medium">
                        Afiliasi Cocok
                    </p>

                    <p class="text-xs text-slate-500">
                        UMM / Prodi / NIM · Bobot 25
                    </p>

                </div>

            </div>

        </label>


        {{-- TIMELINE --}}
        <label class="rounded-xl border border-violet-200 bg-white p-4 cursor-pointer">

            <input
                type="hidden"
                name="cocok_timeline"
                value="0">

            <div class="flex items-start gap-3">

                <input
                    type="checkbox"
                    name="cocok_timeline"
                    value="1"
                    @checked(
                        old(
                            'cocok_timeline',
                            data_get(
                                $signals,
                                'timeline',
                                false
                            )
                        )
                    )
                    class="mt-1 rounded border-slate-300">

                <div>

                    <p class="font-medium">
                        Timeline Cocok
                    </p>

                    <p class="text-xs text-slate-500">
                        Tahun studi/lulus masuk akal · Bobot 20
                    </p>

                </div>

            </div>

        </label>


        {{-- BIDANG --}}
        <label class="rounded-xl border border-violet-200 bg-white p-4 cursor-pointer">

            <input
                type="hidden"
                name="cocok_bidang"
                value="0">

            <div class="flex items-start gap-3">

                <input
                    type="checkbox"
                    name="cocok_bidang"
                    value="1"
                    @checked(
                        old(
                            'cocok_bidang',
                            data_get(
                                $signals,
                                'bidang',
                                false
                            )
                        )
                    )
                    class="mt-1 rounded border-slate-300">

                <div>

                    <p class="font-medium">
                        Bidang Cocok
                    </p>

                    <p class="text-xs text-slate-500">
                        Bidang pendidikan/karier relevan · Bobot 15
                    </p>

                </div>

            </div>

        </label>

    </div>


    <div class="mt-4 rounded-xl border border-violet-200 bg-white p-3">

        <p class="text-xs text-violet-800">
            Confidence ≥ 80 disebut
            <strong>Kemungkinan Kuat</strong>.
            Namun field Project 4 hanya diterapkan otomatis jika
            status juga Teridentifikasi dan terdapat link bukti.
        </p>

    </div>

</div>


{{-- TEMUAN PROJECT 4 --}}
<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-5">

    <div class="mb-5">

        <h3 class="text-lg font-semibold text-emerald-900">
            Temuan 8 Kategori Project 4
        </h3>

        <p class="text-sm text-emerald-700 mt-1">
            Isi hanya field yang benar-benar terlihat atau dapat
            diverifikasi dari link evidence di atas.
        </p>

    </div>


    <div class="grid md:grid-cols-2 gap-5">

        {{-- LINKEDIN --}}
        <div>

            <label class="block mb-2 font-medium">
                LinkedIn
            </label>

            <input
                type="url"
                name="project4[linkedin]"
                value="{{ data_get(
                    $temuanProject4,
                    'linkedin'
                ) }}"
                placeholder="https://linkedin.com/in/..."
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- INSTAGRAM --}}
        <div>

            <label class="block mb-2 font-medium">
                Instagram
            </label>

            <input
                type="url"
                name="project4[instagram]"
                value="{{ data_get(
                    $temuanProject4,
                    'instagram'
                ) }}"
                placeholder="https://instagram.com/..."
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- FACEBOOK --}}
        <div>

            <label class="block mb-2 font-medium">
                Facebook
            </label>

            <input
                type="url"
                name="project4[facebook]"
                value="{{ data_get(
                    $temuanProject4,
                    'facebook'
                ) }}"
                placeholder="https://facebook.com/..."
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- TIKTOK --}}
        <div>

            <label class="block mb-2 font-medium">
                TikTok
            </label>

            <input
                type="url"
                name="project4[tiktok]"
                value="{{ data_get(
                    $temuanProject4,
                    'tiktok'
                ) }}"
                placeholder="https://tiktok.com/@..."
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- EMAIL --}}
        <div>

            <label class="block mb-2 font-medium">
                Email
            </label>

            <input
                type="email"
                name="project4[email]"
                value="{{ data_get(
                    $temuanProject4,
                    'email'
                ) }}"
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- NO HP --}}
        <div>

            <label class="block mb-2 font-medium">
                Nomor HP
            </label>

            <input
                type="text"
                name="project4[no_hp]"
                value="{{ data_get(
                    $temuanProject4,
                    'no_hp'
                ) }}"
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- TEMPAT BEKERJA --}}
        <div>

            <label class="block mb-2 font-medium">
                Tempat Bekerja
            </label>

            <input
                type="text"
                name="project4[tempat_bekerja]"
                value="{{ data_get(
                    $temuanProject4,
                    'tempat_bekerja'
                ) }}"
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- POSISI --}}
        <div>

            <label class="block mb-2 font-medium">
                Posisi / Jabatan
            </label>

            <input
                type="text"
                name="project4[posisi]"
                value="{{ data_get(
                    $temuanProject4,
                    'posisi'
                ) }}"
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- KATEGORI --}}
        <div>

            <label class="block mb-2 font-medium">
                PNS / Swasta / Wirausaha
            </label>

            <select
                name="project4[kategori_pekerjaan]"
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

                <option value="">
                    -- Pilih kategori --
                </option>


                @foreach($kategoriOptions as $kategori)

                    <option
                        value="{{ $kategori }}"
                        @selected(
                            data_get(
                                $temuanProject4,
                                'kategori_pekerjaan'
                            ) === $kategori
                        )>

                        {{ $kategori }}

                    </option>

                @endforeach

            </select>

        </div>


        {{-- SOSMED PERUSAHAAN --}}
        <div>

            <label class="block mb-2 font-medium">
                Sosial Media / Situs Tempat Bekerja
            </label>

            <input
                type="url"
                name="project4[sosmed_tempat_bekerja]"
                value="{{ data_get(
                    $temuanProject4,
                    'sosmed_tempat_bekerja'
                ) }}"
                placeholder="https://..."
                class="w-full rounded-xl border border-slate-300 px-4 py-3">

        </div>


        {{-- ALAMAT --}}
        <div class="md:col-span-2">

            <label class="block mb-2 font-medium">
                Alamat Tempat Bekerja
            </label>

            <textarea
                name="project4[alamat_bekerja]"
                rows="3"
                class="w-full rounded-xl border border-slate-300 px-4 py-3"
            >{{ data_get(
                $temuanProject4,
                'alamat_bekerja'
            ) }}</textarea>

        </div>

    </div>


    <div class="mt-5 rounded-xl border border-emerald-200 bg-white p-4">

        <p class="text-sm text-emerald-800">

            LinkedIn, Instagram, Facebook, dan TikTok tetap
            dihitung sebagai <strong>satu kategori Social Media</strong>
            pada Coverage/Completeness Project 4.

        </p>

    </div>

</div>