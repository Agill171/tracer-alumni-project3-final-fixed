<?php

namespace App\Services;

use App\Models\Alumni;
use App\Models\PelacakanQuery;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PelacakanQueryService
{
    public function availableSources(): array
    {
        return config('tracer.sources', []);
    }

    /**
     * Query dasar alumni.
     */
    public function buildBaseQuery(Alumni $alumni): string
    {
        return collect([
            $this->quote($alumni->nama),
            $this->quote(config('tracer.campus')),
            $alumni->prodi,
        ])
            ->filter(fn ($value) => filled($value))
            ->implode(' ');
    }

    /**
     * Membuat beberapa variasi query berdasarkan sumber.
     */
    private function buildQueries(
        Alumni $alumni,
        string $sourceKey,
        array $source
    ): array {
        $name = $this->quote($alumni->nama);
        $campus = $this->quote(config('tracer.campus'));
        $prodi = $this->quote($alumni->prodi);
        $tahunLulus = $alumni->tahun_lulus;
        $tempatKerja = $this->quote($alumni->tempat_bekerja);

        $prefix = trim($source['prefix'] ?? '');

        $queries = match ($sourceKey) {

            /*
             * Pencarian umum.
             */
            'google' => [
                "{$name} {$campus}",
                "{$name} {$prodi}",
                "{$name} {$campus} {$prodi}",
            ],

            /*
             * Profil profesional.
             */
            'linkedin' => [
                "{$prefix} {$name}",
                "{$prefix} {$name} {$campus}",
                "{$prefix} {$name} {$prodi}",
            ],

            /*
             * Pencarian pekerjaan/perusahaan.
             */
            'company_web' => [
                "{$name} {$campus} kerja",
                "{$name} {$prodi} kerja",
                "{$name} perusahaan",
                "{$name} karyawan",
            ],

            /*
             * Media sosial.
             */
            'instagram' => [
                "{$prefix} {$name}",
                "{$prefix} {$name} {$campus}",
            ],

            'facebook' => [
                "{$prefix} {$name}",
                "{$prefix} {$name} {$campus}",
            ],

            'tiktok' => [
                "{$prefix} {$name}",
                "{$prefix} {$name} {$campus}",
            ],

            /*
             * Sumber akademik/profesional tambahan.
             */
            'github' => [
                "{$name}",
                "{$name} {$prodi}",
            ],

            'google_scholar' => [
                "{$name}",
                "{$name} {$campus}",
            ],

            'researchgate' => [
                "{$prefix} {$name}",
                "{$prefix} {$name} {$campus}",
            ],

            'orcid' => [
                "{$name}",
            ],

            default => [
                trim("{$prefix} ".$this->buildBaseQuery($alumni)),
            ],
        };

        /*
         * Bila tahun lulus tersedia,
         * tambahkan variasi query dengan tahun.
         */
        if (
            filled($tahunLulus) &&
            in_array(
                $sourceKey,
                ['google', 'linkedin', 'company_web'],
                true
            )
        ) {
            $queries[] = trim(
                "{$prefix} {$name} {$campus} {$tahunLulus}"
            );
        }

        /*
         * Bila tempat kerja sudah diketahui dari proses sebelumnya,
         * gunakan juga sebagai verifikasi silang.
         */
        if (
            filled($alumni->tempat_bekerja) &&
            in_array(
                $sourceKey,
                ['google', 'linkedin', 'company_web'],
                true
            )
        ) {
            $queries[] = trim(
                "{$prefix} {$name} {$tempatKerja}"
            );
        }

        return collect($queries)
            ->map(fn ($query) => trim($query))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Generate dan simpan query pelacakan.
     */
    public function generate(
        Alumni $alumni,
        array $sourceKeys = [],
        ?int $userId = null
    ): Collection {
        $sources = collect($this->availableSources());

        if ($sourceKeys !== []) {
            $sources = $sources->filter(
                fn (array $source, string $key) =>
                    in_array($key, $sourceKeys, true)
            );
        }

        $generated = collect();

        foreach ($sources as $key => $source) {
            $queries = $this->buildQueries(
                $alumni,
                $key,
                $source
            );

            foreach ($queries as $query) {

                $encodedQuery = rawurlencode($query);

                $url = str_replace(
                    '{query}',
                    $encodedQuery,
                    $source['url']
                );

                $hash = hash(
                    'sha256',
                    $key.'|'.$query
                );

                $record = PelacakanQuery::updateOrCreate(
                    [
                        'alumni_id' => $alumni->id,
                        'sumber' => $key,
                        'query_hash' => $hash,
                    ],
                    [
                        'user_id' => $userId,
                        'prioritas' => $source['priority'] ?? 99,
                        'query' => $query,
                        'url_pencarian' => $url,
                        'status' => 'Disiapkan',
                        'generated_at' => now(),
                    ]
                );

                $generated->push($record);
            }
        }

        return $generated
            ->sortBy([
                ['prioritas', 'asc'],
                ['id', 'asc'],
            ])
            ->values();
    }

    /**
     * Label sumber.
     */
    public function sourceLabel(string $key): string
    {
        return data_get(
            $this->availableSources(),
            $key.'.label',
            Str::headline($key)
        );
    }

    /**
     * Membungkus teks dengan tanda kutip
     * untuk pencarian exact phrase.
     */
    private function quote(?string $value): string
    {
        if (blank($value)) {
            return '';
        }

        $value = trim($value);

        $value = str_replace(
            '"',
            '',
            $value
        );

        return '"'.$value.'"';
    }
}