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
    */

    'provider' =>
        env(
            'AUTO_ENRICHMENT_PROVIDER',
            'tavily'
        ),


    /*
    |--------------------------------------------------------------------------
    | STORAGE SAFETY
    |--------------------------------------------------------------------------
    |
    | Untuk sekarang false.
    |
    | Provider dapat diuji secara transient,
    | tetapi pipeline yang menyimpan search-result
    | ke pelacakan_kandidats belum kita aktifkan.
    |
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
            4
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
            20
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

];