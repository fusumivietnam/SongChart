<?php

declare(strict_types=1);

return [
    'default' => 0.50,
    'sources' => [
        'provider:musicbrainz' => [
            'default' => 0.82,
            'fields' => [
                'artist.name' => 0.98,
                'artist.artist_type' => 0.96,
                'artist.country_code' => 0.92,
                'release.title' => 0.96,
                'recording.title' => 0.98,
                'recording.duration_ms' => 0.94,
                'work.title' => 0.96,
            ],
        ],
        'provider:youtube' => [
            'default' => 0.45,
            'fields' => [],
        ],
        'songchart:editorial' => [
            'default' => 0.90,
            'fields' => [],
        ],
    ],
];
