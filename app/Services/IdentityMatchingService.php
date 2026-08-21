<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\HasilPelacakan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log; // <-- Tambahan untuk Debug

class IdentityMatchingService
{
    public function analyze(
        Alumni $alumni,
        array $result
    ): array {
        $url =
            (string) (
                $result['url']
                ?? ''
            );

        /*
         * PERUBAHAN PENTING:
         * Gabungkan raw_content (full page) dan snippet (cuplikan Google)
         * agar Regex punya lebih banyak teks untuk mencari Email/No HP.
         */
        $rawContent =
            (string) (
                $result['raw_content']
                ?? ''
            )
            . ' '
            . (string) (
                $result['snippet']
                ?? ''
            );


        /*
         * TAMBAHAN DEBUG SEMENTARA:
         * Cek di Logs Worker (cari tulisan "RAW CONTENT DEBUG").
         * Hapus 3 baris di bawah ini setelah selesai testing!
         */
        Log::info('RAW CONTENT DEBUG: ' . substr($rawContent, 0, 500));

        $text =
            collect([
                $result['title']
                    ?? null,

                $result['snippet']
                    ?? null,

                urldecode(
                    $url
                ),
            ])
                ->filter(
                    fn ($value) =>
                        filled($value)
                )
                ->implode(' ');


        $normalized =
            $this->normalize(
                $text
            );


        $nameMatch =
            $this->contains(
                $normalized,
                $alumni->nama
            );


        $nimMatch =
            filled(
                $alumni->nim
            )
            && $this->contains(
                $normalized,
                $alumni->nim
            );


        $campusMatch =
            $this->contains(
                $normalized,
                config(
                    'tracer.campus',
                    'Universitas Muhammadiyah Malang'
                )
            );


        $timelineMatch =
            $this->yearMatch(
                $normalized,
                $alumni->tahun_lulus
            )
            || $this->yearMatch(
                $normalized,
                $alumni->angkatan
            );


        $bidangMatch =
            filled(
                $alumni->prodi
            )
            && $this->contains(
                $normalized,
                $alumni->prodi
            );


        /*
         * Afiliasi dianggap cocok jika:
         * NIM exact muncul atau kampus exact muncul.
         */
        $affiliationMatch =
            $nimMatch
            || $campusMatch;


        $signals = [
            'nama' =>
                $nameMatch,

            'afiliasi' =>
                $affiliationMatch,

            'timeline' =>
                $timelineMatch,

            'bidang' =>
                $bidangMatch,

            /*
             * Detail tambahan untuk audit otomatis.
             */
            'nim' =>
                $nimMatch,

            'kampus' =>
                $campusMatch,
        ];


        /*
         * Tetap mengikuti bobot sistem manual:
         *
         * Nama      40
         * Afiliasi  25
         * Timeline  20
         * Bidang    15
         */
        $score =
            ($nameMatch ? 40 : 0)
            + ($affiliationMatch ? 25 : 0)
            + ($timelineMatch ? 20 : 0)
            + ($bidangMatch ? 15 : 0);


        /*
         * PERUBAHAN PENTING:
         * Menggabungkan hasil deteksi Project 4 dari URL
         * dan dari Raw Content (untuk mencari Email, No HP, Tempat Kerja).
         */
        $project4FromUrl =
            $this->detectProject4(
                $url
            );

        $project4FromContent =
            $this->detectProject4FromContent(
                $rawContent // <-- Sekarang mengirim gabungan raw + snippet
            );

        $project4 =
            array_merge(
                $project4FromUrl,
                $project4FromContent
            );


        return [
            'signals' =>
                $signals,

            'score' =>
                min(
                    100,
                    $score
                ),

            'category' =>
                HasilPelacakan::classify(
                    $score
                ),

            'project4' =>
                $project4,

            'domain' =>
                $this->domain(
                    $url
                ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | EKSTRAKSI PROJECT 4 DARI RAW CONTENT (EMAIL, NO HP, DLL)
    |--------------------------------------------------------------------------
    |
    | Fungsi ini mencari data kontak langsung dari isi halaman web
    | yang ditemukan oleh Tavily.
    |
    */

    private function detectProject4FromContent(
        ?string $content
    ): array {
        if (
            blank(
                $content
            )
        ) {
            return [];
        }


        $project4 = [];


        /*
         * 1. Ekstrak Email
         */
        if (
            preg_match(
                '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/',
                $content,
                $matches
            )
        ) {
            $project4['email'] =
                $matches[0];
        }


        /*
         * 2. Ekstrak No HP (Format Indonesia)
         */
        if (
            preg_match(
                '/(?:\+62|62|08)[0-9]{8,13}/',
                $content,
                $matches
            )
        ) {
            $project4['no_hp'] =
                $matches[0];
        }


        /*
         * 3. Ekstrak Tempat Bekerja (Heuristik Sederhana)
         * Mencari pola "Bekerja di X", "Working at X", atau "at X"
         */
        if (
            preg_match(
                '/(?:Bekerja di|Working at|Currently at|at)\s+([A-Z][A-Za-z0-9&.\- ]{2,50})/',
                $content,
                $matches
            )
        ) {
            $project4['tempat_bekerja'] =
                trim(
                    $matches[1]
                );
        }


        return $project4;
    }


    private function detectProject4(
        string $url
    ): array {
        if (
            ! filter_var(
                $url,
                FILTER_VALIDATE_URL
            )
        ) {
            return [];
        }


        $host =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_HOST
                )
            );


        $host =
            preg_replace(
                '/^www\./',
                '',
                $host
            ) ?? $host;


        $path =
            strtolower(
                (string) parse_url(
                    $url,
                    PHP_URL_PATH
                )
            );


        /*
         * Hanya URL profil yang cukup jelas
         * yang kita jadikan kandidat Project 4 otomatis.
         */
        if (
            (
                $host === 'linkedin.com'
                || str_ends_with(
                    $host,
                    '.linkedin.com'
                )
            )
            && str_starts_with(
                $path,
                '/in/'
            )
        ) {
            return [
                'linkedin' =>
                    $url,
            ];
        }


        if (
            $host === 'instagram.com'
            || str_ends_with(
                $host,
                '.instagram.com'
            )
        ) {
            return [
                'instagram' =>
                    $url,
            ];
        }


        if (
            $host === 'facebook.com'
            || str_ends_with(
                $host,
                '.facebook.com'
            )
        ) {
            return [
                'facebook' =>
                    $url,
            ];
        }


        if (
            (
                $host === 'tiktok.com'
                || str_ends_with(
                    $host,
                    '.tiktok.com'
                )
            )
            && str_contains(
                $path,
                '/@'
            )
        ) {
            return [
                'tiktok' =>
                    $url,
            ];
        }


        return [];
    }


    private function contains(
        string $normalizedHaystack,
        ?string $needle
    ): bool {
        if (
            blank(
                $needle
            )
        ) {
            return false;
        }


        $needle =
            $this->normalize(
                $needle
            );


        if (
            $needle === ''
        ) {
            return false;
        }

        return str_contains(
            $normalizedHaystack,
            $needle
        );
    }


    private function yearMatch(
        string $normalizedText,
        mixed $year
    ): bool {
        if (
            blank(
                $year
            )
        ) {
            return false;
        }


        $year =
            trim(
                (string) $year
            );


        return preg_match(
            '/\b'
            .preg_quote(
                $year,
                '/'
            )
            .'\b/',
            $normalizedText
        ) === 1;
    }


    private function normalize(
        ?string $value
    ): string {
        if (
            blank(
                $value
            )
        ) {
            return '';
        }


        $value =
            Str::ascii(
                mb_strtolower(
                    trim(
                        $value
                    )
                )
            );


        $value =
            preg_replace(
                '/[^a-z0-9]+/',
                ' ',
                $value
            ) ?? '';


        $value =
            preg_replace(
                '/\s+/',
                ' ',
                $value
            ) ?? $value;


        return trim(
            $value
        );
    }


    private function domain(
        string $url
    ): ?string {
        $host =
            parse_url(
                $url,
                PHP_URL_HOST
            );


        if (
            ! is_string(
                $host
            )
            || $host === ''
        ) {
            return null;
        }


        return preg_replace(
            '/^www\./',
            '',
            strtolower(
                $host
            )
        );
    }
}