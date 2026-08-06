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

    public function buildBaseQuery(Alumni $alumni): string
    {
        $parts = [
            '"'.trim($alumni->nama).'"',
            config('tracer.campus'),
            $alumni->prodi,
            $alumni->tahun_lulus,
            $alumni->tempat_bekerja,
        ];

        return collect($parts)
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->unique()
            ->implode(' ');
    }

    public function generate(Alumni $alumni, array $sourceKeys = [], ?int $userId = null): Collection
    {
        $sources = collect($this->availableSources());

        if ($sourceKeys !== []) {
            $sources = $sources->filter(fn (array $source, string $key) => in_array($key, $sourceKeys, true));
        }

        $baseQuery = $this->buildBaseQuery($alumni);

        return $sources
            ->map(function (array $source, string $key) use ($alumni, $baseQuery, $userId) {
                $query = trim(($source['prefix'] ?? '').' '.$baseQuery);
                $encodedQuery = rawurlencode($query);
                $url = str_replace('{query}', $encodedQuery, $source['url']);
                $hash = hash('sha256', $key.'|'.$query);

                return PelacakanQuery::updateOrCreate(
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
            })
            ->sortBy('prioritas')
            ->values();
    }

    public function sourceLabel(string $key): string
    {
        return data_get($this->availableSources(), $key.'.label', Str::headline($key));
    }
}
