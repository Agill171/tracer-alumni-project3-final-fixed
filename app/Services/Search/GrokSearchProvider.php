<?php

namespace App\Services\Search;

use App\Contracts\SearchProvider;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GrokSearchProvider implements SearchProvider
{
    public function name(): string
    {
        return 'Grok AI (xAI)';
    }


    public function search(
        string $query,
        int $count = 5
    ): array {
        $apiKey =
            config(
                'enrichment.grok.api_key'
            );


        if (blank($apiKey)) {
            throw new RuntimeException(
                'GROK_API_KEY belum diisi.'
            );
        }


        $endpoint =
            config(
                'enrichment.grok.endpoint',
                'https://api.x.ai/v1/chat/completions'
            );


        $model =
            config(
                'enrichment.grok.model',
                'grok-4.6'
            );


        /*
         |--------------------------------------------------------------------------
         | SYSTEM PROMPT
         |--------------------------------------------------------------------------
         | Instruksi ke Grok untuk mencari data alumni dan mengembalikan teks
         | terstruktur agar Regex di IdentityMatchingService bisa membacanya.
         */
        $systemPrompt =
            "Anda adalah asisten pelacakan data alumni. Gunakan kemampuan web search Anda untuk mencari profil publik alumni. "
            . "Kembalikan teks dengan format: "
            . "Email: [email jika ada], No HP: [nomor jika ada], Tempat Bekerja: [tempat jika ada], "
            . "Posisi: [jabatan jika ada], Linkedin: [url jika ada], Instagram: [url jika ada]. "
            . "Jika data tidak ditemukan, tulis: Tidak ditemukan. Jangan membuat data palsu.";


        $userPrompt =
            "Cari data untuk query berikut: " . $query .
            " . Kembalikan teks sesuai format yang diminta.";


        $response =
            Http::acceptJson()
                ->withToken(
                    $apiKey
                )
                ->timeout(
                    (int) config(
                        'enrichment.timeout',
                        60
                    )
                )
                ->retry(
                    2,
                    1000
                )
                ->post(
                    $endpoint,
                    [
                        'model' => $model,
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $systemPrompt,
                            ],
                            [
                                'role' => 'user',
                                'content' => $userPrompt,
                            ],
                        ],
                        'temperature' => 0.1,
                        // Grok menggunakan "web_search_options" untuk mencari di web
                        'web_search_options' => [
                            'search_context_size' => 'high',
                        ],
                    ]
                );


        $response->throw();


        $content =
            data_get(
                $response->json(),
                'choices.0.message.content',
                ''
            );


        if (blank($content)) {
            return [];
        }


        /*
         |--------------------------------------------------------------------------
         | PARSE HASIL
         |--------------------------------------------------------------------------
         | Grok mengembalikan teks. Kita ubah menjadi struktur array yang sama
         | dengan Tavily agar IdentityMatchingService bisa memprosesnya.
         */
        $url =
            $this->extractUrl(
                $content
            );


        $results = [];

        // Ambil satu hasil utama dari Grok
        $results[] = [
            'rank' => 1,

            'title' =>
                'Hasil Pencarian Grok AI: ' .
                Str::limit($query, 50),

            'url' =>
                $url ?? 'https://example.com',

            'snippet' =>
                mb_substr($content, 0, 500),

            /*
             * Grok sudah mengembalikan teks terstruktur (Email, No HP, dll).
             * Memasukkan teks ini ke "raw_content" agar Regex IdentityMatchingService
             * bisa mengekstrak field Project 4 secara otomatis.
             */
            'raw_content' =>
                $content,
        ];


        return $results;
    }


    private function extractUrl(
        string $text
    ): ?string {
        if (
            preg_match(
                '/https?:\/\/[^\s]+/',
                $text,
                $matches
            )
        ) {
            return $matches[0];
        }


        return null;
    }
}