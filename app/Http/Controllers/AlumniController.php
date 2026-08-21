<?php

namespace App\Http\Controllers;

use App\Imports\AlumniImport;
use App\Jobs\ExportAlumniProject4;
use App\Models\Alumni;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class AlumniController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:100',
            ],

            'status' => [
                'nullable',
                Rule::in(
                    Alumni::statusOptions()
                ),
            ],
        ]);


        $alumnis = Alumni::query()

            ->when(
                $filters['q'] ?? null,
                function (
                    $query,
                    string $keyword
                ) {
                    $query->where(
                        function ($subQuery) use ($keyword) {
                            $subQuery
                                ->where(
                                    'nama',
                                    'like',
                                    "%{$keyword}%"
                                )
                                ->orWhere(
                                    'nim',
                                    'like',
                                    "%{$keyword}%"
                                )
                                ->orWhere(
                                    'prodi',
                                    'like',
                                    "%{$keyword}%"
                                )
                                ->orWhere(
                                    'tempat_bekerja',
                                    'like',
                                    "%{$keyword}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $filters['status'] ?? null,
                fn (
                    $query,
                    string $status
                ) =>
                    $query->where(
                        'status_verifikasi',
                        $status
                    )
            )

            ->latest()

            ->paginate(10)

            ->withQueryString();


        return view(
            'alumni.index',
            [
                'alumnis' =>
                    $alumnis,

                'statusOptions' =>
                    Alumni::statusOptions(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        return view(
            'alumni.create',
            [
                'kategoriOptions' =>
                    Alumni::kategoriPekerjaanOptions(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        Alumni::create(
            $this->validatedData(
                $request
            )
        );


        return redirect()
            ->route(
                'alumni.index'
            )
            ->with(
                'success',
                'Data alumni berhasil ditambahkan.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW
    |--------------------------------------------------------------------------
    */

    public function show(
        Alumni $alumni
    ) {
        $alumni->load([
            /*
             * Evidence / hasil pelacakan.
             */
            'hasilPelacakans' =>
                fn ($query) =>
                    $query
                        ->with('user')
                        ->latest(
                            'tanggal_ditemukan'
                        )
                        ->latest('id'),

            /*
             * Query pencarian.
             */
            'queryPelacakans' =>
                fn ($query) =>
                    $query
                        ->latest(
                            'generated_at'
                        )
                        ->orderBy(
                            'prioritas'
                        ),
        ]);


        return view(
            'alumni.show',
            compact(
                'alumni'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    |
    | from_batch dipertahankan agar:
    |
    | Detail Batch
    | → Detail Alumni
    | → Edit Project 4
    | → Simpan
    | → Detail Alumni
    | → Kembali ke Batch
    |
    */

    public function edit(
        Request $request,
        Alumni $alumni
    ) {
        return view(
            'alumni.edit',
            [
                'alumni' =>
                    $alumni,

                'kategoriOptions' =>
                    Alumni::kategoriPekerjaanOptions(),

                'fromBatch' =>
                    $this->fromBatchId(
                        $request
                    ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Alumni $alumni
    ) {
        $fromBatch =
            $this->fromBatchId(
                $request
            );


        $alumni->update(
            $this->validatedData(
                $request,
                $alumni
            )
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
                'Data alumni berhasil diperbarui.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DESTROY
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Alumni $alumni
    ) {
        $alumni->delete();


        return redirect()
            ->route(
                'alumni.index'
            )
            ->with(
                'success',
                'Data alumni berhasil dihapus.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | FORM IMPORT
    |--------------------------------------------------------------------------
    */

    public function importForm()
    {
        return view(
            'alumni.import'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | IMPORT EXCEL
    |--------------------------------------------------------------------------
    */

    public function importExcel(
        Request $request
    ) {
        $request->validate([
            'file_excel' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv',
                'max:51200',
            ],
        ]);


        $path =
            $request
                ->file(
                    'file_excel'
                )
                ->store(
                    'imports'
                );


        Excel::queueImport(
            new AlumniImport(),
            $path,
            'local'
        );


        return redirect()
            ->route(
                'alumni.index'
            )
            ->with(
                'success',
                'File berhasil diterima dan sedang diproses melalui antrean. '
                .'Jalankan queue worker untuk menyelesaikan import.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | EXPORT PROJECT 4
    |--------------------------------------------------------------------------
    */

    public function exportExcel()
    {
        ExportAlumniProject4::dispatch();


        return redirect()
            ->route(
                'alumni.index'
            )
            ->with(
                'success',
                'Export Excel sedang diproses melalui antrean. '
                .'Tunggu hingga proses selesai.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | DOWNLOAD EXPORT DARI S3
    |--------------------------------------------------------------------------
    */

    public function downloadExport()
    {
        $filename =
            'hasil-pelacakan-alumni-project4.xlsx';


        $disk =
            Storage::disk(
                's3'
            );


        /*
        |--------------------------------------------------------------------------
        | CEK FILE DI OBJECT STORAGE
        |--------------------------------------------------------------------------
        */

        if (
            ! $disk->exists(
                $filename
            )
        ) {
            return redirect()
                ->route(
                    'alumni.index'
                )
                ->withErrors([
                    'export' =>
                        'File hasil export belum tersedia. '
                        .'Jalankan Export Excel terlebih dahulu.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | DIREKTORI TEMPORARY LOCAL
        |--------------------------------------------------------------------------
        */

        $tempDirectory =
            storage_path(
                'app/download-temp'
            );


        if (
            ! is_dir(
                $tempDirectory
            )
        ) {
            mkdir(
                $tempDirectory,
                0755,
                true
            );
        }


        $tempPath =
            $tempDirectory
            .DIRECTORY_SEPARATOR
            .'hasil-pelacakan-alumni-project4-'
            .uniqid()
            .'.xlsx';


        /*
        |--------------------------------------------------------------------------
        | BACA STREAM DARI S3
        |--------------------------------------------------------------------------
        */

        $remoteStream =
            $disk->readStream(
                $filename
            );


        if (
            $remoteStream === false
        ) {
            return redirect()
                ->route(
                    'alumni.index'
                )
                ->withErrors([
                    'export' =>
                        'File hasil export tidak dapat dibaca dari storage.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | BUAT FILE TEMPORARY
        |--------------------------------------------------------------------------
        */

        $localStream =
            fopen(
                $tempPath,
                'wb'
            );


        if (
            $localStream === false
        ) {
            fclose(
                $remoteStream
            );


            return redirect()
                ->route(
                    'alumni.index'
                )
                ->withErrors([
                    'export' =>
                        'File sementara untuk proses download tidak dapat dibuat.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | COPY S3 → LOCAL
        |--------------------------------------------------------------------------
        */

        try {
            $copied =
                stream_copy_to_stream(
                    $remoteStream,
                    $localStream
                );


            if (
                $copied === false
            ) {
                throw new RuntimeException(
                    'File hasil export gagal disalin dari storage.'
                );
            }
        } finally {
            fclose(
                $remoteStream
            );

            fclose(
                $localStream
            );
        }


        /*
        |--------------------------------------------------------------------------
        | VALIDASI FILE TEMPORARY
        |--------------------------------------------------------------------------
        */

        if (
            ! file_exists(
                $tempPath
            )
            || filesize(
                $tempPath
            ) === 0
        ) {
            if (
                file_exists(
                    $tempPath
                )
            ) {
                @unlink(
                    $tempPath
                );
            }


            return redirect()
                ->route(
                    'alumni.index'
                )
                ->withErrors([
                    'export' =>
                        'File hasil export kosong atau gagal disiapkan untuk download.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | FORCE DOWNLOAD XLSX
        |--------------------------------------------------------------------------
        */

        return response()
            ->download(
                $tempPath,
                $filename,
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                    'Content-Disposition' =>
                        'attachment; filename="'
                        .$filename
                        .'"',

                    'X-Content-Type-Options' =>
                        'nosniff',

                    'Cache-Control' =>
                        'private, no-store, no-cache, must-revalidate',

                    'Pragma' =>
                        'no-cache',

                    'Expires' =>
                        '0',
                ]
            )
            ->deleteFileAfterSend(
                true
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDASI DATA ALUMNI
    |--------------------------------------------------------------------------
    |
    | Field Project 4:
    |
    | 1. Sosial Media
    |    - LinkedIn
    |    - Instagram
    |    - Facebook
    |    - TikTok
    |
    | 2. Email
    | 3. Nomor HP
    | 4. Tempat Bekerja
    | 5. Alamat Bekerja
    | 6. Posisi / Jabatan
    | 7. PNS / Swasta / Wirausaha
    | 8. Sosial Media / Situs Tempat Bekerja
    |
    */

    private function validatedData(
        Request $request,
        ?Alumni $alumni = null
    ): array {
        $currentYear =
            (int) now()->format(
                'Y'
            );


        return $request->validate([
            /*
             * Identitas dasar.
             */
            'nama' => [
                'required',
                'string',
                'max:255',
            ],


            'nim' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique(
                    'alumnis',
                    'nim'
                )->ignore(
                    $alumni?->id
                ),
            ],


            'prodi' => [
                'nullable',
                'string',
                'max:150',
            ],


            'angkatan' => [
                'nullable',
                'digits:4',
                'integer',
                'min:1900',
                'max:'.(
                    $currentYear + 1
                ),
            ],


            'tahun_lulus' => [
                'nullable',
                'integer',
                'min:1900',
                'max:'.(
                    $currentYear + 1
                ),
            ],


            /*
             * Project 4: email.
             */
            'email' => [
                'nullable',
                'email:rfc',
                'max:255',
            ],


            /*
             * Project 4: nomor HP.
             */
            'no_hp' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[0-9+()\-\s]+$/',
            ],


            /*
             * Project 4: tempat bekerja.
             */
            'tempat_bekerja' => [
                'nullable',
                'string',
                'max:255',
            ],


            /*
             * Project 4: alamat tempat bekerja.
             */
            'alamat_bekerja' => [
                'nullable',
                'string',
                'max:1000',
            ],


            /*
             * Project 4: posisi/jabatan.
             */
            'posisi' => [
                'nullable',
                'string',
                'max:255',
            ],


            /*
             * Project 4: kategori pekerjaan.
             */
            'kategori_pekerjaan' => [
                'nullable',

                Rule::in(
                    Alumni::kategoriPekerjaanOptions()
                ),
            ],


            /*
             * Project 4: sosial media alumni.
             */
            'linkedin' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],


            'instagram' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],


            'facebook' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],


            'tiktok' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],


            /*
             * Project 4:
             * sosial media / situs tempat bekerja.
             */
            'sosmed_tempat_bekerja' => [
                'nullable',
                'url:http,https',
                'max:2048',
            ],


            /*
             * Catatan internal.
             */
            'catatan' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | AMBIL ID BATCH DARI REQUEST
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
    | PARAMETER ROUTE DETAIL ALUMNI
    |--------------------------------------------------------------------------
    |
    | Kalau berasal dari batch:
    |
    | /alumni/123?from_batch=4
    |
    | Kalau bukan:
    |
    | /alumni/123
    |
    */

    private function alumniRouteParameters(
        Alumni $alumni,
        ?int $fromBatch = null
    ): array {
        $parameters = [
            'alumni' =>
                $alumni->id,
        ];


        if ($fromBatch !== null) {
            $parameters['from_batch'] =
                $fromBatch;
        }


        return $parameters;
    }
}