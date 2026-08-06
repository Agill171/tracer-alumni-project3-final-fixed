<x-layouts.app-tracer>
    <x-slot:title>Tambah Alumni</x-slot:title>

    <div class="max-w-5xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-slate-900">Tambah Data Alumni</h2>
            <p class="text-slate-600 mt-1">Masukkan identitas dasar, pekerjaan, dan profil publik alumni.</p>
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
            <div class="mb-6">
                <a href="{{ route('alumni.index') }}"
                   class="inline-block px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                    ← Kembali ke Data Alumni
                </a>
            </div>

            <form action="{{ route('alumni.store') }}" method="POST" class="space-y-6">
                @csrf
                @include('alumni.partials.form')

                <button type="submit"
                        class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">
                    Simpan Data Alumni
                </button>
            </form>
        </div>
    </div>
</x-layouts.app-tracer>
