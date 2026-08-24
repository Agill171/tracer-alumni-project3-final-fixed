<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AUTO ENRICHMENT
    |--------------------------------------------------------------------------
    */

    'enabled' =>
        env(
            'AUTO_ENRICHMENT_ENABLED',
            false
        ),


    /*
    |--------------------------------------------------------------------------
    | SEARCH PROVIDER
    |--------------------------------------------------------------------------
    | Pilih 'grok' untuk menggunakan AI, atau 'tavily' untuk pencarian biasa.
    */

    'provider' =>
        env(
            'AUTO_ENRICHMENT_PROVIDER',
            'grok'
        ),


    /*
    |--------------------------------------------------------------------------
    | STORAGE SAFETY
    |--------------------------------------------------------------------------
    */

    'storage_allowed' =>
        env(
            'AUTO_ENRICHMENT_STORAGE_ALLOWED',
            false
        ),


    /*
    |--------------------------------------------------------------------------
    | SEARCH LIMIT
    |--------------------------------------------------------------------------
    */

    'max_queries_per_alumni' =>
        (int) env(
            'AUTO_ENRICHMENT_MAX_QUERIES',
            1
        ),


    'results_per_query' =>
        (int) env(
            'AUTO_ENRICHMENT_RESULTS_PER_QUERY',
            5
        ),


    /*
    |--------------------------------------------------------------------------
    | CONFIDENCE
    |--------------------------------------------------------------------------
    */

    'strong_threshold' =>
        (int) env(
            'AUTO_ENRICHMENT_STRONG_THRESHOLD',
            80
        ),


    'review_threshold' =>
        (int) env(
            'AUTO_ENRICHMENT_REVIEW_THRESHOLD',
            50
        ),


    /*
    |--------------------------------------------------------------------------
    | HTTP
    |--------------------------------------------------------------------------
    */

    'timeout' =>
        (int) env(
            'AUTO_ENRICHMENT_TIMEOUT',
            60
        ),


    /*
    |--------------------------------------------------------------------------
    | TAVILY SEARCH API
    |--------------------------------------------------------------------------
    */

    'tavily' => [

        'endpoint' =>
            env(
                'TAVILY_SEARCH_ENDPOINT',
                'https://api.tavily.com/search'
            ),


        'api_key' =>
            env(
                'TAVILY_API_KEY'
            ),

    ],


    /*
    |--------------------------------------------------------------------------
    | GROK AI (xAI)
    |--------------------------------------------------------------------------
    */

    'grok' => [

        'endpoint' =>
            env(
                'GROK_SEARCH_ENDPOINT',
                'https://api.x.ai/v1/chat/completions'
            ),


        'api_key' =>
            env(
                'GROK_API_KEY'
            ),

        'model' =>
            env(
                'GROK_MODEL',
                'grok-4.6'
            ),

    ],

];