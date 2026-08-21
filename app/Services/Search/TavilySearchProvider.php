<?php

namespace App\Services\Search;

use App\Contracts\SearchProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TavilySearchProvider implements SearchProvider
{
    public function name(): string
    {
        return 'Tavily Search API';
    }


    public function search(
        string $query,
        int $count = 5
    ): array {
        $apiKey =
            config(
                'enrichment.tavily.api_key'
            );


        if (blank($apiKey)) {
            throw new RuntimeException(
                'TAVILY_API_KEY belum diisi.'
            );
        }


        $count =
            max(
                1,
                min(
                    20,
                    $count
                )
            );


        $endpoint =
            config(
                'enrichment.tavily.endpoint',
                'https://api.tavily.com/search'
            );


        $response =
            Http::acceptJson()
                ->withToken(
                    $apiKey
                )
                ->timeout(
                    (int) config(
                        'enrichment.timeout',
                        20
                    )
                )
                ->retry(
                    3,
                    750
                )
                ->post(
                    $endpoint,
                    [
                        'query' =>
                            $query,

                        'search_depth' =>
                            'basic',

                        'max_results' =>
                            $count,

                        'topic' =>
                            'general',

                        'include_answer' =>
                            false,

                        /*
                         * PERUBAHAN PENTING:
                         * Kita mengaktifkan raw_content agar Tavily
                         * membuka URL dan mengambil isi halaman.
                         * Ini sangat penting untuk mengekstrak Email & No HP
                         * guna meningkatkan Completeness Project 4.
                         */
                        'include_raw_content' =>
                            true,

                        'include_images' =>
                            false,

                        'auto_parameters' =>
                            false,
                    ]
                );


        $response->throw();


        $rows =
            data_get(
                $response->json(),
                'results',
                []
            );


        if (! is_array($rows)) {
            return [];
        }


        $results =
            [];


        foreach (
            $rows
            as $index => $row
        ) {
            $url =
                trim(
                    (string) data_get(
                        $row,
                        'url'
                    )
                );


            if (
                $url === ''
                || ! filter_var(
                    $url,
                    FILTER_VALIDATE_URL
                )
            ) {
                continue;
            }


            $title =
                trim(
                    strip_tags(
                        (string) data_get(
                            $row,
                            'title',
                            ''
                        )
                    )
                );


            /*
             * Tavily menggunakan field "content"
             * untuk snippet hasil pencarian.
             */
            $snippet =
                trim(
                    preg_replace(
                        '/\s+/u',
                        ' ',
                        strip_tags(
                            (string) data_get(
                                $row,
                                'content',
                                ''
                            )
                        )
                    ) ?? ''
                );


            /*
             * PERUBAHAN PENTING:
             * Mengambil raw_content dari hasil Tavily
             * dan meneruskannya ke IdentityMatchingService.
             */
            $rawContent =
                (string) data_get(
                    $row,
                    'raw_content',
                    ''
                );


            $results[] = [
                'rank' =>
                    $index + 1,

                'title' =>
                    $title !== ''
                        ? $title
                        : null,

                'url' =>
                    $url,

                'snippet' =>
                    $snippet !== ''
                        ? $snippet
                        : null,

                /*
                 * Field baru untuk ekstraksi data.
                 */
                'raw_content' =>
                    $rawContent !== ''
                        ? $rawContent
                        : null,
            ];
        }


        return $results;
    }
}