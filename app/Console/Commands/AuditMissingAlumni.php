<?php

namespace App\Console\Commands;

use App\Models\Alumni;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class AuditMissingAlumni extends Command
{
    protected $signature = 'alumni:audit-missing';

    protected $description = 'Membandingkan CSV alumni asli dengan database berdasarkan NIM tanpa mengubah database';

    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | LOKASI FILE
        |--------------------------------------------------------------------------
        */

        $sourcePath = storage_path(
            'app/audit/Alumni 2000-2025.csv'
        );

        $outputDirectory = storage_path(
            'app/audit/hasil'
        );


        if (! File::exists($sourcePath)) {
            $this->error(
                'File sumber tidak ditemukan: '.$sourcePath
            );

            return self::FAILURE;
        }


        File::ensureDirectoryExists(
            $outputDirectory
        );


        $missingPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'alumni_missing_from_database.csv';

        $emptyNimPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'alumni_nim_kosong.csv';

        $duplicatePath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'alumni_nim_duplikat_di_file_sumber.csv';


        $this->info('Memulai audit alumni...');
        $this->newLine();

        $this->line(
            'File sumber: '.$sourcePath
        );


        /*
        |--------------------------------------------------------------------------
        | BACA HEADER + DETEKSI DELIMITER
        |--------------------------------------------------------------------------
        */

        [
            $delimiter,
            $headers,
            $nimIndex,
        ] = $this->inspectCsv($sourcePath);


        $this->line(
            'Delimiter terdeteksi: '
            .match ($delimiter) {
                ',' => 'koma (,)',
                ';' => 'titik koma (;)',
                "\t" => 'TAB',
                default => $delimiter,
            }
        );


        $this->line(
            'Kolom NIM: '.$headers[$nimIndex]
        );


        /*
        |--------------------------------------------------------------------------
        | PASS 1
        |
        | Hitung:
        | - jumlah seluruh baris
        | - NIM kosong
        | - frekuensi NIM
        |--------------------------------------------------------------------------
        */

        $this->newLine();
        $this->info('1/3 Membaca seluruh data pada CSV...');


        $sourceNimCounts = [];

        $totalSourceRows = 0;

        $emptyNimRows = 0;


        $handle = fopen(
            $sourcePath,
            'rb'
        );


        if ($handle === false) {
            throw new RuntimeException(
                'CSV tidak dapat dibuka.'
            );
        }


        /*
         * Lewati header.
         */
        fgetcsv(
            $handle,
            0,
            $delimiter
        );


        while (
            ($row = fgetcsv(
                $handle,
                0,
                $delimiter
            )) !== false
        ) {
            /*
             * Abaikan baris kosong sepenuhnya.
             */
            if ($this->isCompletelyEmptyRow($row)) {
                continue;
            }


            $totalSourceRows++;


            $nim = $this->normalizeNim(
                $row[$nimIndex] ?? null
            );


            if ($nim === '') {
                $emptyNimRows++;

                continue;
            }


            if (! isset($sourceNimCounts[$nim])) {
                $sourceNimCounts[$nim] = 0;
            }


            $sourceNimCounts[$nim]++;
        }


        fclose($handle);


        $uniqueSourceNims = count(
            $sourceNimCounts
        );


        $duplicateNimGroups = 0;

        $duplicateExcessRows = 0;


        foreach ($sourceNimCounts as $count) {
            if ($count > 1) {
                $duplicateNimGroups++;

                $duplicateExcessRows += (
                    $count - 1
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | BACA NIM DATABASE
        |--------------------------------------------------------------------------
        */

        $this->newLine();
        $this->info('2/3 Membaca NIM dari database...');


        $totalDatabase = Alumni::count();


        /*
         * Set associative dipakai supaya pencarian NIM sangat cepat.
         *
         * Contoh:
         *
         * [
         *     '202010320311069' => true
         * ]
         */
        $databaseNims = [];


        Alumni::query()
            ->select([
                'id',
                'nim',
            ])
            ->orderBy('id')
            ->chunkById(
                5000,
                function ($alumnis) use (&$databaseNims) {
                    foreach ($alumnis as $alumni) {
                        $nim = $this->normalizeNim(
                            $alumni->nim
                        );


                        if ($nim !== '') {
                            $databaseNims[$nim] = true;
                        }
                    }
                }
            );


        $uniqueDatabaseNims = count(
            $databaseNims
        );


        /*
        |--------------------------------------------------------------------------
        | PASS 2
        |
        | BUAT FILE LAPORAN
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            '3/3 Membandingkan CSV dengan database...'
        );


        $sourceHandle = fopen(
            $sourcePath,
            'rb'
        );


        if ($sourceHandle === false) {
            throw new RuntimeException(
                'CSV tidak dapat dibuka.'
            );
        }


        $missingHandle = fopen(
            $missingPath,
            'wb'
        );

        $emptyHandle = fopen(
            $emptyNimPath,
            'wb'
        );

        $duplicateHandle = fopen(
            $duplicatePath,
            'wb'
        );


        if (
            $missingHandle === false
            || $emptyHandle === false
            || $duplicateHandle === false
        ) {
            throw new RuntimeException(
                'File output audit tidak dapat dibuat.'
            );
        }


        /*
         * BOM UTF-8 agar Excel Windows membaca karakter
         * Indonesia dengan benar.
         */
        fwrite(
            $missingHandle,
            "\xEF\xBB\xBF"
        );

        fwrite(
            $emptyHandle,
            "\xEF\xBB\xBF"
        );

        fwrite(
            $duplicateHandle,
            "\xEF\xBB\xBF"
        );


        /*
         * Ambil dan buang header dari file sumber.
         */
        fgetcsv(
            $sourceHandle,
            0,
            $delimiter
        );


        $outputHeaders = array_merge(
            $headers,
            [
                '__audit_reason',
            ]
        );


        fputcsv(
            $missingHandle,
            $outputHeaders
        );

        fputcsv(
            $emptyHandle,
            $outputHeaders
        );

        fputcsv(
            $duplicateHandle,
            $outputHeaders
        );


        $missingRows = 0;

        $missingUniqueNims = [];

        $emptyWritten = 0;

        $duplicateRowsWritten = 0;


        while (
            ($row = fgetcsv(
                $sourceHandle,
                0,
                $delimiter
            )) !== false
        ) {
            if ($this->isCompletelyEmptyRow($row)) {
                continue;
            }


            /*
             * Samakan jumlah kolom dengan header.
             */
            $row = array_pad(
                $row,
                count($headers),
                ''
            );


            $row = array_slice(
                $row,
                0,
                count($headers)
            );


            $nim = $this->normalizeNim(
                $row[$nimIndex] ?? null
            );


            /*
             * NIM KOSONG
             */
            if ($nim === '') {
                fputcsv(
                    $emptyHandle,
                    array_merge(
                        $row,
                        [
                            'NIM kosong pada file sumber',
                        ]
                    )
                );


                $emptyWritten++;

                continue;
            }


            /*
             * NIM DUPLIKAT DI FILE DOSEN
             */
            if (
                ($sourceNimCounts[$nim] ?? 0) > 1
            ) {
                fputcsv(
                    $duplicateHandle,
                    array_merge(
                        $row,
                        [
                            'NIM muncul '
                            .$sourceNimCounts[$nim]
                            .' kali pada file sumber',
                        ]
                    )
                );


                $duplicateRowsWritten++;
            }


            /*
             * NIM ADA DI FILE DOSEN TAPI TIDAK ADA DI DATABASE.
             */
            if (! isset($databaseNims[$nim])) {
                fputcsv(
                    $missingHandle,
                    array_merge(
                        $row,
                        [
                            'NIM tidak ditemukan di database',
                        ]
                    )
                );


                $missingRows++;

                $missingUniqueNims[$nim] = true;
            }
        }


        fclose($sourceHandle);

        fclose($missingHandle);

        fclose($emptyHandle);

        fclose($duplicateHandle);


        $missingUniqueCount = count(
            $missingUniqueNims
        );


        /*
        |--------------------------------------------------------------------------
        | HASIL
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'AUDIT SELESAI'
        );


        $this->table(
            [
                'Pemeriksaan',
                'Jumlah',
            ],
            [
                [
                    'Baris alumni pada CSV',
                    number_format(
                        $totalSourceRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Data alumni di database',
                    number_format(
                        $totalDatabase,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Selisih jumlah baris',
                    number_format(
                        $totalSourceRows
                        - $totalDatabase,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM unik pada CSV',
                    number_format(
                        $uniqueSourceNims,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM unik pada database',
                    number_format(
                        $uniqueDatabaseNims,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Baris dengan NIM kosong',
                    number_format(
                        $emptyNimRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Kelompok NIM duplikat di CSV',
                    number_format(
                        $duplicateNimGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Baris duplikat berlebih',
                    number_format(
                        $duplicateExcessRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Baris tidak ditemukan di DB',
                    number_format(
                        $missingRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM unik tidak ditemukan di DB',
                    number_format(
                        $missingUniqueCount,
                        0,
                        ',',
                        '.'
                    ),
                ],
            ]
        );


        $this->newLine();

        $this->info(
            'File hasil audit dibuat di:'
        );


        $this->line(
            '1. Missing: '.$missingPath
        );

        $this->line(
            '2. NIM kosong: '.$emptyNimPath
        );

        $this->line(
            '3. NIM duplikat: '.$duplicatePath
        );


        $this->newLine();

        /*
         * Hanya informasi.
         * Tidak ada INSERT / UPDATE / DELETE.
         */
        $this->warn(
            'Database tidak diubah sama sekali oleh proses audit ini.'
        );


        return self::SUCCESS;
    }


    /*
    |--------------------------------------------------------------------------
    | INSPEKSI CSV
    |--------------------------------------------------------------------------
    */

    private function inspectCsv(
        string $path
    ): array {
        $handle = fopen(
            $path,
            'rb'
        );


        if ($handle === false) {
            throw new RuntimeException(
                'File CSV tidak dapat dibuka.'
            );
        }


        $firstLine = fgets(
            $handle
        );


        fclose($handle);


        if ($firstLine === false) {
            throw new RuntimeException(
                'File CSV kosong.'
            );
        }


        /*
         * Coba beberapa delimiter umum.
         */
        $possibleDelimiters = [
            ',',
            ';',
            "\t",
        ];


        $delimiter = ',';

        $highestColumnCount = 0;


        foreach (
            $possibleDelimiters as $candidate
        ) {
            $parsed = str_getcsv(
                $firstLine,
                $candidate
            );


            $columnCount = count(
                $parsed
            );


            if (
                $columnCount
                > $highestColumnCount
            ) {
                $highestColumnCount =
                    $columnCount;

                $delimiter =
                    $candidate;
            }
        }


        $handle = fopen(
            $path,
            'rb'
        );


        if ($handle === false) {
            throw new RuntimeException(
                'File CSV tidak dapat dibuka.'
            );
        }


        $headers = fgetcsv(
            $handle,
            0,
            $delimiter
        );


        fclose($handle);


        if (
            $headers === false
            || count($headers) === 0
        ) {
            throw new RuntimeException(
                'Header CSV tidak ditemukan.'
            );
        }


        /*
         * Bersihkan UTF-8 BOM.
         */
        if (isset($headers[0])) {
            $headers[0] = preg_replace(
                '/^\xEF\xBB\xBF/',
                '',
                $headers[0]
            );
        }


        $normalizedHeaders = array_map(
            fn ($header) =>
                $this->normalizeHeader(
                    $header
                ),
            $headers
        );


        /*
         * Kemungkinan nama kolom NIM.
         */
        $nimAliases = [
            'nim',
            'nomorindukmahasiswa',
            'nomorinduk',
            'nomahasiswa',
        ];


        $nimIndex = null;


        foreach (
            $normalizedHeaders
            as $index => $header
        ) {
            if (
                in_array(
                    $header,
                    $nimAliases,
                    true
                )
            ) {
                $nimIndex = $index;

                break;
            }
        }


        if ($nimIndex === null) {
            throw new RuntimeException(
                'Kolom NIM tidak ditemukan. Header yang terbaca: '
                .implode(
                    ' | ',
                    $headers
                )
            );
        }


        return [
            $delimiter,
            $headers,
            $nimIndex,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALISASI HEADER
    |--------------------------------------------------------------------------
    */

    private function normalizeHeader(
        mixed $value
    ): string {
        $value = (string) $value;

        $value = trim(
            $value
        );

        $value = mb_strtolower(
            $value
        );


        return preg_replace(
            '/[^a-z0-9]+/',
            '',
            $value
        ) ?? '';
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALISASI NIM
    |--------------------------------------------------------------------------
    |
    | NIM tetap dianggap string.
    | Tidak dikonversi ke integer agar format identitas tidak berubah.
    |--------------------------------------------------------------------------
    */

    private function normalizeNim(
        mixed $value
    ): string {
        if ($value === null) {
            return '';
        }


        $nim = trim(
            (string) $value
        );


        /*
         * Hilangkan BOM apabila ada.
         */
        $nim = preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $nim
        ) ?? $nim;


        /*
         * Kadang Excel memberi apostrof untuk memaksa format Text:
         *
         * '202010320311069
         *
         * Kita hilangkan apostrof pembukanya.
         */
        $nim = preg_replace(
            "/^'/",
            '',
            $nim
        ) ?? $nim;


        /*
         * Jika hasil CSV berasal dari angka Excel dan menjadi:
         *
         * 202010320311069.0
         *
         * ubah menjadi:
         *
         * 202010320311069
         */
        if (
            preg_match(
                '/^\d+\.0$/',
                $nim
            )
        ) {
            $nim = substr(
                $nim,
                0,
                -2
            );
        }


        return trim(
            $nim
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CEK BARIS KOSONG
    |--------------------------------------------------------------------------
    */

    private function isCompletelyEmptyRow(
        array $row
    ): bool {
        foreach ($row as $value) {
            if (
                trim(
                    (string) $value
                ) !== ''
            ) {
                return false;
            }
        }


        return true;
    }
}