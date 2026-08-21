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
                        /*
                         * Query pelacakan alumni.
                         */
                        'query' =>
                            $query,


                        /*
                         * Basic = 1 credit/request.
                         */
                        'search_depth' =>
                            'basic',


                        /*
                         * Maksimal 20 menurut API Tavily.
                         */
                        'max_results' =>
                            $count,


                        /*
                         * Pencarian umum, bukan news.
                         */
                        'topic' =>
                            'general',


                        /*
                         * Kita tidak membutuhkan jawaban LLM.
                         */
                        'include_answer' =>
                            false,


                        /*
                         * Jangan ambil full HTML.
                         */
                        'include_raw_content' =>
                            false,


                        /*
                         * Tidak butuh image.
                         */
                        'include_images' =>
                            false,


                        /*
                         * Jangan biarkan Tavily mengubah
                         * parameter otomatis.
                         */
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
            ];
        }


        return $results;
    }
}