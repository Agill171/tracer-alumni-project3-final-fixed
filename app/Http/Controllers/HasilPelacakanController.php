<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\HasilPelacakan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HasilPelacakanController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | FIELD PROJECT 4
    |--------------------------------------------------------------------------
    |
    | Sesuai rubrik Project 4:
    |
    | 1. Social Media Alumni
    |    - LinkedIn
    |    - Instagram
    |    - Facebook
    |    - TikTok
    |
    | 2. Email
    | 3. Nomor HP
    | 4. Tempat Bekerja
    | 5. Alamat Tempat Bekerja
    | 6. Posisi / Jabatan
    | 7. PNS / Swasta / Wirausaha
    | 8. Social Media / Situs Tempat Bekerja
    |
    | Empat social media alumni tetap dihitung sebagai SATU kategori
    | pada Coverage / Completeness dashboard.
    |
    */

    private const PROJECT4_FIELDS = [
        'linkedin',
        'instagram',
        'facebook',
        'tiktok',
        'email',
        'no_hp',
        'tempat_bekerja',
        'alamat_bekerja',
        'posisi',
        'kategori_pekerjaan',
        'sosmed_tempat_bekerja',
    ];


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create(
        Request $request,
        Alumni $alumni
    ) {
        return view('pelacakan.create', [
            'alumni' =>
                $alumni,

            'statusOptions' =>
                Alumni::statusOptions(),

            'kategoriOptions' =>
                Alumni::kategoriPekerjaanOptions(),

            'queryPencarian' =>
                $request->query('query'),

            'sumberPencarian' =>
                $request->query('source'),

            'fromBatch' =>
                $this->fromBatchId($request),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        Alumni $alumni
    ) {
        $fromBatch =
            $this->fromBatchId($request);

        $payload =
            $this->validatedPayload($request);


        $result =
            DB::transaction(
                function () use (
                    $request,
                    $alumni,
                    $payload
                ) {
                    $payload['alumni_id'] =
                        $alumni->id;

                    $payload['user_id'] =
                        $request->user()->id;


                    /*
                     * Simpan evidence / hasil pelacakan.
                     */
                    $pelacakan =
                        HasilPelacakan::create(
                            $payload
                        );


                    /*
                     * Terapkan Project 4 apabila evidence
                     * sudah memenuhi syarat.
                     */
                    $applyResult =
                        $this->applyVerifiedProject4(
                            $alumni,
                            $pelacakan
                        );


                    /*
                     * Sinkronkan status alumni berdasarkan
                     * hasil pelacakan terbaru.
                     */
                    $this->updateStatusVerifikasiAlumni(
                        $alumni
                    );


                    return [
                        'pelacakan' =>
                            $pelacakan,

                        'applyResult' =>
                            $applyResult,
                    ];
                }
            );


        $message =
            $this->buildSuccessMessage(
                'Hasil pelacakan berhasil ditambahkan.',
                $result['pelacakan'],
                $result['applyResult']
            );


        return redirect()
            ->route(
                'alumni.show',
                $this->alumniRouteParameters(
                    $alumni,
                    $fromBatch
                )
            )
            ->with(
                'success',
                $message
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        HasilPelacakan $pelacakan
    ) {
        $pelacakan->load(
            'alumni'
        );


        return view('pelacakan.edit', [
            'pelacakan' =>
                $pelacakan,

            'alumni' =>
                $pelacakan->alumni,

            'statusOptions' =>
                Alumni::statusOptions(),

            'kategoriOptions' =>
                Alumni::kategoriPekerjaanOptions(),

            'fromBatch' =>
                $this->fromBatchId($request),
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        HasilPelacakan $pelacakan
    ) {
        $fromBatch =
            $this->fromBatchId($request);

        $alumni =
            $pelacakan->alumni;

        $payload =
            $this->validatedPayload($request);


        $result =
            DB::transaction(
                function () use (
                    $pelacakan,
                    $alumni,
                    $payload
                ) {
                    /*
                     * Update evidence.
                     */
                    $pelacakan->update(
                        $payload
                    );


                    $pelacakan->refresh();


                    /*
                     * Terapkan Project 4 apabila evidence
                     * sekarang memenuhi syarat.
                     */
                    $applyResult =
                        $this->applyVerifiedProject4(
                            $alumni,
                            $pelacakan
                        );


                    /*
                     * Sinkronkan status alumni.
                     */
                    $this->updateStatusVerifikasiAlumni(
                        $alumni
                    );


                    return [
                        'pelacakan' =>
                            $pelacakan,

                        'applyResult' =>
                            $applyResult,
                    ];
                }
            );


        $message =
            $this->buildSuccessMessage(
                'Hasil pelacakan berhasil diperbarui.',
                $result['pelacakan'],
                $result['applyResult']
            );


        return redirect()
            ->route(
                'alumni.show',
                $this->alumniRouteParameters(
                    $alumni,
                    $fromBatch
                )
            )
            ->with(
                'success',
                $message
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Request $request,
        HasilPelacakan $pelacakan
    ) {
        $fromBatch =
            $this->fromBatchId($request);

        $alumni =
            $pelacakan->alumni;


        DB::transaction(
            function () use (
                $pelacakan,
                $alumni
            ) {
                $pelacakan->delete();


                /*
                 * Status alumni disesuaikan dengan
                 * evidence yang masih tersisa.
                 */
                $this->updateStatusVerifikasiAlumni(
                    $alumni
                );
            }
        );


        /*
         * Data Project 4 yang pernah diterapkan ke alumni
         * TIDAK dihapus otomatis.
         *
         * Alasannya:
         * field tersebut mungkin sudah didukung evidence lain.
         */
        return redirect()
            ->route(
                'alumni.show',
                $this->alumniRouteParameters(
                    $alumni,
                    $fromBatch
                )
            )
            ->with(
                'success',
                'Hasil pelacakan berhasil dihapus. '
                .'Data Project 4 yang sebelumnya sudah diterapkan '
                .'tidak dihapus otomatis.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATED PAYLOAD
    |--------------------------------------------------------------------------
    */

    private function validatedPayload(
        Request $request
    ): array {
        $validated =
            $request->validate([
                /*
                 * Status pelacakan.
                 */
                'status_pelacakan' => [
                    'required',

                    Rule::in(
                        Alumni::statusOptions()
                    ),
                ],


                /*
                 * Evidence.
                 */
                'judul_temuan' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'sumber_temuan' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'link_bukti' => [
                    'nullable',
                    'url:http,https',
                    'max:2048',
                ],

                'query_pencarian' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'ringkasan_hasil' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],

                /*
                 * Nama database tetap tanggal_ditemukan.
                 * Secara konsep juga digunakan sebagai
                 * tanggal penelusuran untuk kasus tidak ditemukan.
                 */
                'tanggal_ditemukan' => [
                    'nullable',
                    'date',
                    'before_or_equal:today',
                ],


                /*
                 * Sinyal identitas.
                 */
                'cocok_nama' => [
                    'nullable',
                    'boolean',
                ],

                'cocok_afiliasi' => [
                    'nullable',
                    'boolean',
                ],

                'cocok_timeline' => [
                    'nullable',
                    'boolean',
                ],

                'cocok_bidang' => [
                    'nullable',
                    'boolean',
                ],


                /*
                 * Snapshot temuan Project 4.
                 */
                'project4' => [
                    'nullable',
                    'array',
                ],

                'project4.linkedin' => [
                    'nullable',
                    'url:http,https',
                    'max:2048',
                ],

                'project4.instagram' => [
                    'nullable',
                    'url:http,https',
                    'max:2048',
                ],

                'project4.facebook' => [
                    'nullable',
                    'url:http,https',
                    'max:2048',
                ],

                'project4.tiktok' => [
                    'nullable',
                    'url:http,https',
                    'max:2048',
                ],

                'project4.email' => [
                    'nullable',
                    'email:rfc',
                    'max:255',
                ],

                'project4.no_hp' => [
                    'nullable',
                    'string',
                    'max:30',
                    'regex:/^[0-9+()\-\s]+$/',
                ],

                'project4.tempat_bekerja' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'project4.alamat_bekerja' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'project4.posisi' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'project4.kategori_pekerjaan' => [
                    'nullable',

                    Rule::in(
                        Alumni::kategoriPekerjaanOptions()
                    ),
                ],

                'project4.sosmed_tempat_bekerja' => [
                    'nullable',
                    'url:http,https',
                    'max:2048',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | NORMALISASI PROJECT 4
        |--------------------------------------------------------------------------
        */

        $project4 =
            $this->normalizeProject4(
                $validated['project4']
                    ?? []
            );


        $status =
            $validated['status_pelacakan'];


        /*
        |--------------------------------------------------------------------------
        | STATUS TIDAK DITEMUKAN
        |--------------------------------------------------------------------------
        |
        | Kasus:
        |
        | seluruh query sudah diperiksa,
        | tetapi tidak ditemukan kandidat relevan.
        |
        | Maka:
        |
        | confidence_score    = NULL
        | kategori_kecocokan  = NULL
        | Project 4           = harus kosong
        |
        | Ini BERBEDA dari:
        | kandidat ditemukan tetapi ternyata tidak cocok.
        |
        */

        if (
            $status
            === Alumni::STATUS_TIDAK_DITEMUKAN
        ) {
            if ($project4 !== []) {
                throw ValidationException::withMessages([
                    'project4' =>
                        'Temuan Project 4 harus dikosongkan karena status '
                        .'pelacakan adalah Belum Ditemukan di Sumber Publik.',
                ]);
            }


            unset(
                $validated['cocok_nama'],
                $validated['cocok_afiliasi'],
                $validated['cocok_timeline'],
                $validated['cocok_bidang'],
                $validated['project4']
            );


            /*
             * Tidak ada kandidat yang dinilai,
             * sehingga tidak ada confidence.
             */
            $validated['sinyal_identitas'] =
                null;

            $validated['confidence_score'] =
                null;

            $validated['kategori_kecocokan'] =
                null;

            $validated['temuan_project4'] =
                null;


            return $validated;
        }


        /*
        |--------------------------------------------------------------------------
        | STATUS BELUM DILACAK
        |--------------------------------------------------------------------------
        |
        | Status ini juga tidak mempunyai confidence/evidence
        | karena proses pencarian belum benar-benar dilakukan.
        |
        */

        if (
            $status
            === Alumni::STATUS_BELUM_DILACAK
        ) {
            if ($project4 !== []) {
                throw ValidationException::withMessages([
                    'project4' =>
                        'Temuan Project 4 tidak dapat disimpan '
                        .'dengan status Belum Dilacak.',
                ]);
            }


            unset(
                $validated['cocok_nama'],
                $validated['cocok_afiliasi'],
                $validated['cocok_timeline'],
                $validated['cocok_bidang'],
                $validated['project4']
            );


            $validated['sinyal_identitas'] =
                null;

            $validated['confidence_score'] =
                null;

            $validated['kategori_kecocokan'] =
                null;

            $validated['temuan_project4'] =
                null;


            return $validated;
        }


        /*
        |--------------------------------------------------------------------------
        | PROJECT 4 WAJIB MEMILIKI LINK BUKTI
        |--------------------------------------------------------------------------
        */

        if (
            $project4 !== []
            && blank(
                $validated['link_bukti']
                    ?? null
            )
        ) {
            throw ValidationException::withMessages([
                'link_bukti' =>
                    'Link bukti / evidence wajib diisi jika Anda '
                    .'mencatat salah satu temuan Project 4.',
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | SINYAL IDENTITAS
        |--------------------------------------------------------------------------
        */

        $signals = [
            'nama' =>
                $request->boolean(
                    'cocok_nama'
                ),

            'afiliasi' =>
                $request->boolean(
                    'cocok_afiliasi'
                ),

            'timeline' =>
                $request->boolean(
                    'cocok_timeline'
                ),

            'bidang' =>
                $request->boolean(
                    'cocok_bidang'
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | CONFIDENCE SCORE
        |--------------------------------------------------------------------------
        |
        | Nama      = 40
        | Afiliasi  = 25
        | Timeline  = 20
        | Bidang    = 15
        |
        | Maksimum  = 100
        |
        */

        $score =
            ($signals['nama']
                ? 40
                : 0)

            + ($signals['afiliasi']
                ? 25
                : 0)

            + ($signals['timeline']
                ? 20
                : 0)

            + ($signals['bidang']
                ? 15
                : 0);


        unset(
            $validated['cocok_nama'],
            $validated['cocok_afiliasi'],
            $validated['cocok_timeline'],
            $validated['cocok_bidang'],
            $validated['project4']
        );


        $validated['sinyal_identitas'] =
            $signals;


        $validated['confidence_score'] =
            $score;


        $validated['kategori_kecocokan'] =
            HasilPelacakan::classify(
                $score
            );


        /*
         * Snapshot evidence.
         */
        $validated['temuan_project4'] =
            $project4 !== []
                ? $project4
                : null;


        return $validated;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALISASI PROJECT 4
    |--------------------------------------------------------------------------
    */

    private function normalizeProject4(
        array $values
    ): array {
        $result = [];


        foreach (
            self::PROJECT4_FIELDS
            as $field
        ) {
            $value =
                $values[$field]
                ?? null;


            /*
             * Bersihkan whitespace.
             */
            if (
                is_string(
                    $value
                )
            ) {
                $value =
                    trim(
                        $value
                    );
            }


            /*
             * Simpan hanya nilai yang benar-benar terisi.
             */
            if (
                filled(
                    $value
                )
            ) {
                $result[$field] =
                    $value;
            }
        }


        return $result;
    }


    /*
    |--------------------------------------------------------------------------
    | APPLY VERIFIED PROJECT 4
    |--------------------------------------------------------------------------
    |
    | Field Project 4 baru boleh otomatis diterapkan ke Alumni jika:
    |
    | 1. Status = Teridentifikasi dari Sumber Publik
    | 2. Confidence >= 80
    | 3. Ada link evidence
    | 4. Ada nilai Project 4 yang benar-benar ditemukan
    |
    | Field alumni yang sudah terisi TIDAK ditimpa otomatis.
    |
    */

    private function applyVerifiedProject4(
        Alumni $alumni,
        HasilPelacakan $pelacakan
    ): array {
        $project4 =
            $pelacakan->temuan_project4
            ?? [];


        $eligible =
            $pelacakan->status_pelacakan
                === Alumni::STATUS_TERIDENTIFIKASI

            && $pelacakan->confidence_score
                !== null

            && (int) $pelacakan->confidence_score
                >= 80

            && filled(
                $pelacakan->link_bukti
            );


        /*
         * Tidak memenuhi syarat.
         */
        if (
            ! $eligible
            || $project4 === []
        ) {
            return [
                'eligible' =>
                    $eligible,

                'applied' =>
                    [],

                'conflicts' =>
                    [],
            ];
        }


        $updates =
            [];

        $conflicts =
            [];


        foreach (
            $project4
            as $field => $value
        ) {
            /*
             * Proteksi agar hanya field Project 4
             * yang diizinkan yang dapat diterapkan.
             */
            if (
                ! in_array(
                    $field,
                    self::PROJECT4_FIELDS,
                    true
                )
                || blank(
                    $value
                )
            ) {
                continue;
            }


            $current =
                $alumni->getAttribute(
                    $field
                );


            /*
             * Jika field alumni masih kosong:
             * isi otomatis.
             */
            if (
                blank(
                    $current
                )
            ) {
                $updates[$field] =
                    $value;

                continue;
            }


            /*
             * Kalau sudah ada nilai berbeda:
             * jangan timpa otomatis.
             *
             * Masukkan sebagai conflict agar bisa
             * diverifikasi manual.
             */
            if (
                trim(
                    (string) $current
                )
                !==
                trim(
                    (string) $value
                )
            ) {
                $conflicts[] =
                    $field;
            }
        }


        /*
         * Apply field yang masih kosong.
         */
        if (
            $updates !== []
        ) {
            $alumni->update(
                $updates
            );
        }


        return [
            'eligible' =>
                true,

            'applied' =>
                array_keys(
                    $updates
                ),

            'conflicts' =>
                $conflicts,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */

    private function buildSuccessMessage(
        string $baseMessage,
        HasilPelacakan $pelacakan,
        array $applyResult
    ): string {
        /*
         * Kasus tidak ditemukan.
         */
        if (
            $pelacakan->status_pelacakan
            === Alumni::STATUS_TIDAK_DITEMUKAN
        ) {
            return $baseMessage
                .' Alumni dicatat sebagai belum ditemukan di sumber publik. '
                .'Coverage Project 4 tidak berubah.';
        }


        $project4 =
            $pelacakan->temuan_project4
            ?? [];


        /*
         * Tidak ada Project 4 pada evidence ini.
         */
        if (
            $project4 === []
        ) {
            return $baseMessage;
        }


        /*
         * Ada Project 4 tetapi evidence belum cukup kuat
         * untuk mengubah data alumni.
         */
        if (
            ! $applyResult['eligible']
        ) {
            return $baseMessage
                .' Temuan Project 4 tersimpan sebagai kandidat, '
                .'tetapi belum diterapkan ke data alumni karena '
                .'belum memenuhi seluruh syarat: '
                .'status Teridentifikasi, confidence minimal 80, '
                .'dan link evidence tersedia.';
        }


        $message =
            $baseMessage;


        $appliedCount =
            count(
                $applyResult['applied']
            );


        /*
         * Field berhasil diterapkan.
         */
        if (
            $appliedCount > 0
        ) {
            $message .=
                " {$appliedCount} field Project 4 berhasil diterapkan "
                .'ke data alumni.';
        }


        $conflictCount =
            count(
                $applyResult['conflicts']
            );


        /*
         * Field sudah ada dengan nilai berbeda.
         */
        if (
            $conflictCount > 0
        ) {
            $message .=
                " {$conflictCount} field tidak ditimpa otomatis "
                .'karena data alumni sudah mempunyai nilai berbeda.';
        }


        /*
         * Evidence valid tetapi semua field ternyata
         * sudah ada dengan nilai yang sama.
         */
        if (
            $appliedCount === 0
            && $conflictCount === 0
        ) {
            $message .=
                ' Temuan Project 4 sudah tersimpan. '
                .'Tidak ada field kosong yang perlu diperbarui.';
        }


        return $message;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE STATUS VERIFIKASI ALUMNI
    |--------------------------------------------------------------------------
    */

    private function updateStatusVerifikasiAlumni(
        Alumni $alumni
    ): void {
        /*
         * Ambil evidence paling akhir.
         */
        $pelacakanTerakhir =
            $alumni
                ->hasilPelacakans()
                ->orderByDesc(
                    'tanggal_ditemukan'
                )
                ->orderByDesc(
                    'id'
                )
                ->first();


        /*
         * Jika tidak ada evidence:
         * kembali menjadi Belum Dilacak.
         */
        $alumni->update([
            'status_verifikasi' =>
                $pelacakanTerakhir
                    ?->status_pelacakan
                ?? Alumni::STATUS_BELUM_DILACAK,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | FROM BATCH
    |--------------------------------------------------------------------------
    */

    private function fromBatchId(
        Request $request
    ): ?int {
        $value =
            $request->input(
                'from_batch',
                $request->query(
                    'from_batch'
                )
            );


        if (
            ! is_numeric(
                $value
            )
            || (int) $value < 1
        ) {
            return null;
        }


        return (int) $value;
    }


    /*
    |--------------------------------------------------------------------------
    | ROUTE PARAMETERS DETAIL ALUMNI
    |--------------------------------------------------------------------------
    */

    private function alumniRouteParameters(
        Alumni $alumni,
        ?int $fromBatch = null
    ): array {
        $parameters = [
            'alumni' =>
                $alumni->id,
        ];


        if (
            $fromBatch !== null
        ) {
            $parameters['from_batch'] =
                $fromBatch;
        }


        return $parameters;
    }
}