<x-layouts.app-tracer>
    <x-slot:title>Import Data Alumni</x-slot:title>

    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-slate-900">Import Data Alumni</h2>
            <p class="text-slate-600 mt-1">Unggah XLSX, XLS, atau CSV untuk menambahkan dan memperbarui data secara massal.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="mb-5 flex flex-wrap gap-3">
                <a href="{{ route('alumni.index') }}" class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 font-medium">← Kembali</a>
                <a href="{{ asset('template_import_alumni.csv') }}" download
                   class="px-4 py-2 rounded-xl bg-sky-100 hover:bg-sky-200 text-sky-800 font-medium">Unduh Template CSV</a>
            </div>

            <form action="{{ route('alumni.import.excel') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label for="file_excel" class="block mb-2 font-medium">Pilih file, maksimal 50 MB</label>
                    <input id="file_excel" type="file" name="file_excel" accept=".xlsx,.xls,.csv"
                           class="block w-full rounded-xl border border-slate-300 px-4 py-3 bg-white" required>
                </div>
                <button type="submit" class="px-5 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-medium">
                    Kirim ke Antrean Import
                </button>
            </form>

            <div class="mt-6 rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900">
                <p class="font-semibold mb-2">Catatan proses</p>
                <p>Import diproses per 100 baris melalui queue untuk mencegah timeout. Pastikan menjalankan <code class="font-mono bg-white/70 px-1 rounded">php artisan queue:work</code>.</p>
            </div>

            <div class="mt-4 rounded-2xl bg-slate-50 border border-slate-200 p-4 text-sm text-slate-700">
                <p class="font-semibold mb-2">Header yang didukung</p>
                <p class="break-words">nama, nim, prodi, angkatan, tahun_lulus, email, no_hp, tempat_bekerja, alamat_bekerja, posisi, kategori_pekerjaan, linkedin, instagram, facebook, tiktok, sosmed_tempat_bekerja, status_verifikasi, catatan</p>
            </div>
        </div>
    </div>
</x-layouts.app-tracer>
