<?php

namespace App\Exports;

use App\Models\Alumni;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AlumniProject4Export implements
    FromQuery,
    WithHeadings,
    WithMapping,
    ShouldQueue
{
    use Exportable;

    public function query()
    {
        return Alumni::query()
            ->with([
                'hasilPelacakans' => function ($query) {
                    $query
                        ->with([
                            'user',
                            'auditor',
                        ])
                        ->orderByDesc('tanggal_ditemukan')
                        ->orderByDesc('id');
                },
            ])
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
            'Catatan Alumni',

            'Judul Temuan Terbaru',
            'Sumber Temuan',
            'Link Bukti',
            'Query Pencarian',
            'Ringkasan Hasil',
            'Tanggal Ditemukan',

            'Confidence Score',
            'Kategori Kecocokan',

            'Status Audit',
            'Catatan Audit',
            'Auditor',
            'Tanggal Audit',

            'Dicatat Oleh',
        ];
    }

    public function map($alumni): array
    {
        $pelacakan = $alumni->hasilPelacakans->first();

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

            $pelacakan?->judul_temuan,
            $pelacakan?->sumber_temuan,
            $pelacakan?->link_bukti,
            $pelacakan?->query_pencarian,
            $pelacakan?->ringkasan_hasil,
            $pelacakan?->tanggal_ditemukan?->format('Y-m-d'),

            $pelacakan?->confidence_score,
            $pelacakan?->kategori_kecocokan,

            $pelacakan?->status_audit,
            $pelacakan?->catatan_audit,
            $pelacakan?->auditor?->name,
            $pelacakan?->audited_at?->format('Y-m-d H:i:s'),

            $pelacakan?->user?->name,
        ];
    }
}