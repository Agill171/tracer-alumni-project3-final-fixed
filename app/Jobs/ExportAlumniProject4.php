<?php

namespace App\Jobs;

use App\Models\Alumni;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use RuntimeException;

class ExportAlumniProject4 implements ShouldQueue
{
    use Queueable;

    public $tries = 1;

    public $timeout = 500;

    public $failOnTimeout = true;

    public function __construct()
    {
        //
    }

    public function handle(): void
    {
        $filename = 'hasil-pelacakan-alumni-project4.xlsx';

        $tempDirectory = storage_path('app/export-temp');

        if (! is_dir($tempDirectory)) {
            mkdir($tempDirectory, 0755, true);
        }

        $tempPath = $tempDirectory
            . DIRECTORY_SEPARATOR
            . 'alumni-project4-'
            . uniqid()
            . '.xlsx';

        $writer = new Writer();

        try {
            $writer->openToFile($tempPath);

            try {
                $writer->addRow(Row::fromValues([
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
                ]));

                Alumni::query()
                    ->select([
                        'id',
                        'nama',
                        'nim',
                        'prodi',
                        'angkatan',
                        'tahun_lulus',
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
                        'status_verifikasi',
                        'catatan',
                    ])
                    ->lazyById(1000)
                    ->each(function (Alumni $alumni) use ($writer) {
                        $writer->addRow(Row::fromValues([
                            $alumni->nama ?? '',
                            $alumni->nim ?? '',
                            $alumni->prodi ?? '',
                            $alumni->angkatan ?? '',
                            $alumni->tahun_lulus ?? '',
                            $alumni->linkedin ?? '',
                            $alumni->instagram ?? '',
                            $alumni->facebook ?? '',
                            $alumni->tiktok ?? '',
                            $alumni->email ?? '',
                            $alumni->no_hp ?? '',
                            $alumni->tempat_bekerja ?? '',
                            $alumni->alamat_bekerja ?? '',
                            $alumni->posisi ?? '',
                            $alumni->kategori_pekerjaan ?? '',
                            $alumni->sosmed_tempat_bekerja ?? '',
                            $alumni->status_verifikasi ?? '',
                            $alumni->catatan ?? '',
                        ]));
                    });
            } finally {
                $writer->close();
            }

            $stream = fopen($tempPath, 'rb');

            if ($stream === false) {
                throw new RuntimeException(
                    'File sementara hasil export tidak dapat dibuka.'
                );
            }

            try {
                $stored = Storage::disk('s3')->put(
                    $filename,
                    $stream
                );

                if (! $stored) {
                    throw new RuntimeException(
                        'Hasil export gagal disimpan ke S3.'
                    );
                }
            } finally {
                fclose($stream);
            }
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
