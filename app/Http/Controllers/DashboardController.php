<?php

namespace App\Http\Controllers;

use App\Models\AccuracyAuditSample;
use App\Models\Alumni;

class DashboardController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | DATASET & HASIL AUDIT SUMBER
        |--------------------------------------------------------------------------
        |
        | Hasil audit file sumber dosen:
        |
        | - Total baris data sumber        : 142.292
        | - Alumni / NIM unik              : 142.122
        | - Baris duplikat berlebih        : 170
        | - Kelompok NIM duplikat          : 169
        | - Kelompok konflik atribut        : 125
        | - NIM unik tidak ada di database : 0
        |
        | Konflik sumber terutama:
        | - Program Studi berbeda : 99 kelompok
        | - Tahun/Tanggal Lulus   : 24 kelompok
        |
        | Tidak ditemukan konflik nama.
        |
        */

        $totalAlumni = Alumni::count();

        /*
         * Angka yang digunakan sebagai denominator Coverage
         * sesuai data sumber/rubrik dosen.
         */
        $totalDatasetRubrik = 142292;

        /*
         * Jumlah NIM unik berdasarkan audit CSV.
         */
        $totalAlumniUnikSumber = 142122;

        /*
         * 142.292 baris - 142.122 NIM unik.
         */
        $totalDuplikatSumber = 170;

        /*
         * Jumlah kelompok NIM yang muncul lebih dari satu kali.
         */
        $totalKelompokDuplikat = 169;

        /*
         * Dari 169 kelompok duplikat,
         * 125 mempunyai perbedaan atribut pada file sumber.
         */
        $totalKonflikSumber = 125;

        /*
         * Hasil audit file sumber dibanding database.
         */
        $totalMissingNimUnik = max(
            0,
            $totalAlumniUnikSumber - $totalAlumni
        );

        /*
         * Seluruh alumni unik dianggap sudah tersedia apabila
         * database telah mencakup minimal seluruh NIM unik sumber.
         */
        $datasetUnikLengkap =
            $totalMissingNimUnik === 0
            && $totalAlumni >= $totalAlumniUnikSumber;


        /*
        |--------------------------------------------------------------------------
        | 8 KATEGORI PROJECT 4
        |--------------------------------------------------------------------------
        |
        | 1. Sosial Media Alumni
        |    LinkedIn / Instagram / Facebook / TikTok
        |
        | 2. Email
        | 3. No HP
        | 4. Tempat Bekerja
        | 5. Alamat Bekerja
        | 6. Posisi / Jabatan
        | 7. PNS / Swasta / Wirausaha
        | 8. Sosial Media Tempat Bekerja
        |
        */

        $sosialMediaExpression = "
            CASE
                WHEN
                    NULLIF(TRIM(COALESCE(linkedin, '')), '') IS NOT NULL
                    OR NULLIF(TRIM(COALESCE(instagram, '')), '') IS NOT NULL
                    OR NULLIF(TRIM(COALESCE(facebook, '')), '') IS NOT NULL
                    OR NULLIF(TRIM(COALESCE(tiktok, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";

        $emailExpression = "
            CASE
                WHEN NULLIF(TRIM(COALESCE(email, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";

        $noHpExpression = "
            CASE
                WHEN NULLIF(TRIM(COALESCE(no_hp, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";

        $tempatBekerjaExpression = "
            CASE
                WHEN NULLIF(TRIM(COALESCE(tempat_bekerja, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";

        $alamatBekerjaExpression = "
            CASE
                WHEN NULLIF(TRIM(COALESCE(alamat_bekerja, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";

        $posisiExpression = "
            CASE
                WHEN NULLIF(TRIM(COALESCE(posisi, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";

        $kategoriPekerjaanExpression = "
            CASE
                WHEN NULLIF(TRIM(COALESCE(kategori_pekerjaan, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";

        $sosmedTempatBekerjaExpression = "
            CASE
                WHEN NULLIF(TRIM(COALESCE(sosmed_tempat_bekerja, '')), '') IS NOT NULL
                THEN 1
                ELSE 0
            END
        ";


        /*
        |--------------------------------------------------------------------------
        | JUMLAH KATEGORI TERISI PER ALUMNI
        |--------------------------------------------------------------------------
        */

        $jumlahKategoriExpression = "
            (
                {$sosialMediaExpression}
                +
                {$emailExpression}
                +
                {$noHpExpression}
                +
                {$tempatBekerjaExpression}
                +
                {$alamatBekerjaExpression}
                +
                {$posisiExpression}
                +
                {$kategoriPekerjaanExpression}
                +
                {$sosmedTempatBekerjaExpression}
            )
        ";


        /*
        |--------------------------------------------------------------------------
        | COVERAGE + COMPLETENESS
        |--------------------------------------------------------------------------
        */

        $metrics = Alumni::query()

            /*
             * Coverage:
             * minimal satu kategori Project 4 ditemukan.
             */
            ->selectRaw("
                SUM(
                    CASE
                        WHEN {$jumlahKategoriExpression} >= 1
                        THEN 1
                        ELSE 0
                    END
                ) AS coverage
            ")

            /*
             * Completeness < 2 kategori.
             */
            ->selectRaw("
                SUM(
                    CASE
                        WHEN {$jumlahKategoriExpression} < 2
                        THEN 1
                        ELSE 0
                    END
                ) AS completeness_kurang_dua
            ")

            /*
             * Completeness tepat 2 kategori.
             */
            ->selectRaw("
                SUM(
                    CASE
                        WHEN {$jumlahKategoriExpression} = 2
                        THEN 1
                        ELSE 0
                    END
                ) AS completeness_dua
            ")

            /*
             * Completeness tepat 3 kategori.
             */
            ->selectRaw("
                SUM(
                    CASE
                        WHEN {$jumlahKategoriExpression} = 3
                        THEN 1
                        ELSE 0
                    END
                ) AS completeness_tiga
            ")

            /*
             * Completeness minimal 4 kategori.
             */
            ->selectRaw("
                SUM(
                    CASE
                        WHEN {$jumlahKategoriExpression} >= 4
                        THEN 1
                        ELSE 0
                    END
                ) AS completeness_empat
            ")

            /*
             * Coverage kategori 1.
             */
            ->selectRaw("
                SUM({$sosialMediaExpression})
                AS coverage_sosial_media
            ")

            /*
             * Coverage kategori 2.
             */
            ->selectRaw("
                SUM({$emailExpression})
                AS coverage_email
            ")

            /*
             * Coverage kategori 3.
             */
            ->selectRaw("
                SUM({$noHpExpression})
                AS coverage_no_hp
            ")

            /*
             * Coverage kategori 4.
             */
            ->selectRaw("
                SUM({$tempatBekerjaExpression})
                AS coverage_tempat_bekerja
            ")

            /*
             * Coverage kategori 5.
             */
            ->selectRaw("
                SUM({$alamatBekerjaExpression})
                AS coverage_alamat_bekerja
            ")

            /*
             * Coverage kategori 6.
             */
            ->selectRaw("
                SUM({$posisiExpression})
                AS coverage_posisi
            ")

            /*
             * Coverage kategori 7.
             */
            ->selectRaw("
                SUM({$kategoriPekerjaanExpression})
                AS coverage_kategori_pekerjaan
            ")

            /*
             * Coverage kategori 8.
             */
            ->selectRaw("
                SUM({$sosmedTempatBekerjaExpression})
                AS coverage_sosmed_tempat_bekerja
            ")

            ->first();


        /*
        |--------------------------------------------------------------------------
        | HASIL METRIK
        |--------------------------------------------------------------------------
        */

        $coverage = (int) (
            $metrics->coverage ?? 0
        );

        $completenessKurangDua = (int) (
            $metrics->completeness_kurang_dua ?? 0
        );

        $completenessDua = (int) (
            $metrics->completeness_dua ?? 0
        );

        $completenessTiga = (int) (
            $metrics->completeness_tiga ?? 0
        );

        $completenessEmpat = (int) (
            $metrics->completeness_empat ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | COVERAGE PER 8 KATEGORI
        |--------------------------------------------------------------------------
        */

        $fieldCoverageResult = [
            '1. Sosial Media (LinkedIn / IG / Facebook / TikTok)' =>
                (int) (
                    $metrics->coverage_sosial_media ?? 0
                ),

            '2. Email' =>
                (int) (
                    $metrics->coverage_email ?? 0
                ),

            '3. No HP' =>
                (int) (
                    $metrics->coverage_no_hp ?? 0
                ),

            '4. Tempat Bekerja' =>
                (int) (
                    $metrics->coverage_tempat_bekerja ?? 0
                ),

            '5. Alamat Bekerja' =>
                (int) (
                    $metrics->coverage_alamat_bekerja ?? 0
                ),

            '6. Posisi / Jabatan' =>
                (int) (
                    $metrics->coverage_posisi ?? 0
                ),

            '7. PNS / Swasta / Wirausaha' =>
                (int) (
                    $metrics->coverage_kategori_pekerjaan ?? 0
                ),

            '8. Sosial Media Tempat Bekerja' =>
                (int) (
                    $metrics->coverage_sosmed_tempat_bekerja ?? 0
                ),
        ];


        /*
        |--------------------------------------------------------------------------
        | PERSENTASE COVERAGE
        |--------------------------------------------------------------------------
        */

        $belumPunyaDataProject4 = max(
            0,
            $totalAlumni - $coverage
        );


        /*
         * Denominator Coverage tetap menggunakan 142.292
         * sesuai jumlah data pada rubrik dosen.
         */
        $coveragePersen = $totalDatasetRubrik > 0
            ? round(
                (
                    $coverage
                    / $totalDatasetRubrik
                ) * 100,
                2
            )
            : 0;


        /*
         * Persentase alumni dengan Coverage yang sudah
         * mencapai minimal 4 kategori.
         */
        $completenessPersen = $coverage > 0
            ? round(
                (
                    $completenessEmpat
                    / $coverage
                ) * 100,
                2
            )
            : 0;


        /*
        |--------------------------------------------------------------------------
        | STATUS PELACAKAN ALUMNI
        |--------------------------------------------------------------------------
        */

        $belumDilacak = Alumni::query()
            ->where(function ($query) {
                $query
                    ->whereNull('status_verifikasi')
                    ->orWhere(
                        'status_verifikasi',
                        Alumni::STATUS_BELUM_DILACAK
                    )
                    ->orWhere(
                        'status_verifikasi',
                        ''
                    );
            })
            ->count();


        $perluVerifikasi = Alumni::where(
            'status_verifikasi',
            Alumni::STATUS_PERLU_VERIFIKASI
        )->count();


        $terverifikasi = Alumni::where(
            'status_verifikasi',
            Alumni::STATUS_TERIDENTIFIKASI
        )->count();


        /*
        |--------------------------------------------------------------------------
        | ACCURACY SAMPLING 500
        |--------------------------------------------------------------------------
        */

        $accuracyTargetSample = 500;

        $accuracyTotalSample =
            AccuracyAuditSample::count();


        $accuracyBenar =
            AccuracyAuditSample::where(
                'status_audit',
                AccuracyAuditSample::STATUS_BENAR
            )->count();


        $accuracySalah =
            AccuracyAuditSample::where(
                'status_audit',
                AccuracyAuditSample::STATUS_SALAH
            )->count();


        $accuracyPerluVerifikasi =
            AccuracyAuditSample::where(
                'status_audit',
                AccuracyAuditSample::STATUS_PERLU_VERIFIKASI
            )->count();


        $accuracyBelumDiaudit =
            AccuracyAuditSample::where(
                'status_audit',
                AccuracyAuditSample::STATUS_BELUM
            )->count();


        $accuracyTotalDiaudit =
            $accuracyBenar
            + $accuracySalah
            + $accuracyPerluVerifikasi;


        $accuracyTotalFinal =
            $accuracyBenar
            + $accuracySalah;


        /*
         * Accuracy sementara:
         *
         * Benar / (Benar + Salah) × 100%
         */
        $accuracySementara =
            $accuracyTotalFinal > 0
                ? round(
                    (
                        $accuracyBenar
                        / $accuracyTotalFinal
                    ) * 100,
                    2
                )
                : 0;


        /*
         * Sampling lengkap jika sudah ada 500 sampel.
         */
        $accuracySamplingLengkap =
            $accuracyTotalSample
            >= $accuracyTargetSample;


        /*
         * Audit lengkap jika:
         *
         * - 500 sampel tersedia
         * - tidak ada Belum Diaudit
         * - tidak ada Perlu Verifikasi
         * - seluruh 500 memiliki keputusan Benar/Salah
         */
        $accuracyAuditLengkap =
            $accuracySamplingLengkap
            && $accuracyBelumDiaudit === 0
            && $accuracyPerluVerifikasi === 0
            && $accuracyTotalFinal
                >= $accuracyTargetSample;


        /*
         * Rentang Accuracy berdasarkan rubrik dosen.
         */
        if ($accuracyAuditLengkap) {
            $accuracyRentangNilai = match (true) {
                $accuracyBenar > 475 =>
                    '91 - 100',

                $accuracyBenar >= 426 =>
                    '76 - 90',

                $accuracyBenar >= 350 =>
                    '51 - 75',

                default =>
                    '0 - 50',
            };
        } else {
            $accuracyRentangNilai =
                'Belum dapat ditetapkan';
        }


        $accuracyKekuranganSample = max(
            0,
            $accuracyTargetSample
            - $accuracyTotalSample
        );


        /*
        |--------------------------------------------------------------------------
        | TARGET COVERAGE
        |--------------------------------------------------------------------------
        */

        /*
         * Rubrik tertinggi:
         * > 106.720 data ditemukan.
         */
        $targetCoverageRubrik = 106721;

        /*
         * Target internal sebagai buffer.
         */
        $targetCoverageAman = 115000;


        $sisaTargetRubrik = max(
            0,
            $targetCoverageRubrik - $coverage
        );


        $sisaTargetAman = max(
            0,
            $targetCoverageAman - $coverage
        );


        $progressTargetAman =
            $targetCoverageAman > 0
                ? min(
                    100,
                    round(
                        (
                            $coverage
                            / $targetCoverageAman
                        ) * 100,
                        2
                    )
                )
                : 0;


        /*
        |--------------------------------------------------------------------------
        | RENTANG COVERAGE RUBRIK
        |--------------------------------------------------------------------------
        */

        $coverageRentangNilai = match (true) {
            $coverage > 106720 =>
                '91 - 100',

            $coverage >= 85377 =>
                '81 - 90',

            $coverage >= 56918 =>
                '61 - 80',

            $coverage >= 28459 =>
                '41 - 60',

            default =>
                '0 - 40',
        };


        /*
        |--------------------------------------------------------------------------
        | COMPLETENESS RUBRIK
        |--------------------------------------------------------------------------
        */

        $completenessRentangNilai = [
            '< 2 Field' =>
                '0 - 50',

            '2 Field' =>
                '51 - 70',

            '3 Field' =>
                '71 - 85',

            '≥ 4 Field' =>
                '86 - 100',
        ];


        /*
        |--------------------------------------------------------------------------
        | ALUMNI TERBARU
        |--------------------------------------------------------------------------
        */

        $alumniTerbaru = Alumni::latest()
            ->take(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN VIEW
        |--------------------------------------------------------------------------
        */

        return view(
            'dashboard',
            compact(
                /*
                 * Dataset.
                 */
                'totalAlumni',
                'totalDatasetRubrik',
                'totalAlumniUnikSumber',
                'totalDuplikatSumber',
                'totalKelompokDuplikat',
                'totalKonflikSumber',
                'totalMissingNimUnik',
                'datasetUnikLengkap',

                /*
                 * Coverage.
                 */
                'coverage',
                'coveragePersen',
                'coverageRentangNilai',

                /*
                 * Completeness.
                 */
                'completenessKurangDua',
                'completenessDua',
                'completenessTiga',
                'completenessEmpat',
                'completenessPersen',
                'completenessRentangNilai',

                'belumPunyaDataProject4',

                /*
                 * Coverage 8 kategori.
                 */
                'fieldCoverageResult',

                /*
                 * Status pelacakan.
                 */
                'belumDilacak',
                'perluVerifikasi',
                'terverifikasi',

                /*
                 * Accuracy Sampling 500.
                 */
                'accuracyTargetSample',
                'accuracyTotalSample',
                'accuracyKekuranganSample',
                'accuracyTotalDiaudit',
                'accuracyTotalFinal',
                'accuracyBenar',
                'accuracySalah',
                'accuracyPerluVerifikasi',
                'accuracyBelumDiaudit',
                'accuracySementara',
                'accuracyRentangNilai',
                'accuracySamplingLengkap',
                'accuracyAuditLengkap',

                /*
                 * Target Coverage.
                 */
                'targetCoverageRubrik',
                'targetCoverageAman',
                'sisaTargetRubrik',
                'sisaTargetAman',
                'progressTargetAman',

                /*
                 * Alumni terbaru.
                 */
                'alumniTerbaru'
            )
        );
    }
}
