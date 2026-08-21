<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class AnalyzeDuplicateConflicts extends Command
{
    protected $signature = 'alumni:analyze-duplicate-conflicts';

    protected $description = 'Menganalisis jenis perbedaan pada NIM duplikat dari file sumber alumni';

    public function handle(): int
    {
        /*
        |--------------------------------------------------------------------------
        | FILE INPUT / OUTPUT
        |--------------------------------------------------------------------------
        */

        $sourcePath = storage_path(
            'app/audit/hasil/alumni_duplikat_konflik.csv'
        );

        $outputDirectory = storage_path(
            'app/audit/hasil/analisis-konflik'
        );

        if (! File::exists($sourcePath)) {
            $this->error(
                'File konflik tidak ditemukan: '.$sourcePath
            );

            return self::FAILURE;
        }

        File::ensureDirectoryExists(
            $outputDirectory
        );

        $summaryPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'ringkasan_konflik_per_nim.csv';

        $detailPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'detail_perbedaan_kolom.csv';

        $seriousPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'konflik_serius.csv';

        $complementaryPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'duplikat_saling_melengkapi.csv';


        /*
        |--------------------------------------------------------------------------
        | INSPEKSI CSV
        |--------------------------------------------------------------------------
        */

        [$delimiter, $headers] = $this->inspectCsv(
            $sourcePath
        );

        $nimGroupIndex = $this->findHeaderIndex(
            $headers,
            [
                '__nim_group',
                'nim',
            ]
        );

        if ($nimGroupIndex === null) {
            throw new RuntimeException(
                'Kolom NIM tidak ditemukan.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | KOLOM METADATA AUDIT YANG TIDAK PERLU DIBANDINGKAN
        |--------------------------------------------------------------------------
        */

        $ignoredHeaders = [
            '__nimgroup',
            '__duplicatetype',
            '__rowingroup',
            '__auditreason',
        ];


        /*
        |--------------------------------------------------------------------------
        | BACA DAN KELOMPOKKAN BERDASARKAN NIM
        |--------------------------------------------------------------------------
        */

        $this->info(
            'Membaca file konflik...'
        );

        $handle = fopen(
            $sourcePath,
            'rb'
        );

        if ($handle === false) {
            throw new RuntimeException(
                'File konflik tidak dapat dibuka.'
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

        $groups = [];

        $totalRows = 0;

        while (
            ($row = fgetcsv(
                $handle,
                0,
                $delimiter
            )) !== false
        ) {
            if ($this->isCompletelyEmptyRow($row)) {
                continue;
            }

            $totalRows++;

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
                $row[$nimGroupIndex] ?? ''
            );

            if ($nim === '') {
                continue;
            }

            $groups[$nim][] = $row;
        }

        fclose($handle);


        /*
        |--------------------------------------------------------------------------
        | SIAPKAN OUTPUT
        |--------------------------------------------------------------------------
        */

        $summaryHandle = fopen(
            $summaryPath,
            'wb'
        );

        $detailHandle = fopen(
            $detailPath,
            'wb'
        );

        $seriousHandle = fopen(
            $seriousPath,
            'wb'
        );

        $complementaryHandle = fopen(
            $complementaryPath,
            'wb'
        );

        if (
            $summaryHandle === false
            || $detailHandle === false
            || $seriousHandle === false
            || $complementaryHandle === false
        ) {
            throw new RuntimeException(
                'File output tidak dapat dibuat.'
            );
        }

        /*
         * UTF-8 BOM agar nyaman dibuka di Excel Windows.
         */
        foreach ([
            $summaryHandle,
            $detailHandle,
            $seriousHandle,
            $complementaryHandle,
        ] as $outputHandle) {
            fwrite(
                $outputHandle,
                "\xEF\xBB\xBF"
            );
        }


        /*
        |--------------------------------------------------------------------------
        | HEADER OUTPUT
        |--------------------------------------------------------------------------
        */

        fputcsv(
            $summaryHandle,
            [
                'NIM',
                'Jumlah Baris',
                'Klasifikasi',
                'Kolom Berbeda Nyata',
                'Kolom Saling Melengkapi',
                'Nama Berbeda',
                'Prodi Berbeda',
                'Fakultas Berbeda',
                'Angkatan/Tahun Masuk Berbeda',
                'Tahun/Tanggal Lulus Berbeda',
                'Perlu Review Manual',
            ]
        );

        fputcsv(
            $detailHandle,
            [
                'NIM',
                'Kolom',
                'Jenis Perbedaan',
                'Nilai yang Ditemukan',
            ]
        );

        fputcsv(
            $seriousHandle,
            [
                'NIM',
                'Jumlah Baris',
                'Kolom Konflik',
                'Alasan',
            ]
        );

        fputcsv(
            $complementaryHandle,
            [
                'NIM',
                'Jumlah Baris',
                'Kolom Saling Melengkapi',
                'Keterangan',
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | COUNTER
        |--------------------------------------------------------------------------
        */

        $totalGroups = 0;

        $formatOnlyGroups = 0;

        $complementaryGroups = 0;

        $realConflictGroups = 0;

        $nameConflictGroups = 0;

        $prodiConflictGroups = 0;

        $fakultasConflictGroups = 0;

        $angkatanConflictGroups = 0;

        $lulusConflictGroups = 0;


        /*
        |--------------------------------------------------------------------------
        | ANALISIS SETIAP NIM
        |--------------------------------------------------------------------------
        */

        foreach ($groups as $nim => $rows) {
            $totalGroups++;

            $realConflictColumns = [];

            $complementaryColumns = [];

            $formatOnlyColumns = [];


            foreach (
                $headers
                as $columnIndex => $header
            ) {
                $normalizedHeader =
                    $this->normalizeHeader(
                        $header
                    );

                /*
                 * Abaikan kolom metadata audit.
                 */
                if (
                    in_array(
                        $normalizedHeader,
                        $ignoredHeaders,
                        true
                    )
                ) {
                    continue;
                }


                /*
                 * Kumpulkan semua nilai asli pada kolom ini.
                 */
                $rawValues = [];

                foreach ($rows as $row) {
                    $rawValues[] =
                        trim(
                            (string) (
                                $row[$columnIndex]
                                ?? ''
                            )
                        );
                }


                /*
                 * Hilangkan nilai raw yang persis sama.
                 */
                $uniqueRawValues =
                    array_values(
                        array_unique(
                            $rawValues
                        )
                    );


                /*
                 * Jika raw pun sama, kolom tidak berbeda.
                 */
                if (
                    count($uniqueRawValues)
                    <= 1
                ) {
                    continue;
                }


                /*
                 * Normalisasi untuk membedakan:
                 *
                 * "Teknik Informatika"
                 * " teknik informatika "
                 *
                 * dari konflik yang benar-benar berbeda.
                 */
                $normalizedValues =
                    array_map(
                        fn ($value) =>
                            $this->normalizeValue(
                                $value,
                                $normalizedHeader
                            ),
                        $rawValues
                    );


                $uniqueNormalizedValues =
                    array_values(
                        array_unique(
                            $normalizedValues
                        )
                    );


                /*
                 * Bila setelah normalisasi hanya ada satu nilai,
                 * perbedaannya hanya format/spasi/huruf besar-kecil.
                 */
                if (
                    count(
                        $uniqueNormalizedValues
                    ) === 1
                ) {
                    $formatOnlyColumns[] =
                        $header;

                    fputcsv(
                        $detailHandle,
                        [
                            $nim,
                            $header,
                            'BEDA FORMAT SAJA',
                            implode(
                                ' || ',
                                $uniqueRawValues
                            ),
                        ]
                    );

                    continue;
                }


                /*
                 * Pisahkan nilai kosong dan non-kosong.
                 */
                $nonEmptyNormalized =
                    array_values(
                        array_filter(
                            $uniqueNormalizedValues,
                            fn ($value) =>
                                $value !== ''
                        )
                    );


                /*
                 * Jika hanya ada satu nilai non-kosong
                 * dan sisanya kosong:
                 *
                 * baris hanya saling melengkapi.
                 *
                 * Contoh:
                 *
                 * Baris A: Tahun Lulus kosong
                 * Baris B: Tahun Lulus 2021
                 */
                if (
                    count(
                        $nonEmptyNormalized
                    ) === 1
                    && in_array(
                        '',
                        $uniqueNormalizedValues,
                        true
                    )
                ) {
                    $complementaryColumns[] =
                        $header;

                    fputcsv(
                        $detailHandle,
                        [
                            $nim,
                            $header,
                            'SALING MELENGKAPI',
                            implode(
                                ' || ',
                                $uniqueRawValues
                            ),
                        ]
                    );

                    continue;
                }


                /*
                 * Lebih dari satu nilai non-kosong berbeda:
                 * konflik nyata.
                 */
                $realConflictColumns[] =
                    $header;

                fputcsv(
                    $detailHandle,
                    [
                        $nim,
                        $header,
                        'KONFLIK NYATA',
                        implode(
                            ' || ',
                            $uniqueRawValues
                        ),
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | DETEKSI JENIS KONFLIK PENTING
            |--------------------------------------------------------------------------
            */

            $nameConflict = $this->containsAlias(
                $realConflictColumns,
                [
                    'nama',
                    'namalengkap',
                    'namaalumni',
                ]
            );

            $prodiConflict = $this->containsAlias(
                $realConflictColumns,
                [
                    'prodi',
                    'programstudi',
                    'programstudy',
                ]
            );

            $fakultasConflict = $this->containsAlias(
                $realConflictColumns,
                [
                    'fakultas',
                    'faculty',
                ]
            );

            $angkatanConflict = $this->containsAlias(
                $realConflictColumns,
                [
                    'angkatan',
                    'tahunmasuk',
                    'tahunangkatan',
                ]
            );

            $lulusConflict = $this->containsAlias(
                $realConflictColumns,
                [
                    'tahunlulus',
                    'tanggallulus',
                    'tanggalwisuda',
                    'tahunwisuda',
                ]
            );


            if ($nameConflict) {
                $nameConflictGroups++;
            }

            if ($prodiConflict) {
                $prodiConflictGroups++;
            }

            if ($fakultasConflict) {
                $fakultasConflictGroups++;
            }

            if ($angkatanConflict) {
                $angkatanConflictGroups++;
            }

            if ($lulusConflict) {
                $lulusConflictGroups++;
            }


            /*
            |--------------------------------------------------------------------------
            | KLASIFIKASI KESELURUHAN NIM
            |--------------------------------------------------------------------------
            */

            if (
                count($realConflictColumns)
                > 0
            ) {
                $classification =
                    'KONFLIK DATA';

                $realConflictGroups++;
            } elseif (
                count(
                    $complementaryColumns
                ) > 0
            ) {
                $classification =
                    'SALING MELENGKAPI';

                $complementaryGroups++;
            } else {
                $classification =
                    'BEDA FORMAT SAJA';

                $formatOnlyGroups++;
            }


            /*
             * Review manual terutama bila identitas/akademik inti berbeda.
             */
            $manualReview =
                $nameConflict
                || $prodiConflict
                || $fakultasConflict
                || $angkatanConflict
                || $lulusConflict;


            /*
            |--------------------------------------------------------------------------
            | RINGKASAN
            |--------------------------------------------------------------------------
            */

            fputcsv(
                $summaryHandle,
                [
                    $nim,

                    count($rows),

                    $classification,

                    count(
                        $realConflictColumns
                    ) > 0
                        ? implode(
                            ', ',
                            $realConflictColumns
                        )
                        : '-',

                    count(
                        $complementaryColumns
                    ) > 0
                        ? implode(
                            ', ',
                            $complementaryColumns
                        )
                        : '-',

                    $nameConflict
                        ? 'YA'
                        : 'TIDAK',

                    $prodiConflict
                        ? 'YA'
                        : 'TIDAK',

                    $fakultasConflict
                        ? 'YA'
                        : 'TIDAK',

                    $angkatanConflict
                        ? 'YA'
                        : 'TIDAK',

                    $lulusConflict
                        ? 'YA'
                        : 'TIDAK',

                    $manualReview
                        ? 'YA'
                        : 'TIDAK',
                ]
            );


            /*
            |--------------------------------------------------------------------------
            | FILE KONFLIK SERIUS
            |--------------------------------------------------------------------------
            */

            if ($manualReview) {
                $reasons = [];

                if ($nameConflict) {
                    $reasons[] =
                        'Nama berbeda';
                }

                if ($prodiConflict) {
                    $reasons[] =
                        'Program Studi berbeda';
                }

                if ($fakultasConflict) {
                    $reasons[] =
                        'Fakultas berbeda';
                }

                if ($angkatanConflict) {
                    $reasons[] =
                        'Angkatan/Tahun Masuk berbeda';
                }

                if ($lulusConflict) {
                    $reasons[] =
                        'Tahun/Tanggal Lulus berbeda';
                }

                fputcsv(
                    $seriousHandle,
                    [
                        $nim,

                        count($rows),

                        implode(
                            ', ',
                            $realConflictColumns
                        ),

                        implode(
                            '; ',
                            $reasons
                        ),
                    ]
                );
            }


            /*
            |--------------------------------------------------------------------------
            | FILE SALING MELENGKAPI
            |--------------------------------------------------------------------------
            */

            if (
                count($realConflictColumns)
                === 0
                && count(
                    $complementaryColumns
                ) > 0
            ) {
                fputcsv(
                    $complementaryHandle,
                    [
                        $nim,

                        count($rows),

                        implode(
                            ', ',
                            $complementaryColumns
                        ),

                        'Tidak ada nilai non-kosong yang saling bertentangan',
                    ]
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | TUTUP FILE
        |--------------------------------------------------------------------------
        */

        fclose($summaryHandle);

        fclose($detailHandle);

        fclose($seriousHandle);

        fclose($complementaryHandle);


        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN HASIL
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'ANALISIS KONFLIK SELESAI'
        );

        $this->table(
            [
                'Pemeriksaan',
                'Jumlah',
            ],
            [
                [
                    'Baris konflik yang dianalisis',
                    number_format(
                        $totalRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Kelompok NIM dianalisis',
                    number_format(
                        $totalGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Hanya beda format',
                    number_format(
                        $formatOnlyGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Saling melengkapi',
                    number_format(
                        $complementaryGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Konflik data nyata',
                    number_format(
                        $realConflictGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM dengan nama berbeda',
                    number_format(
                        $nameConflictGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM dengan prodi berbeda',
                    number_format(
                        $prodiConflictGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM dengan fakultas berbeda',
                    number_format(
                        $fakultasConflictGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM dengan angkatan/tahun masuk berbeda',
                    number_format(
                        $angkatanConflictGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'NIM dengan tahun/tanggal lulus berbeda',
                    number_format(
                        $lulusConflictGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | OUTPUT
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'File hasil analisis:'
        );

        $this->line(
            '1. Ringkasan per NIM: '
            .$summaryPath
        );

        $this->line(
            '2. Detail perbedaan: '
            .$detailPath
        );

        $this->line(
            '3. Konflik serius: '
            .$seriousPath
        );

        $this->line(
            '4. Saling melengkapi: '
            .$complementaryPath
        );

        $this->newLine();

        $this->warn(
            'Database tidak diubah sama sekali.'
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
                'CSV tidak dapat dibuka.'
            );
        }

        $firstLine = fgets(
            $handle
        );

        fclose($handle);

        if ($firstLine === false) {
            throw new RuntimeException(
                'CSV kosong.'
            );
        }

        $possibleDelimiters = [
            ',',
            ';',
            "\t",
        ];

        $delimiter = ',';

        $highestColumnCount = 0;

        foreach (
            $possibleDelimiters
            as $candidate
        ) {
            $parsed = str_getcsv(
                $firstLine,
                $candidate
            );

            if (
                count($parsed)
                > $highestColumnCount
            ) {
                $highestColumnCount =
                    count($parsed);

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
                'CSV tidak dapat dibuka.'
            );
        }

        $headers = fgetcsv(
            $handle,
            0,
            $delimiter
        );

        fclose($handle);

        if ($headers === false) {
            throw new RuntimeException(
                'Header tidak ditemukan.'
            );
        }

        if (isset($headers[0])) {
            $headers[0] =
                preg_replace(
                    '/^\xEF\xBB\xBF/',
                    '',
                    $headers[0]
                );
        }

        return [
            $delimiter,
            $headers,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CARI INDEX HEADER
    |--------------------------------------------------------------------------
    */

    private function findHeaderIndex(
        array $headers,
        array $aliases
    ): ?int {
        $aliases = array_map(
            fn ($alias) =>
                $this->normalizeHeader(
                    $alias
                ),
            $aliases
        );

        foreach (
            $headers
            as $index => $header
        ) {
            if (
                in_array(
                    $this->normalizeHeader(
                        $header
                    ),
                    $aliases,
                    true
                )
            ) {
                return $index;
            }
        }

        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | NORMALISASI HEADER
    |--------------------------------------------------------------------------
    */

    private function normalizeHeader(
        mixed $value
    ): string {
        $value = mb_strtolower(
            trim(
                (string) $value
            )
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

        $nim = preg_replace(
            '/^\xEF\xBB\xBF/',
            '',
            $nim
        ) ?? $nim;

        $nim = preg_replace(
            "/^'/",
            '',
            $nim
        ) ?? $nim;

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
    | NORMALISASI VALUE
    |--------------------------------------------------------------------------
    */

    private function normalizeValue(
        mixed $value,
        string $header = ''
    ): string {
        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return '';
        }

        /*
         * Hilangkan whitespace berlebih.
         */
        $value = preg_replace(
            '/\s+/u',
            ' ',
            $value
        ) ?? $value;

        /*
         * Jika bentuk angka Excel:
         *
         * 2020.0 -> 2020
         */
        if (
            preg_match(
                '/^\d+\.0$/',
                $value
            )
        ) {
            $value = substr(
                $value,
                0,
                -2
            );
        }

        /*
         * Text dibuat lowercase supaya:
         *
         * KEHUTANAN
         * Kehutanan
         * kehutanan
         *
         * dianggap sama.
         */
        return mb_strtolower(
            trim($value)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CEK ALIAS KOLOM
    |--------------------------------------------------------------------------
    */

    private function containsAlias(
        array $columns,
        array $aliases
    ): bool {
        $normalizedColumns =
            array_map(
                fn ($column) =>
                    $this->normalizeHeader(
                        $column
                    ),
                $columns
            );

        $normalizedAliases =
            array_map(
                fn ($alias) =>
                    $this->normalizeHeader(
                        $alias
                    ),
                $aliases
            );

        foreach (
            $normalizedColumns
            as $column
        ) {
            if (
                in_array(
                    $column,
                    $normalizedAliases,
                    true
                )
            ) {
                return true;
            }
        }

        return false;
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