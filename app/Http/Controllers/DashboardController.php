<?php

namespace App\Http\Controllers;

use App\Models\Alumni;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAlumni = Alumni::count();

        $belumDilacak = Alumni::where(function ($query) {
            $query->where('status_verifikasi', Alumni::STATUS_BELUM_DILACAK)
                ->orWhereNull('status_verifikasi');
        })->count();

        $perluVerifikasi = Alumni::where('status_verifikasi', Alumni::STATUS_PERLU_VERIFIKASI)->count();

        $terverifikasi = Alumni::where('status_verifikasi', Alumni::STATUS_TERIDENTIFIKASI)->count();

        $alumniTerbaru = Alumni::latest()->take(5)->get();

        return view('dashboard', compact(
            'totalAlumni',
            'belumDilacak',
            'perluVerifikasi',
            'terverifikasi',
            'alumniTerbaru'
        ));
    }
}
