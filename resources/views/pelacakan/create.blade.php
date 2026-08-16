<x-layouts.app-tracer>
    <x-slot:title>Catat Hasil Pelacakan</x-slot:title>

    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-slate-900">
                Catat Hasil Pelacakan
            </h2>

            <p class="text-slate-600 mt-1">
                Simpan kandidat, bukti, dan sinyal kecocokan untuk
                <span class="font-medium">{{ $alumni->nama }}</span>.
            </p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(filled($queryPencarian ?? null))
            <div class="mb-6 rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <p class="text-sm font-semibold text-blue-900">
                    Query yang sedang diverifikasi
                </p>

                <p class="mt-2 text-sm text-blue-800 break-words">
                    {{ $queryPencarian }}
                </p>

                @if(filled($sumberPencarian ?? null))
                    <p class="mt-2 text-xs text-blue-700">
                        Sumber:
                        <span class="font-medium">
                            {{ data_get(
                                config('tracer.sources'),
                                $sumberPencarian.'.label',
                                $sumberPencarian
                            ) }}
                        </span>
                    </p>
                @endif
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <a href="{{ route('alumni.show', $alumni) }}"
                   class="inline-block px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                    ← Kembali ke Detail Alumni
                </a>

                <div class="text-sm text-slate-600">
                    <span>NIM: {{ $alumni->nim ?: '-' }}</span>

                    @if($alumni->prodi)
                        <span class="mx-2">·</span>
                        <span>{{ $alumni->prodi }}</span>
                    @endif
                </div>
            </div>

            <div class="mb-6 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <p class="text-sm font-semibold text-amber-800">
                    Verifikasi sebelum menyimpan
                </p>

                <p class="text-sm text-amber-700 mt-1">
                    Jangan menyimpulkan identitas hanya berdasarkan kesamaan nama.
                    Periksa nama, afiliasi, timeline, bidang, dan link bukti sebelum menentukan status.
                </p>
            </div>

            <form action="{{ route('pelacakan.store', $alumni) }}"
                  method="POST"
                  class="space-y-5">
                @csrf

                @include('pelacakan.partials.form', [
                    'queryPencarian' => $queryPencarian ?? null,
                    'sumberPencarian' => $sumberPencarian ?? null,
                ])

                <div class="flex flex-wrap gap-3">
                    <button type="submit"
                            class="px-5 py-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-medium">
                        Simpan Hasil Pelacakan
                    </button>

                    <a href="{{ route('alumni.show', $alumni) }}"
                       class="px-5 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app-tracer>
