<?php

declare(strict_types=1);

return [
    'recipes' => [
        'artist' => [
            'fields' => [
                ['key' => 'name', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low', 'reason' => 'Canonical Artist identity requires a provider-backed name.'],
                ['key' => 'artist_type', 'provider' => 'musicbrainz', 'priority' => 'high', 'cost_class' => 'low', 'reason' => 'Artist subtype drives public taxonomy and relationship semantics.'],
                ['key' => 'country_code', 'provider' => 'musicbrainz', 'priority' => 'normal', 'cost_class' => 'low', 'reason' => 'Country context improves catalog quality.'],
            ],
            'identifiers' => [
                ['key' => 'provider:musicbrainz', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low', 'reason' => 'MusicBrainz MBID anchors cross-provider identity resolution.'],
            ],
        ],
        'release_group' => [
            'fields' => [
                ['key' => 'title', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low'],
                ['key' => 'primary_type', 'provider' => 'musicbrainz', 'priority' => 'normal', 'cost_class' => 'low'],
            ],
            'identifiers' => [
                ['key' => 'musicbrainz_release_group', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low'],
            ],
        ],
        'release' => [
            'fields' => [
                ['key' => 'title', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low'],
                ['key' => 'released_on', 'provider' => 'musicbrainz', 'priority' => 'normal', 'cost_class' => 'low'],
            ],
            'identifiers' => [
                ['key' => 'musicbrainz_release', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low'],
            ],
        ],
        'recording' => [
            'fields' => [
                ['key' => 'title', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low'],
                ['key' => 'duration_ms', 'provider' => 'musicbrainz', 'priority' => 'high', 'cost_class' => 'low'],
            ],
            'identifiers' => [
                ['key' => 'musicbrainz_recording', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low'],
                ['key' => 'isrc', 'provider' => 'musicbrainz', 'priority' => 'high', 'cost_class' => 'low'],
            ],
            'destinations' => [
                ['provider' => 'youtube', 'priority' => 'normal', 'cost_class' => 'quota', 'reason' => 'Playback-ready Recording benefits from one reviewed YouTube destination.'],
            ],
        ],
        'work' => [
            'fields' => [
                ['key' => 'title', 'provider' => 'musicbrainz', 'priority' => 'critical', 'cost_class' => 'low'],
            ],
            'identifiers' => [
                ['key' => 'musicbrainz_work', 'provider' => 'musicbrainz', 'priority' => 'high', 'cost_class' => 'low'],
            ],
        ],
        'version' => [],
        'collection' => [],
    ],
];
