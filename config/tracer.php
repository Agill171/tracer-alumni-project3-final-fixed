<?php

return [
    'campus' => env(
        'TRACER_CAMPUS',
        'Universitas Muhammadiyah Malang'
    ),

    'sources' => [
        'google' => [
            'label' => 'Google Web',
            'priority' => 1,
            'prefix' => '',
            'url' => 'https://www.google.com/search?q={query}',
        ],

        'linkedin' => [
            'label' => 'LinkedIn',
            'priority' => 2,
            'prefix' => 'site:linkedin.com/in',
            'url' => 'https://www.google.com/search?q={query}',
        ],

        'company_web' => [
            'label' => 'Website / Tempat Kerja',
            'priority' => 3,
            'prefix' => '',
            'url' => 'https://www.google.com/search?q={query}',
        ],

        'instagram' => [
            'label' => 'Instagram',
            'priority' => 4,
            'prefix' => 'site:instagram.com',
            'url' => 'https://www.google.com/search?q={query}',
        ],

        'facebook' => [
            'label' => 'Facebook',
            'priority' => 5,
            'prefix' => 'site:facebook.com',
            'url' => 'https://www.google.com/search?q={query}',
        ],

        'tiktok' => [
            'label' => 'TikTok',
            'priority' => 6,
            'prefix' => 'site:tiktok.com',
            'url' => 'https://www.google.com/search?q={query}',
        ],

        'github' => [
            'label' => 'GitHub',
            'priority' => 10,
            'prefix' => '',
            'url' => 'https://github.com/search?q={query}&type=users',
        ],

        'google_scholar' => [
            'label' => 'Google Scholar',
            'priority' => 11,
            'prefix' => '',
            'url' => 'https://scholar.google.com/scholar?q={query}',
        ],

        'researchgate' => [
            'label' => 'ResearchGate',
            'priority' => 12,
            'prefix' => 'site:researchgate.net/profile',
            'url' => 'https://www.google.com/search?q={query}',
        ],

        'orcid' => [
            'label' => 'ORCID',
            'priority' => 13,
            'prefix' => '',
            'url' => 'https://orcid.org/orcid-search/search?searchQuery={query}',
        ],
    ],
];