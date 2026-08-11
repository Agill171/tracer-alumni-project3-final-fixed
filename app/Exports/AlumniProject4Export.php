<?php

namespace App\Exports;

use App\Models\Alumni;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AlumniProject4Export implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query()
    {
        return Alumni::query()
            ->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'Nama Lengkap',
            'NIM',
            'Program Studi',
            'Angkatan',
            'Tahun Lulus',
            'LinkedIn',
            'Instagram',
            'Facebook',
            'TikTok',
            'Email',
            'Nomor HP',
            'Tempat Bekerja',
            'Alamat Bekerja',
            'Posisi/Jabatan',
            'Kategori Pekerjaan',
            'Sosial Media Tempat Bekerja',
            'Status Verifikasi',
            'Catatan',
        ];
    }

    public function map($alumni): array
    {
        return [
            $alumni->nama,
            $alumni->nim,
            $alumni->prodi,
            $alumni->angkatan,
            $alumni->tahun_lulus,
            $alumni->linkedin,
            $alumni->instagram,
            $alumni->facebook,
            $alumni->tiktok,
            $alumni->email,
            $alumni->no_hp,
            $alumni->tempat_bekerja,
            $alumni->alamat_bekerja,
            $alumni->posisi,
            $alumni->kategori_pekerjaan,
            $alumni->sosmed_tempat_bekerja,
            $alumni->status_verifikasi,
            $alumni->catatan,
        ];
    }
}