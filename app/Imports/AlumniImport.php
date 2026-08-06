<?php

namespace App\Imports;

use App\Models\Alumni;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class AlumniImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    WithUpserts,
    WithValidation,
    SkipsEmptyRows,
    SkipsOnFailure,
    SkipsOnError,
    ShouldQueue
{
    use SkipsErrors;
    use SkipsFailures;

    public function model(array $row)
    {
        return new Alumni([
            'nama' => $row['nama'] ?? null,
            'nim' => $row['nim'] ?? null,
            'prodi' => $row['prodi'] ?? null,
            'angkatan' => $row['angkatan'] ?? null,
            'tahun_lulus' => $row['tahun_lulus'] ?? null,
            'email' => $row['email'] ?? null,
            'no_hp' => $row['no_hp'] ?? null,
            'tempat_bekerja' => $row['tempat_bekerja'] ?? null,
            'alamat_bekerja' => $row['alamat_bekerja'] ?? null,
            'posisi' => $row['posisi'] ?? null,
            'kategori_pekerjaan' => $row['kategori_pekerjaan'] ?? null,
            'linkedin' => $row['linkedin'] ?? null,
            'instagram' => $row['instagram'] ?? null,
            'facebook' => $row['facebook'] ?? null,
            'tiktok' => $row['tiktok'] ?? null,
            'sosmed_tempat_bekerja' => $row['sosmed_tempat_bekerja'] ?? null,
            'status_verifikasi' => $row['status_verifikasi'] ?? Alumni::STATUS_BELUM_DILACAK,
            'catatan' => $row['catatan'] ?? null,
        ]);
    }

    public function rules(): array
    {
        $currentYear = (int) now()->format('Y');

        return [
            '*.nama' => ['required', 'string', 'max:255'],
            '*.nim' => ['nullable', 'string', 'max:50'],
            '*.email' => ['nullable', 'email:rfc', 'max:255'],
            '*.angkatan' => ['nullable', 'integer', 'min:1900', 'max:'.($currentYear + 1)],
            '*.tahun_lulus' => ['nullable', 'integer', 'min:1900', 'max:'.($currentYear + 1)],
            '*.kategori_pekerjaan' => ['nullable', Rule::in(Alumni::kategoriPekerjaanOptions())],
            '*.status_verifikasi' => ['nullable', Rule::in(Alumni::statusOptions())],
        ];
    }

    public function uniqueBy(): string
    {
        return 'nim';
    }

    public function upsertColumns(): array
    {
        return [
            'nama',
            'prodi',
            'angkatan',
            'tahun_lulus',
            'email',
            'no_hp',
            'tempat_bekerja',
            'alamat_bekerja',
            'posisi',
            'kategori_pekerjaan',
            'linkedin',
            'instagram',
            'facebook',
            'tiktok',
            'sosmed_tempat_bekerja',
            'status_verifikasi',
            'catatan',
        ];
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }
}
