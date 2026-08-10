@php($record = $alumni ?? null)

<div class="grid md:grid-cols-2 gap-5">

    <div class="md:col-span-2">
        <label for="nama" class="block mb-2 font-medium">
            Nama Lengkap <span class="text-red-500">*</span>
        </label>
        <input
            id="nama"
            type="text"
            name="nama"
            value="{{ old('nama', data_get($record, 'nama')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
            required
        >
    </div>

    <div>
        <label for="nim" class="block mb-2 font-medium">NIM</label>
        <input
            id="nim"
            type="text"
            name="nim"
            value="{{ old('nim', data_get($record, 'nim')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="prodi" class="block mb-2 font-medium">Program Studi</label>
        <input
            id="prodi"
            type="text"
            name="prodi"
            value="{{ old('prodi', data_get($record, 'prodi')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="angkatan" class="block mb-2 font-medium">Angkatan</label>
        <input
            id="angkatan"
            type="number"
            name="angkatan"
            min="1900"
            max="{{ now()->year + 1 }}"
            value="{{ old('angkatan', data_get($record, 'angkatan')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="tahun_lulus" class="block mb-2 font-medium">Tahun Lulus</label>
        <input
            id="tahun_lulus"
            type="number"
            name="tahun_lulus"
            min="1900"
            max="{{ now()->year + 1 }}"
            value="{{ old('tahun_lulus', data_get($record, 'tahun_lulus')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="email" class="block mb-2 font-medium">Email</label>
        <input
            id="email"
            type="email"
            name="email"
            value="{{ old('email', data_get($record, 'email')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="no_hp" class="block mb-2 font-medium">Nomor HP</label>
        <input
            id="no_hp"
            type="text"
            name="no_hp"
            value="{{ old('no_hp', data_get($record, 'no_hp')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="tempat_bekerja" class="block mb-2 font-medium">
            Tempat Bekerja
        </label>
        <input
            id="tempat_bekerja"
            type="text"
            name="tempat_bekerja"
            value="{{ old('tempat_bekerja', data_get($record, 'tempat_bekerja')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="posisi" class="block mb-2 font-medium">
            Posisi/Jabatan
        </label>
        <input
            id="posisi"
            type="text"
            name="posisi"
            value="{{ old('posisi', data_get($record, 'posisi')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="kategori_pekerjaan" class="block mb-2 font-medium">
            Kategori Pekerjaan
        </label>

        <select
            id="kategori_pekerjaan"
            name="kategori_pekerjaan"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
            <option value="">-- Pilih kategori --</option>

            @foreach ($kategoriOptions as $kategori)
                <option
                    value="{{ $kategori }}"
                    @selected(
                        old(
                            'kategori_pekerjaan',
                            data_get($record, 'kategori_pekerjaan')
                        ) === $kategori
                    )
                >
                    {{ $kategori }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="md:col-span-2">
        <label for="alamat_bekerja" class="block mb-2 font-medium">
            Alamat Tempat Bekerja
        </label>

        <textarea
            id="alamat_bekerja"
            name="alamat_bekerja"
            rows="3"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >{{ old('alamat_bekerja', data_get($record, 'alamat_bekerja')) }}</textarea>
    </div>

    <div>
        <label for="linkedin" class="block mb-2 font-medium">LinkedIn</label>
        <input
            id="linkedin"
            type="url"
            name="linkedin"
            placeholder="https://..."
            value="{{ old('linkedin', data_get($record, 'linkedin')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="instagram" class="block mb-2 font-medium">Instagram</label>
        <input
            id="instagram"
            type="url"
            name="instagram"
            placeholder="https://..."
            value="{{ old('instagram', data_get($record, 'instagram')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="facebook" class="block mb-2 font-medium">Facebook</label>
        <input
            id="facebook"
            type="url"
            name="facebook"
            placeholder="https://..."
            value="{{ old('facebook', data_get($record, 'facebook')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div>
        <label for="tiktok" class="block mb-2 font-medium">TikTok</label>
        <input
            id="tiktok"
            type="url"
            name="tiktok"
            placeholder="https://..."
            value="{{ old('tiktok', data_get($record, 'tiktok')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div class="md:col-span-2">
        <label for="sosmed_tempat_bekerja" class="block mb-2 font-medium">
            Situs/Sosial Media Tempat Bekerja
        </label>

        <input
            id="sosmed_tempat_bekerja"
            type="url"
            name="sosmed_tempat_bekerja"
            placeholder="https://..."
            value="{{ old('sosmed_tempat_bekerja', data_get($record, 'sosmed_tempat_bekerja')) }}"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >
    </div>

    <div class="md:col-span-2">
        <label for="catatan" class="block mb-2 font-medium">Catatan</label>

        <textarea
            id="catatan"
            name="catatan"
            rows="4"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
        >{{ old('catatan', data_get($record, 'catatan')) }}</textarea>

    </div>
