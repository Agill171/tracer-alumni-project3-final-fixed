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
        return config(
            'tracer.sources',
            []
        );
    }

    /*
    |--------------------------------------------------------------------------
    | QUERY DASAR
    |--------------------------------------------------------------------------
    |
    | Prioritas identitas:
    |
    | 1. Nama
    | 2. NIM
    | 3. Program Studi
    | 4. Fakultas jika tersedia
    | 5. Universitas Muhammadiyah Malang
    |
    */

    public function buildBaseQuery(
        Alumni $alumni
    ): string {
        return $this->joinQueryParts([
            $this->quote($alumni->nama),
            $this->quote($alumni->prodi),
            $this->quote(
                $alumni->getAttribute('fakultas')
            ),
            $this->quote(
                config('tracer.campus')
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE VARIASI QUERY
    |--------------------------------------------------------------------------
    */

    private function buildQueries(
        Alumni $alumni,
        string $sourceKey,
        array $source
    ): array {
        $nama = $this->quote(
            $alumni->nama
        );

        $nim = $this->quote(
            $alumni->nim
        );

        $prodi = $this->quote(
            $alumni->prodi
        );

        $fakultas = $this->quote(
            $alumni->getAttribute(
                'fakultas'
            )
        );

        $kampus = $this->quote(
            config('tracer.campus')
        );

        $tahunLulus = filled(
            $alumni->tahun_lulus
        )
            ? (string) $alumni->tahun_lulus
            : '';

        $tempatKerja = $this->quote(
            $alumni->tempat_bekerja
        );

        $prefix = trim(
            $source['prefix'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | GOOGLE WEB
        |--------------------------------------------------------------------------
        |
        | Query dibuat dari yang paling kuat terlebih dahulu.
        |
        */

        if ($sourceKey === 'google') {
            $queries = [

                /*
                 * Paling kuat apabila NIM pernah dipublikasikan.
                 */
                $this->joinQueryParts([
                    $nama,
                    $nim,
                ]),

                /*
                 * Nama + identitas akademik lengkap.
                 */
                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    $fakultas,
                    $kampus,
                ]),

                /*
                 * Nama + Prodi + Kampus.
                 */
                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    $kampus,
                ]),

                /*
                 * Nama + Kampus.
                 */
                $this->joinQueryParts([
                    $nama,
                    $kampus,
                ]),

                /*
                 * NIM + Kampus.
                 */
                $this->joinQueryParts([
                    $nim,
                    $kampus,
                ]),

                /*
                 * Nama + Prodi.
                 */
                $this->joinQueryParts([
                    $nama,
                    $prodi,
                ]),
            ];


            /*
             * Tambahkan tahun lulus sebagai pembeda
             * jika tersedia.
             */
            if ($tahunLulus !== '') {
                $queries[] =
                    $this->joinQueryParts([
                        $nama,
                        $prodi,
                        $kampus,
                        $tahunLulus,
                    ]);
            }


            /*
             * Query khusus untuk mencari email.
             */
            $queries[] =
                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    $kampus,
                    'email',
                ]);


            /*
             * Query khusus pekerjaan.
             */
            $queries[] =
                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    $kampus,
                    'kerja',
                ]);

            $queries[] =
                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    'perusahaan',
                ]);

            return $this->cleanQueries(
                $queries
            );
        }


        /*
        |--------------------------------------------------------------------------
        | LINKEDIN
        |--------------------------------------------------------------------------
        |
        | Jangan terlalu longgar.
        |
        | Nama tetap exact phrase agar Google tidak
        | membawa kita terlalu jauh ke orang lain.
        |
        */

        if ($sourceKey === 'linkedin') {
            $queries = [

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $kampus,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $prodi,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                ]),
            ];

            if ($tahunLulus !== '') {
                $queries[] =
                    $this->joinQueryParts([
                        $prefix,
                        $nama,
                        $kampus,
                        $tahunLulus,
                    ]);
            }

            return $this->cleanQueries(
                $queries
            );
        }


        /*
        |--------------------------------------------------------------------------
        | WEBSITE / TEMPAT KERJA
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'company_web') {
            $queries = [

                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    $kampus,
                    'kerja',
                ]),

                $this->joinQueryParts([
                    $nama,
                    $kampus,
                    'karyawan',
                ]),

                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    'perusahaan',
                ]),

                $this->joinQueryParts([
                    $nama,
                    'pegawai',
                ]),

                $this->joinQueryParts([
                    $nama,
                    'staff',
                ]),
            ];

            if (
                filled(
                    $alumni->tempat_bekerja
                )
            ) {
                $queries[] =
                    $this->joinQueryParts([
                        $nama,
                        $tempatKerja,
                    ]);
            }

            return $this->cleanQueries(
                $queries
            );
        }


        /*
        |--------------------------------------------------------------------------
        | INSTAGRAM
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'instagram') {
            return $this->cleanQueries([
                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $kampus,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $prodi,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                ]),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | FACEBOOK
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'facebook') {
            return $this->cleanQueries([
                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $kampus,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $prodi,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                ]),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | TIKTOK
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'tiktok') {
            return $this->cleanQueries([
                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $kampus,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $prodi,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                ]),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | GITHUB
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'github') {
            return $this->cleanQueries([
                $alumni->nama,

                $this->joinQueryParts([
                    $alumni->nama,
                    $alumni->prodi,
                ]),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | GOOGLE SCHOLAR
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'google_scholar') {
            return $this->cleanQueries([
                $nama,

                $this->joinQueryParts([
                    $nama,
                    $kampus,
                ]),

                $this->joinQueryParts([
                    $nama,
                    $prodi,
                    $kampus,
                ]),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | RESEARCHGATE
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'researchgate') {
            return $this->cleanQueries([
                $this->joinQueryParts([
                    $prefix,
                    $nama,
                ]),

                $this->joinQueryParts([
                    $prefix,
                    $nama,
                    $kampus,
                ]),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | ORCID
        |--------------------------------------------------------------------------
        */

        if ($sourceKey === 'orcid') {
            return $this->cleanQueries([
                $nama,

                $this->joinQueryParts([
                    $nama,
                    $kampus,
                ]),
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */

        return $this->cleanQueries([
            $this->joinQueryParts([
                $prefix,
                $this->buildBaseQuery(
                    $alumni
                ),
            ]),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GENERATE DAN SIMPAN
    |--------------------------------------------------------------------------
    */

    public function generate(
        Alumni $alumni,
        array $sourceKeys = [],
        ?int $userId = null
    ): Collection {
        $sources = collect(
            $this->availableSources()
        );

        if ($sourceKeys !== []) {
            $sources =
                $sources->filter(
                    fn (
                        array $source,
                        string $key
                    ) =>
                        in_array(
                            $key,
                            $sourceKeys,
                            true
                        )
                );
        }

        $generated = collect();

        foreach (
            $sources
            as $key => $source
        ) {
            $queries =
                $this->buildQueries(
                    $alumni,
                    $key,
                    $source
                );

            foreach (
                $queries
                as $query
            ) {
                $encodedQuery =
                    rawurlencode(
                        $query
                    );

                $url = str_replace(
                    '{query}',
                    $encodedQuery,
                    $source['url']
                );

                $hash = hash(
                    'sha256',
                    $key.'|'.$query
                );

                $record =
                    PelacakanQuery::firstOrNew([
                        'alumni_id' =>
                            $alumni->id,

                        'sumber' =>
                            $key,

                        'query_hash' =>
                            $hash,
                    ]);

                $record->user_id =
                    $userId;

                $record->prioritas =
                    $source['priority'] ?? 99;

                $record->query =
                    $query;

                $record->url_pencarian =
                    $url;

                if (
                    ! $record->exists
                    || blank(
                        $record->status
                    )
                ) {
                    $record->status =
                        'Disiapkan';
                }

                $record->generated_at =
                    now();

                $record->save();

                $generated->push(
                    $record
                );
            }
        }

        return $generated
            ->sortBy([
                [
                    'prioritas',
                    'asc',
                ],
                [
                    'id',
                    'asc',
                ],
            ])
            ->values();
    }

    /*
    |--------------------------------------------------------------------------
    | LABEL SUMBER
    |--------------------------------------------------------------------------
    */

    public function sourceLabel(
        string $key
    ): string {
        return data_get(
            $this->availableSources(),
            $key.'.label',
            Str::headline($key)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CLEAN QUERY
    |--------------------------------------------------------------------------
    */

    private function cleanQueries(
        array $queries
    ): array {
        return collect($queries)
            ->map(
                fn ($query) =>
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        trim(
                            (string) $query
                        )
                    )
            )
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | JOIN PARTS
    |--------------------------------------------------------------------------
    */

    private function joinQueryParts(
        array $parts
    ): string {
        return collect($parts)
            ->filter(
                fn ($value) =>
                    filled($value)
            )
            ->map(
                fn ($value) =>
                    trim(
                        (string) $value
                    )
            )
            ->implode(' ');
    }

    /*
    |--------------------------------------------------------------------------
    | EXACT PHRASE
    |--------------------------------------------------------------------------
    */

    private function quote(
        mixed $value
    ): string {
        if (blank($value)) {
            return '';
        }

        $value = trim(
            (string) $value
        );

        $value = str_replace(
            '"',
            '',
            $value
        );

        return '"'.$value.'"';
    }
}