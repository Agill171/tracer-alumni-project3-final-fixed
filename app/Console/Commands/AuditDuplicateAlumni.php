<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

class AuditDuplicateAlumni extends Command
{
    protected $signature = 'alumni:audit-duplicates';

    protected $description = 'Menganalisis NIM duplikat pada file sumber alumni dan mendeteksi konflik data';

    public function handle(): int
    {
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

        $conflictPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'alumni_duplikat_konflik.csv';

        $identicalPath = $outputDirectory
            .DIRECTORY_SEPARATOR
            .'alumni_duplikat_identik.csv';

        /*
        |--------------------------------------------------------------------------
        | BACA CSV
        |--------------------------------------------------------------------------
        */

        [
            $delimiter,
            $headers,
            $nimIndex,
        ] = $this->inspectCsv($sourcePath);

        $this->info('Memulai analisis duplikat alumni...');
        $this->line('Kolom NIM: '.$headers[$nimIndex]);

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
                $row[$nimIndex] ?? null
            );

            if ($nim === '') {
                continue;
            }

            $groups[$nim][] = $row;
        }

        fclose($handle);

        /*
        |--------------------------------------------------------------------------
        | OUTPUT FILE
        |--------------------------------------------------------------------------
        */

        $conflictHandle = fopen(
            $conflictPath,
            'wb'
        );

        $identicalHandle = fopen(
            $identicalPath,
            'wb'
        );

        if (
            $conflictHandle === false
            || $identicalHandle === false
        ) {
            throw new RuntimeException(
                'File output tidak dapat dibuat.'
            );
        }

        fwrite(
            $conflictHandle,
            "\xEF\xBB\xBF"
        );

        fwrite(
            $identicalHandle,
            "\xEF\xBB\xBF"
        );

        $outputHeaders = array_merge(
            [
                '__nim_group',
                '__duplicate_type',
                '__row_in_group',
            ],
            $headers
        );

        fputcsv(
            $conflictHandle,
            $outputHeaders
        );

        fputcsv(
            $identicalHandle,
            $outputHeaders
        );

        /*
        |--------------------------------------------------------------------------
        | ANALISIS DUPLIKAT
        |--------------------------------------------------------------------------
        */

        $duplicateGroups = 0;

        $duplicateRows = 0;

        $excessRows = 0;

        $identicalGroups = 0;

        $conflictGroups = 0;

        $identicalRows = 0;

        $conflictRows = 0;

        foreach ($groups as $nim => $rows) {
            $count = count($rows);

            if ($count <= 1) {
                continue;
            }

            $duplicateGroups++;

            $duplicateRows += $count;

            $excessRows += (
                $count - 1
            );

            /*
             * Bandingkan isi seluruh baris.
             */
            $normalizedVersions = [];

            foreach ($rows as $row) {
                $normalizedVersions[] =
                    $this->normalizeRow(
                        $row
                    );
            }

            /*
             * JSON dipakai agar mudah membandingkan seluruh kolom.
             */
            $signatures = array_map(
                fn (array $row) =>
                    json_encode(
                        $row,
                        JSON_UNESCAPED_UNICODE
                    ),
                $normalizedVersions
            );

            $uniqueSignatures = array_unique(
                $signatures
            );

            $isIdentical =
                count($uniqueSignatures) === 1;

            if ($isIdentical) {
                $identicalGroups++;

                foreach (
                    $rows as $index => $row
                ) {
                    fputcsv(
                        $identicalHandle,
                        array_merge(
                            [
                                $nim,
                                'IDENTIK',
                                $index + 1,
                            ],
                            $row
                        )
                    );

                    $identicalRows++;
                }

                continue;
            }

            /*
             * Ada perbedaan pada satu atau lebih kolom.
             */
            $conflictGroups++;

            foreach (
                $rows as $index => $row
            ) {
                fputcsv(
                    $conflictHandle,
                    array_merge(
                        [
                            $nim,
                            'BERBEDA',
                            $index + 1,
                        ],
                        $row
                    )
                );

                $conflictRows++;
            }
        }

        fclose($conflictHandle);
        fclose($identicalHandle);

        /*
        |--------------------------------------------------------------------------
        | HASIL
        |--------------------------------------------------------------------------
        */

        $this->newLine();

        $this->info(
            'AUDIT DUPLIKAT SELESAI'
        );

        $this->table(
            [
                'Pemeriksaan',
                'Jumlah',
            ],
            [
                [
                    'Total baris data',
                    number_format(
                        $totalRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Kelompok NIM duplikat',
                    number_format(
                        $duplicateGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Total baris dalam kelompok duplikat',
                    number_format(
                        $duplicateRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Baris duplikat berlebih',
                    number_format(
                        $excessRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Kelompok duplikat identik',
                    number_format(
                        $identicalGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Kelompok duplikat berbeda/konflik',
                    number_format(
                        $conflictGroups,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Baris pada kelompok identik',
                    number_format(
                        $identicalRows,
                        0,
                        ',',
                        '.'
                    ),
                ],

                [
                    'Baris pada kelompok konflik',
                    number_format(
                        $conflictRows,
                        0,
                        ',',
                        '.'
                    ),
                ],
            ]
        );

        $this->newLine();

        $this->info(
            'File hasil:'
        );

        $this->line(
            'Duplikat identik : '.$identicalPath
        );

        $this->line(
            'Duplikat konflik : '.$conflictPath
        );

        $this->newLine();

        if ($conflictGroups === 0) {
            $this->info(
                'Semua NIM duplikat memiliki data yang identik.'
            );
        } else {
            $this->warn(
                'Ada NIM yang sama tetapi isi datanya berbeda. File konflik perlu diperiksa.'
            );
        }

        $this->warn(
            'Command ini hanya membaca data. Database tidak diubah.'
        );

        return self::SUCCESS;
    }

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
                'Kolom NIM tidak ditemukan.'
            );
        }

        return [
            $delimiter,
            $headers,
            $nimIndex,
        ];
    }

    private function normalizeHeader(
        mixed $value
    ): string {
        $value = trim(
            mb_strtolower(
                (string) $value
            )
        );

        return preg_replace(
            '/[^a-z0-9]+/',
            '',
            $value
        ) ?? '';
    }

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

    private function normalizeRow(
        array $row
    ): array {
        return array_map(
            function ($value) {
                $value = trim(
                    (string) $value
                );

                /*
                 * Samakan whitespace berlebihan.
                 */
                $value = preg_replace(
                    '/\s+/u',
                    ' ',
                    $value
                ) ?? $value;

                return $value;
            },
            $row
        );
    }

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