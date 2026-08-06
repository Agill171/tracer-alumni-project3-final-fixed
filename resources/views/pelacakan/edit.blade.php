<x-layouts.app-tracer>
    <x-slot:title>Edit Hasil Pelacakan</x-slot:title>

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-slate-900">Edit Hasil Pelacakan</h2>
            <p class="text-slate-600 mt-1">Perbarui bukti dan hasil verifikasi kandidat alumni.</p>
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
                <a href="{{ route('alumni.show', $pelacakan->alumni) }}"
                   class="inline-block px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                    ← Kembali ke Detail Alumni
                </a>
            </div>

            <form action="{{ route('pelacakan.update', $pelacakan) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')
                @include('pelacakan.partials.form')

                <button type="submit" class="px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium">
                    Perbarui Hasil Pelacakan
                </button>
            </form>
        </div>
    </div>
</x-layouts.app-tracer>
