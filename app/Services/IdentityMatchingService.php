<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\HasilPelacakan;
use Illuminate\Support\Str;

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


        $project4 =
            $this->detectProject4(
                $url
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