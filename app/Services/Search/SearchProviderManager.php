<?php

namespace App\Services\Search;

use App\Contracts\SearchProvider;
use RuntimeException;

class SearchProviderManager
{
    public function __construct(
        private TavilySearchProvider $tavily,
        private GrokSearchProvider $grok
    ) {
        //
    }


    public function driver(): SearchProvider
    {
        $provider =
            config(
                'enrichment.provider',
                'tavily'
            );


        return match ($provider) {
            'tavily' =>
                $this->tavily,

            'grok' =>
                $this->grok,

            default =>
                throw new RuntimeException(
                    "Search provider [{$provider}] tidak didukung."
                ),
        };
    }
}