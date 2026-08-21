<?php

namespace App\Http\Controllers;

use App\Models\AccuracyAuditSample;
use App\Models\Alumni;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccuracyAuditController extends Controller
{
    private const TARGET_SAMPLE = 500;

    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | LENGKAPI SAMPEL ACCURACY
        |--------------------------------------------------------------------------
        */

        $this->ensureSamples();

        /*
        |--------------------------------------------------------------------------
        | TOTAL ELIGIBLE DAN TARGET
        |--------------------------------------------------------------------------
        */

        $totalEligible = $this->eligibleAlumniQuery()->count();

        $targetSample = self::TARGET_SAMPLE;

        $totalSample = AccuracyAuditSample::count();

        /*
        |--------------------------------------------------------------------------
        | STATISTIK AUDIT
        |--------------------------------------------------------------------------
        */

        $benar = AccuracyAuditSample::where(
            'status_audit',
            AccuracyAuditSample::STATUS_BENAR
        )->count();

        $salah = AccuracyAuditSample::where(
            'status_audit',
            AccuracyAuditSample::STATUS_SALAH
        )->count();

        $perluVerifikasi = AccuracyAuditSample::where(
            'status_audit',
            AccuracyAuditSample::STATUS_PERLU_VERIFIKASI
        )->count();

        $belumDiaudit = AccuracyAuditSample::where(
            'status_audit',
            AccuracyAuditSample::STATUS_BELUM
        )->count();

        $totalDiaudit = $benar
            + $salah
            + $perluVerifikasi;

        /*
        |--------------------------------------------------------------------------
        | KEPUTUSAN FINAL
        |--------------------------------------------------------------------------
        */

        $totalFinal = $benar + $salah;

        /*
        |--------------------------------------------------------------------------
        | ACCURACY SEMENTARA
        |--------------------------------------------------------------------------
        */

        $accuracySementara = $totalFinal > 0
            ? round(
                ($benar / $totalFinal) * 100,
                2
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | STATUS SAMPLING
        |--------------------------------------------------------------------------
        */

        $samplingLengkap = $totalSample >= $targetSample;

        $auditLengkap = $samplingLengkap
            && $belumDiaudit === 0
            && $perluVerifikasi === 0
            && $totalFinal >= $targetSample;

        /*
        |--------------------------------------------------------------------------
        | RENTANG NILAI ACCURACY
        |--------------------------------------------------------------------------
        */

        if ($auditLengkap) {
            $accuracyRentangNilai = match (true) {
                $benar > 475 => '91 - 100',
                $benar >= 426 => '76 - 90',
                $benar >= 350 => '51 - 75',
                default => '0 - 50',
            };
        } else {
            $accuracyRentangNilai = 'Belum dapat ditetapkan';
        }

        /*
        |--------------------------------------------------------------------------
        | KEKURANGAN SAMPEL
        |--------------------------------------------------------------------------
        */

        $kekuranganSample = max(
            0,
            $targetSample - $totalSample
        );

        /*
        |--------------------------------------------------------------------------
        | DAFTAR SAMPEL
        |--------------------------------------------------------------------------
        */

        $samples = AccuracyAuditSample::query()
            ->with([
                'auditor',

                'alumni' => function ($query) {
                    $query->with([
                        'hasilPelacakans' => function ($hasilQuery) {
                            $hasilQuery
                                ->with('user')
                                ->latest('tanggal_ditemukan')
                                ->latest('id');
                        },
                    ]);
                },
            ])
            ->orderByRaw(
                "
                CASE
                    WHEN status_audit = ? THEN 0
                    WHEN status_audit = ? THEN 1
                    ELSE 2
                END
                ",
                [
                    AccuracyAuditSample::STATUS_BELUM,
                    AccuracyAuditSample::STATUS_PERLU_VERIFIKASI,
                ]
            )
            ->orderBy('sample_order')
            ->paginate(25);

        return view('accuracy-audit.index', compact(
            'targetSample',
            'totalEligible',
            'totalSample',
            'kekuranganSample',

            'totalDiaudit',
            'totalFinal',

            'benar',
            'salah',
            'perluVerifikasi',
            'belumDiaudit',

            'accuracySementara',
            'accuracyRentangNilai',

            'samplingLengkap',
            'auditLengkap',

            'samples'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | SIMPAN AUDIT
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        AccuracyAuditSample $sample
    ) {
        $validated = $request->validate([
            'status_audit' => [
                'required',
                Rule::in(
                    AccuracyAuditSample::statusOptions()
                ),
            ],

            'catatan_audit' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ]);

        $sample->update([
            'status_audit' =>
                $validated['status_audit'],

            'catatan_audit' =>
                $validated['catatan_audit'] ?? null,

            'audited_by' =>
                $request->user()->id,

            'audited_at' =>
                now(),
        ]);

        return redirect()
            ->route('accuracy-audit.index')
            ->with(
                'success',
                'Audit sampel Accuracy Project 4 berhasil disimpan.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | RESET AUDIT
    |--------------------------------------------------------------------------
    */

    public function reset(
        AccuracyAuditSample $sample
    ) {
        $sample->update([
            'status_audit' =>
                AccuracyAuditSample::STATUS_BELUM,

            'catatan_audit' =>
                null,

            'audited_by' =>
                null,

            'audited_at' =>
                null,
        ]);

        return redirect()
            ->route('accuracy-audit.index')
            ->with(
                'success',
                'Audit sampel dikembalikan menjadi Belum Diaudit.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE / LENGKAPI SAMPEL
    |--------------------------------------------------------------------------
    */

    private function ensureSamples(): void
    {
        DB::transaction(function () {
            $existingSamples = AccuracyAuditSample::query()
                ->orderBy('sample_order')
                ->lockForUpdate()
                ->get([
                    'id',
                    'alumni_id',
                    'sample_order',
                ]);

            $existingCount = $existingSamples->count();

            if ($existingCount >= self::TARGET_SAMPLE) {
                return;
            }

            $needed = self::TARGET_SAMPLE - $existingCount;

            $existingAlumniIds = $existingSamples
                ->pluck('alumni_id');

            $candidateQuery = $this->eligibleAlumniQuery();

            if ($existingAlumniIds->isNotEmpty()) {
                $candidateQuery->whereNotIn(
                    'id',
                    $existingAlumniIds
                );
            }

            $selectedAlumniIds = $candidateQuery
                ->inRandomOrder()
                ->limit($needed)
                ->pluck('id');

            if ($selectedAlumniIds->isEmpty()) {
                return;
            }

            $nextOrder = (int) (
                $existingSamples->max('sample_order') ?? 0
            );

            foreach ($selectedAlumniIds as $alumniId) {
                $nextOrder++;

                AccuracyAuditSample::create([
                    'alumni_id' =>
                        $alumniId,

                    'sample_order' =>
                        $nextOrder,

                    'status_audit' =>
                        AccuracyAuditSample::STATUS_BELUM,
                ]);
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | ALUMNI ELIGIBLE UNTUK SAMPEL ACCURACY
    |--------------------------------------------------------------------------
    */

    private function eligibleAlumniQuery(): Builder
    {
        return Alumni::query()
            ->where(function ($query) {
                /*
                 * 1. Sosial Media Alumni
                 */
                $query
                    ->whereRaw(
                        "NULLIF(TRIM(COALESCE(linkedin, '')), '') IS NOT NULL"
                    )
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(instagram, '')), '') IS NOT NULL"
                    )
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(facebook, '')), '') IS NOT NULL"
                    )
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(tiktok, '')), '') IS NOT NULL"
                    )

                    /*
                     * 2. Email
                     */
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(email, '')), '') IS NOT NULL"
                    )

                    /*
                     * 3. No HP
                     */
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(no_hp, '')), '') IS NOT NULL"
                    )

                    /*
                     * 4. Tempat Bekerja
                     */
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(tempat_bekerja, '')), '') IS NOT NULL"
                    )

                    /*
                     * 5. Alamat Bekerja
                     */
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(alamat_bekerja, '')), '') IS NOT NULL"
                    )

                    /*
                     * 6. Posisi / Jabatan
                     */
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(posisi, '')), '') IS NOT NULL"
                    )

                    /*
                     * 7. Kategori Pekerjaan
                     */
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(kategori_pekerjaan, '')), '') IS NOT NULL"
                    )

                    /*
                     * 8. Sosial Media Tempat Bekerja
                     */
                    ->orWhereRaw(
                        "NULLIF(TRIM(COALESCE(sosmed_tempat_bekerja, '')), '') IS NOT NULL"
                    );
            });
    }
}