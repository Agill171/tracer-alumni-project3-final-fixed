<x-layouts.app-tracer>
    <x-slot:title>Edit Hasil Pelacakan</x-slot:title>


    @php
        $fromBatch =
            $fromBatch
            ?? request('from_batch');

        $alumni =
            $pelacakan->alumni;

        $backUrl =
            route(
                'alumni.show',
                [
                    'alumni' =>
                        $alumni->id,

                    'from_batch' =>
                        $fromBatch,
                ]
            );
    @endphp


    <div class="max-w-4xl mx-auto">

        <div class="mb-6">

            <h2 class="text-3xl font-bold text-slate-900">
                Edit Hasil Pelacakan
            </h2>

            <p class="text-slate-600 mt-1">

                Perbarui evidence dan temuan Project 4 untuk

                <span class="font-medium">
                    {{ $alumni->nama }}
                </span>.

            </p>

        </div>


        @if($errors->any())

            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-red-700">

                <ul class="list-disc list-inside space-y-1">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        @endif


        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">

            <div class="mb-6">

                <a
                    href="{{ $backUrl }}"
                    class="inline-block px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">

                    ← Kembali ke Detail Alumni

                </a>

            </div>


            <form
                action="{{ route(
                    'pelacakan.update',
                    [
                        'pelacakan' =>
                            $pelacakan->id,

                        'from_batch' =>
                            $fromBatch,
                    ]
                ) }}"
                method="POST"
                class="space-y-5">

                @csrf
                @method('PUT')


                @if($fromBatch)

                    <input
                        type="hidden"
                        name="from_batch"
                        value="{{ $fromBatch }}">

                @endif


                @include(
                    'pelacakan.partials.form',
                    [
                        'pelacakan' =>
                            $pelacakan,

                        'alumni' =>
                            $alumni,

                        'queryPencarian' =>
                            null,

                        'sumberPencarian' =>
                            null,
                    ]
                )


                <div class="flex flex-wrap gap-3">

                    <button
                        type="submit"
                        class="px-5 py-3 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-medium">

                        Perbarui Hasil Pelacakan

                    </button>


                    <a
                        href="{{ $backUrl }}"
                        class="px-5 py-3 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-medium">

                        Batal

                    </a>

                </div>

            </form>

        </div>

    </div>

</x-layouts.app-tracer>