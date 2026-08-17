<?php

namespace App\Http\Controllers;

use App\Models\Alumni;
use App\Models\HasilPelacakan;

class DashboardController extends Controller
{
    public function index()
    {

        /*
        |--------------------------------------------------------------------------
        | TOTAL ALUMNI
        |--------------------------------------------------------------------------
        */

        $totalAlumni = Alumni::count();



        /*
        |--------------------------------------------------------------------------
        | COVERAGE PROJECT 4
        | alumni yang mempunyai hasil pelacakan
        |--------------------------------------------------------------------------
        */

        $coverage = Alumni::whereHas('hasilPelacakans')
            ->count();



        /*
        |--------------------------------------------------------------------------
        | STATUS PELACAKAN
        |--------------------------------------------------------------------------
        */

        $belumDilacak =
            Alumni::whereDoesntHave('hasilPelacakans')
            ->count();



        $perluVerifikasi =
            HasilPelacakan::where(
                'status_pelacakan',
                Alumni::STATUS_PERLU_VERIFIKASI
            )->count();



        $terverifikasi =
            HasilPelacakan::where(
                'status_pelacakan',
                Alumni::STATUS_TERIDENTIFIKASI
            )->count();



        /*
        |--------------------------------------------------------------------------
        | COMPLETENESS
        |--------------------------------------------------------------------------
        */

        $completenessEmpat =
            HasilPelacakan::whereNotNull('ringkasan_hasil')
                ->whereNotNull('link_bukti')
                ->whereNotNull('confidence_score')
                ->whereNotNull('kategori_kecocokan')
                ->distinct('alumni_id')
                ->count('alumni_id');



        /*
        |--------------------------------------------------------------------------
        | FIELD COVERAGE
        |--------------------------------------------------------------------------
        */

        $fieldCoverageResult = [

            'Judul Temuan' =>
                HasilPelacakan::whereNotNull('judul_temuan')->count(),

            'Sumber Temuan' =>
                HasilPelacakan::whereNotNull('sumber_temuan')->count(),

            'Link Bukti' =>
                HasilPelacakan::whereNotNull('link_bukti')->count(),

            'Query Pencarian' =>
                HasilPelacakan::whereNotNull('query_pencarian')->count(),

            'Ringkasan Hasil' =>
                HasilPelacakan::whereNotNull('ringkasan_hasil')->count(),

            'Confidence Score' =>
                HasilPelacakan::whereNotNull('confidence_score')->count(),

        ];



        /*
        |--------------------------------------------------------------------------
        | PERSENTASE
        |--------------------------------------------------------------------------
        */


        $belumPunyaDataProject4 =
            max(
                0,
                $totalAlumni - $coverage
            );


        $coveragePersen =
            $totalAlumni > 0
            ?
            round(
                ($coverage/$totalAlumni)*100,
                2
            )
            :
            0;



        $completenessPersen =
            $coverage > 0
            ?
            round(
                ($completenessEmpat/$coverage)*100,
                2
            )
            :
            0;



        /*
        |--------------------------------------------------------------------------
        | TARGET
        |--------------------------------------------------------------------------
        */


        $targetCoverageRubrik = 106721;

        $targetCoverageAman = 115000;



        $sisaTargetRubrik =
            max(
                0,
                $targetCoverageRubrik-$coverage
            );


        $sisaTargetAman =
            max(
                0,
                $targetCoverageAman-$coverage
            );



        $progressTargetAman =
            round(
                ($coverage/$targetCoverageAman)*100,
                2
            );



        /*
        |--------------------------------------------------------------------------
        | ALUMNI TERBARU
        |--------------------------------------------------------------------------
        */

        $alumniTerbaru =
            Alumni::latest()
            ->take(5)
            ->get();



        return view(
            'dashboard',
            compact(
                'totalAlumni',
                'coverage',
                'coveragePersen',
                'completenessEmpat',
                'completenessPersen',
                'belumPunyaDataProject4',
                'belumDilacak',
                'perluVerifikasi',
                'terverifikasi',
                'fieldCoverageResult',
                'targetCoverageRubrik',
                'targetCoverageAman',
                'sisaTargetRubrik',
                'sisaTargetAman',
                'progressTargetAman',
                'alumniTerbaru'
            )
        );

    }
}
