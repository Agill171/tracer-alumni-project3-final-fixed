<?php

namespace App\Jobs;

use App\Models\Alumni;
use App\Models\HasilPelacakan;
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

    public $timeout = 900;

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

                /*
                |--------------------------------------------------------------------------
                | HEADER EXCEL
                |--------------------------------------------------------------------------
                */

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
                ]));


                /*
                |--------------------------------------------------------------------------
                | DATA ALUMNI
                |--------------------------------------------------------------------------
                |
                | Diproses per 1.000 alumni agar tidak memakan RAM terlalu besar.
                |
                */

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
                    ->orderBy('id')
                    ->chunkById(
                        1000,
                        function ($alumnis) use ($writer) {

                            /*
                            |--------------------------------------------------------------------------
                            | AMBIL HASIL PELACAKAN
                            |--------------------------------------------------------------------------
                            |
                            | Hanya query hasil pelacakan untuk alumni pada chunk ini.
                            |
                            */

                            $alumniIds = $alumnis
                                ->pluck('id')
                                ->all();


                            $hasilPelacakans = HasilPelacakan::query()
                                ->with([
                                    'user:id,name',
                                    'auditor:id,name',
                                ])
                                ->whereIn(
                                    'alumni_id',
                                    $alumniIds
                                )
                                ->orderBy('alumni_id')
                                ->orderByDesc('tanggal_ditemukan')
                                ->orderByDesc('id')
                                ->get()
                                ->groupBy('alumni_id');


                            /*
                            |--------------------------------------------------------------------------
                            | TULIS KE EXCEL
                            |--------------------------------------------------------------------------
                            */

                            foreach ($alumnis as $alumni) {

                                /*
                                 * Ambil hasil pelacakan terbaru.
                                 */
                                $pelacakan = $hasilPelacakans
                                    ->get($alumni->id)
                                    ?->first();


                                $writer->addRow(Row::fromValues([

                                    /*
                                     * DATA ALUMNI
                                     */
                                    $alumni->nama ?? '',
                                    $alumni->nim ?? '',
                                    $alumni->prodi ?? '',
                                    $alumni->angkatan ?? '',
                                    $alumni->tahun_lulus ?? '',


                                    /*
                                     * SOSIAL MEDIA
                                     */
                                    $alumni->linkedin ?? '',
                                    $alumni->instagram ?? '',
                                    $alumni->facebook ?? '',
                                    $alumni->tiktok ?? '',
                                    $alumni->email ?? '',
                                    $alumni->no_hp ?? '',


                                    /*
                                     * PEKERJAAN
                                     */
                                    $alumni->tempat_bekerja ?? '',
                                    $alumni->alamat_bekerja ?? '',
                                    $alumni->posisi ?? '',
                                    $alumni->kategori_pekerjaan ?? '',
                                    $alumni->sosmed_tempat_bekerja ?? '',


                                    /*
                                     * STATUS ALUMNI
                                     */
                                    $alumni->status_verifikasi ?? '',
                                    $alumni->catatan ?? '',


                                    /*
                                     * HASIL PELACAKAN TERBARU
                                     */
                                    $pelacakan?->judul_temuan ?? '',
                                    $pelacakan?->sumber_temuan ?? '',
                                    $pelacakan?->link_bukti ?? '',
                                    $pelacakan?->query_pencarian ?? '',
                                    $pelacakan?->ringkasan_hasil ?? '',

                                    $pelacakan?->tanggal_ditemukan
                                        ?->format('Y-m-d') ?? '',


                                    /*
                                     * CONFIDENCE
                                     */
                                    $pelacakan?->confidence_score ?? '',
                                    $pelacakan?->kategori_kecocokan ?? '',


                                    /*
                                     * ACCURACY AUDIT
                                     */
                                    $pelacakan?->status_audit ?? '',
                                    $pelacakan?->catatan_audit ?? '',

                                    $pelacakan?->auditor?->name ?? '',

                                    $pelacakan?->audited_at
                                        ?->format('Y-m-d H:i:s') ?? '',


                                    /*
                                     * USER PENCATAT
                                     */
                                    $pelacakan?->user?->name ?? '',
                                ]));
                            }
                        }
                    );

            } finally {

                /*
                 * Writer harus selalu ditutup.
                 */
                $writer->close();
            }


            /*
            |--------------------------------------------------------------------------
            | UPLOAD KE S3
            |--------------------------------------------------------------------------
            */

            $stream = fopen(
                $tempPath,
                'rb'
            );


            if ($stream === false) {
                throw new RuntimeException(
                    'File sementara hasil export tidak dapat dibuka.'
                );
            }


            try {

                $stored = Storage::disk('s3')
                    ->put(
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

            /*
            |--------------------------------------------------------------------------
            | HAPUS FILE TEMPORARY
            |--------------------------------------------------------------------------
            */

            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }
}
