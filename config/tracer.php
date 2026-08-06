<?php

return [
    'campus' => env('TRACER_CAMPUS', 'Universitas Muhammadiyah Malang'),

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
        'github' => [
            'label' => 'GitHub',
            'priority' => 3,
            'prefix' => '',
            'url' => 'https://github.com/search?q={query}&type=users',
        ],
        'google_scholar' => [
            'label' => 'Google Scholar',
            'priority' => 4,
            'prefix' => '',
            'url' => 'https://scholar.google.com/scholar?q={query}',
        ],
        'researchgate' => [
            'label' => 'ResearchGate',
            'priority' => 5,
            'prefix' => 'site:researchgate.net/profile',
            'url' => 'https://www.google.com/search?q={query}',
        ],
        'orcid' => [
            'label' => 'ORCID',
            'priority' => 6,
            'prefix' => '',
            'url' => 'https://orcid.org/orcid-search/search?searchQuery={query}',
        ],
        'instagram' => [
            'label' => 'Instagram',
            'priority' => 7,
            'prefix' => 'site:instagram.com',
            'url' => 'https://www.google.com/search?q={query}',
        ],
        'facebook' => [
            'label' => 'Facebook',
            'priority' => 8,
            'prefix' => 'site:facebook.com',
            'url' => 'https://www.google.com/search?q={query}',
        ],
    ],
];
