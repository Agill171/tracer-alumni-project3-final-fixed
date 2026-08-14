<?php

namespace App\Http\Controllers;

use App\Models\Alumni;

class DashboardController extends Controller
{
    public function index()
    {
        $project4Fields = [
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

        $coverageCondition = implode(
            ' OR ',
            array_map(
                fn ($field) => "({$field} IS NOT NULL AND TRIM({$field}) <> '')",
                $project4Fields
            )
        );

        $completenessExpression = implode(
            ' + ',
            array_map(
                fn ($field) => "
                    CASE
                        WHEN {$field} IS NOT NULL
                            AND TRIM({$field}) <> ''
                        THEN 1
                        ELSE 0
                    END
                ",
                $project4Fields
            )
        );

        $metrics = Alumni::query()
            ->selectRaw('COUNT(*) AS total_alumni')
            ->selectRaw("
                SUM(
                    CASE
                        WHEN {$coverageCondition}
                        THEN 1
                        ELSE 0
                    END
                ) AS coverage
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN ({$completenessExpression}) >= 4
                        THEN 1
                        ELSE 0
                    END
                ) AS completeness_empat
            ")
            ->selectRaw("
                SUM(
                    CASE
                        WHEN status_verifikasi = ?
                            OR status_verifikasi IS NULL
                        THEN 1
                        ELSE 0
                    END
                ) AS belum_dilacak
            ", [
                Alumni::STATUS_BELUM_DILACAK,
            ])
            ->selectRaw("
                SUM(
                    CASE
                        WHEN status_verifikasi = ?
                        THEN 1
                        ELSE 0
                    END
                ) AS perlu_verifikasi
            ", [
                Alumni::STATUS_PERLU_VERIFIKASI,
            ])
            ->selectRaw("
                SUM(
                    CASE
                        WHEN status_verifikasi = ?
                        THEN 1
                        ELSE 0
                    END
                ) AS terverifikasi
            ", [
                Alumni::STATUS_TERIDENTIFIKASI,
            ])
            ->first();

        $totalAlumni = (int) ($metrics->total_alumni ?? 0);
        $coverage = (int) ($metrics->coverage ?? 0);
        $completenessEmpat = (int) ($metrics->completeness_empat ?? 0);

        $belumDilacak = (int) ($metrics->belum_dilacak ?? 0);
        $perluVerifikasi = (int) ($metrics->perlu_verifikasi ?? 0);
        $terverifikasi = (int) ($metrics->terverifikasi ?? 0);

        $belumPunyaDataProject4 = max(
            0,
            $totalAlumni - $coverage
        );

        $coveragePersen = $totalAlumni > 0
            ? round(($coverage / $totalAlumni) * 100, 2)
            : 0;

        $completenessPersen = $coverage > 0
            ? round(($completenessEmpat / $coverage) * 100, 2)
            : 0;

        $targetCoverageRubrik = 106721;
        $targetCoverageAman = 115000;

        $sisaTargetRubrik = max(
            0,
            $targetCoverageRubrik - $coverage
        );

        $sisaTargetAman = max(
            0,
            $targetCoverageAman - $coverage
        );

        $progressTargetAman = $targetCoverageAman > 0
            ? min(
                100,
                round(
                    ($coverage / $targetCoverageAman) * 100,
                    2
                )
            )
            : 0;

        $alumniTerbaru = Alumni::latest()
            ->take(5)
            ->get();

        return view('dashboard', compact(
            'totalAlumni',
            'belumDilacak',
            'perluVerifikasi',
            'terverifikasi',
            'alumniTerbaru',
            'coverage',
            'coveragePersen',
            'completenessEmpat',
            'completenessPersen',
            'belumPunyaDataProject4',
            'targetCoverageRubrik',
            'targetCoverageAman',
            'sisaTargetRubrik',
            'sisaTargetAman',
            'progressTargetAman'
        ));
    }
}
