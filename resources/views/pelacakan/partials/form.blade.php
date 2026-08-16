@php
    $record = $pelacakan ?? null;
    $signals = data_get($record, 'sinyal_identitas', []);

    $prefillQuery = old(
        'query_pencarian',
        data_get(
            $record,
            'query_pencarian',
            $queryPencarian ?? null
        )
    );

    $prefillSourceKey = $sumberPencarian ?? null;

    $prefillSourceLabel = $prefillSourceKey
        ? data_get(
            config('tracer.sources'),
            $prefillSourceKey.'.label',
            $prefillSourceKey
        )
        : null;

    $prefillSource = old(
        'sumber_temuan',
        data_get(
            $record,
            'sumber_temuan',
            $prefillSourceLabel
        )
    );
@endphp

<div>
    <label for="status_pelacakan"
           class="block mb-2 font-medium">
        Status Alumni
        <span class="text-red-500">*</span>
    </label>

    <select id="status_pelacakan"
            name="status_pelacakan"
            class="w-full rounded-xl border border-slate-300 px-4 py-3"
            required>
        <option value="">
            -- Pilih status --
        </option>

        @foreach($statusOptions as $status)
            <option value="{{ $status }}"
                    @selected(
                        old(
                            'status_pelacakan',
                            data_get($record, 'status_pelacakan')
                        ) === $status
                    )>
                {{ $status }}
            </option>
        @endforeach
    </select>
</div>

<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label for="judul_temuan"
               class="block mb-2 font-medium">
            Judul Temuan
        </label>

        <input id="judul_temuan"
               type="text"
               name="judul_temuan"
               value="{{ old(
                   'judul_temuan',
                   data_get($record, 'judul_temuan')
               ) }}"
               class="w-full rounded-xl border border-slate-300 px-4 py-3"
               placeholder="Contoh: Profil LinkedIn alumni">
    </div>

    <div>
        <label for="sumber_temuan"
               class="block mb-2 font-medium">
            Sumber Temuan
        </label>

        <input id="sumber_temuan"
               type="text"
               name="sumber_temuan"
               value="{{ $prefillSource }}"
               class="w-full rounded-xl border border-slate-300 px-4 py-3"
               placeholder="LinkedIn, GitHub, website perusahaan, dan sebagainya">
    </div>
</div>

<div>
    <label for="link_bukti"
           class="block mb-2 font-medium">
        Link Bukti
    </label>

    <input id="link_bukti"
           type="url"
           name="link_bukti"
           value="{{ old(
               'link_bukti',
               data_get($record, 'link_bukti')
           ) }}"
           class="w-full rounded-xl border border-slate-300 px-4 py-3"
           placeholder="https://...">
</div>

<div>
    <label for="query_pencarian"
           class="block mb-2 font-medium">
        Query yang Digunakan
    </label>

    <textarea id="query_pencarian"
              name="query_pencarian"
              rows="2"
              class="w-full rounded-xl border border-slate-300 px-4 py-3"
              placeholder='Contoh: "Nama Alumni" Universitas Muhammadiyah Malang'>{{ $prefillQuery }}</textarea>

    @if(filled($queryPencarian ?? null))
        <p class="mt-2 text-xs text-emerald-700">
            Query ini otomatis dibawa dari tautan pencarian yang dipilih.
        </p>
    @endif
</div>

<div>
    <label for="ringkasan_hasil"
           class="block mb-2 font-medium">
        Ringkasan Hasil
    </label>

    <textarea id="ringkasan_hasil"
              name="ringkasan_hasil"
              rows="5"
              class="w-full rounded-xl border border-slate-300 px-4 py-3"
              placeholder="Ringkas alasan mengapa kandidat dianggap cocok atau tidak cocok.">{{ old(
                  'ringkasan_hasil',
                  data_get($record, 'ringkasan_hasil')
              ) }}</textarea>
</div>

<div>
    <label for="tanggal_ditemukan"
           class="block mb-2 font-medium">
        Tanggal Ditemukan
    </label>

    <input id="tanggal_ditemukan"
           type="date"
           name="tanggal_ditemukan"
           max="{{ now()->format('Y-m-d') }}"
           value="{{ old(
               'tanggal_ditemukan',
               data_get($record, 'tanggal_ditemukan')?->format('Y-m-d')
           ) }}"
           class="w-full rounded-xl border border-slate-300 px-4 py-3">
</div>

<fieldset class="rounded-2xl border border-slate-200 p-5">
    <legend class="px-2 font-semibold">
        Sinyal Kecocokan Identitas
    </legend>

    <p class="text-sm text-slate-600 mb-4">
        Confidence score dihitung otomatis:
        nama 40%, afiliasi 25%, timeline 20%, dan bidang 15%.
    </p>

    <div class="grid sm:grid-cols-2 gap-3">
        @foreach([
            'cocok_nama' => [
                'label' => 'Nama sesuai',
                'signal' => 'nama',
                'weight' => 40,
            ],

            'cocok_afiliasi' => [
                'label' => 'Afiliasi sesuai',
                'signal' => 'afiliasi',
                'weight' => 25,
            ],

            'cocok_timeline' => [
                'label' => 'Timeline sesuai',
                'signal' => 'timeline',
                'weight' => 20,
            ],

            'cocok_bidang' => [
                'label' => 'Bidang/topik sesuai',
                'signal' => 'bidang',
                'weight' => 15,
            ],
        ] as $field => $option)
            <label class="flex items-center gap-3 rounded-xl bg-slate-50 border border-slate-200 p-4 cursor-pointer">
                <input type="hidden"
                       name="{{ $field }}"
                       value="0">

                <input type="checkbox"
                       name="{{ $field }}"
                       value="1"
                       @checked(
                           (bool) old(
                               $field,
                               data_get(
                                   $signals,
                                   $option['signal'],
                                   false
                               )
                           )
                       )
                       class="rounded border-slate-300 text-blue-600">

                <span>
                    {{ $option['label'] }}

                    <span class="text-slate-500">
                        ({{ $option['weight'] }}%)
                    </span>
                </span>
            </label>
        @endforeach
    </div>
</fieldset>
