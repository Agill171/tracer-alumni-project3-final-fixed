<?php

namespace App\Imports;

use App\Models\Alumni;
use DateTimeInterface;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AlumniImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    WithUpserts,
    WithUpsertColumns,
    WithValidation,
    SkipsEmptyRows,
    SkipsOnFailure,
    SkipsOnError,
    ShouldQueue
{
    use SkipsErrors;
    use SkipsFailures;

    /**
     * Mapping CSV alumni ke tabel alumnis.
     *
     * Header CSV asli:
     * Nama Lulusan,NIM,Tahun Masuk,Tanggal Lulus,Fakultas,Program Studi
     *
     * Dengan WithHeadingRow, header menjadi:
     * nama_lulusan
     * nim
     * tahun_masuk
     * tanggal_lulus
     * fakultas
     * program_studi
     */
    public function model(array $row)
    {
        $nama = $this->stringOrNull($row['nama_lulusan'] ?? null);

        if ($nama === null) {
            return null;
        }

        return new Alumni([
            'nama' => $nama,

            'nim' => $this->stringOrNull(
                $row['nim'] ?? null
            ),

            'prodi' => $this->stringOrNull(
                $row['program_studi'] ?? null
            ),

            'angkatan' => $this->extractYear(
                $row['tahun_masuk'] ?? null
            ),

            'tahun_lulus' => $this->extractYear(
                $row['tanggal_lulus'] ?? null
            ),

            /*
             * Data Project 4 tidak berasal dari file alumni awal.
             * Untuk alumni baru status awalnya Belum Dilacak.
             *
             * Field email, media sosial, pekerjaan, posisi, dll.
             * sengaja TIDAK diisi di sini agar import ulang
             * tidak menghapus hasil pelacakan yang sudah ada.
             */
            'status_verifikasi' => Alumni::STATUS_BELUM_DILACAK,
        ]);
    }

    /**
     * Validasi berdasarkan header CSV asli.
     */
    public function rules(): array
    {
        return [
            '*.nama_lulusan' => [
                'required',
                'string',
                'max:255',
            ],

            /*
             * Jangan dipaksa "string".
             * Excel/CSV kadang membaca NIM sebagai angka.
             * Nanti dikonversi menjadi string di model().
             */
            '*.nim' => [
                'nullable',
            ],

            '*.tahun_masuk' => [
                'nullable',
            ],

            '*.tanggal_lulus' => [
                'nullable',
            ],

            '*.fakultas' => [
                'nullable',
                'string',
                'max:255',
            ],

            '*.program_studi' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    /**
     * NIM digunakan sebagai identitas untuk upsert.
     *
     * Pastikan kolom nim di database memiliki unique index.
     */
    public function uniqueBy(): string
    {
        return 'nim';
    }

    /**
     * Jika NIM sudah ada, hanya data master alumni ini
     * yang boleh diperbarui.
     *
     * JANGAN masukkan email, linkedin, tempat_bekerja,
     * status_verifikasi, dll. agar hasil Project 4
     * tidak terhapus saat file alumni di-import ulang.
     */
    public function upsertColumns(): array
    {
        return [
            'nama',
            'prodi',
            'angkatan',
            'tahun_lulus',
        ];
    }

    /**
     * Memproses file besar secara bertahap.
     *
     * Sebelumnya 100, sehingga 142.293 baris menghasilkan
     * sekitar 1.423 chunk. 500 mengurangi overhead queue
     * secara signifikan tetapi masih cukup aman.
     */
    public function chunkSize(): int
    {
        return 500;
    }

    /**
     * Insert/update dilakukan per batch.
     */
    public function batchSize(): int
    {
        return 1;
    }

    /**
     * Rapikan nilai string.
     */
    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    /**
     * Mengambil tahun dari:
     *
     * 2020
     * "2020"
     * "2020-08-15"
     * "15/08/2020"
     * tanggal Excel berbentuk serial number
     */
    private function extractYear(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof DateTimeInterface) {
            return (int) $value->format('Y');
        }

        $currentYear = ((int) date('Y')) + 1;

        /*
         * Tahun biasa, misalnya 2018 atau 2024.
         */
        if (is_numeric($value)) {
            $numericValue = (float) $value;

            if (
                $numericValue >= 1900 &&
                $numericValue <= $currentYear
            ) {
                return (int) $numericValue;
            }

            /*
             * Jika berasal dari XLS/XLSX, tanggal dapat
             * diterima sebagai serial date Excel.
             */
            if (
                $numericValue > 20000 &&
                $numericValue < 100000
            ) {
                try {
                    return (int) ExcelDate::excelToDateTimeObject(
                        $numericValue
                    )->format('Y');
                } catch (\Throwable $e) {
                    // Lanjut ke pemeriksaan string di bawah.
                }
            }
        }

        /*
         * Mencari tahun dari teks tanggal.
         */
        if (
            preg_match(
                '/\b(19|20)\d{2}\b/',
                (string) $value,
                $matches
            )
        ) {
            $year = (int) $matches[0];

            if ($year >= 1900 && $year <= $currentYear) {
                return $year;
            }
        }

        return null;
    }
}