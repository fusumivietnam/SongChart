<?php

declare(strict_types=1);

$adminTwoFactorMode = (string) env('SONGCHART_ADMIN_2FA_MODE', env('APP_ENV') === 'local' ? 'disabled' : 'required');
if (! in_array($adminTwoFactorMode, ['required', 'disabled'], true)) {
    $adminTwoFactorMode = 'required';
}

$productionEnvironmentGuardEnabled = (bool) env(
    'SONGCHART_PRODUCTION_ENVIRONMENT_GUARD',
    env('APP_ENV') === 'production',
);

$developmentDatabaseMode = (string) env('SONGCHART_DEV_DATABASE_MODE', 'local');
$developmentStorageMode = (string) env('SONGCHART_DEV_STORAGE_MODE', 'local');

return [
    'search' => [
        'demo_enabled' => (bool) env('SONGCHART_DEMO_SEARCH', false),
    ],
    'discovery' => [
        'queue' => env('SONGCHART_DISCOVERY_QUEUE', 'discovery-projections'),
        'schedule_enabled' => (bool) env('SONGCHART_DISCOVERY_SCHEDULE_ENABLED', true),
        'projection_batch_size' => (int) env('SONGCHART_DISCOVERY_PROJECTION_BATCH_SIZE', 250),
        'projection_ttl_minutes' => (int) env('SONGCHART_DISCOVERY_PROJECTION_TTL_MINUTES', 15),
    ],
    'charts' => [
        'youtube_view_count_schedule_enabled' => (bool) env('SONGCHART_YOUTUBE_VIEW_CHART_SCHEDULE_ENABLED', false),
    ],

    'core_version' => env('SONGCHART_CORE_VERSION', env('APP_VERSION', 'unreleased')),

    'production' => [
        'environment_guard_enabled' => $productionEnvironmentGuardEnabled,
        'queue_monitor_max' => (int) env('SONGCHART_QUEUE_MONITOR_MAX', 100),
    ],

    'development' => [
        'database' => [
            'mode' => $developmentDatabaseMode,
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'sslmode' => env('DB_SSLMODE', 'prefer'),
            'expected_database' => env('SONGCHART_DEV_DATABASE_EXPECTED_NAME'),
        ],
        'storage' => [
            'mode' => $developmentStorageMode,
            'disk' => env('SONGCHART_DEV_STORAGE_DISK', $developmentStorageMode === 'remote' ? 'r2' : 'local'),
            'bucket' => env('R2_BUCKET'),
            'endpoint' => env('R2_ENDPOINT'),
            'key' => env('R2_ACCESS_KEY_ID'),
            'secret' => env('R2_SECRET_ACCESS_KEY'),
        ],
    ],
    'security' => [
        'admin_2fa_mode' => $adminTwoFactorMode,
    ],

    'providers' => [
        'youtube' => [
            'enabled' => (bool) env('YOUTUBE_ENABLED', false),
            'base_url' => env('YOUTUBE_BASE_URL', 'https://www.googleapis.com'),
            'api_key' => env('YOUTUBE_API_KEY', ''),
            'connect_timeout_seconds' => (int) env('YOUTUBE_CONNECT_TIMEOUT_SECONDS', 5),
            'timeout_seconds' => (int) env('YOUTUBE_TIMEOUT_SECONDS', 15),
            'quota' => [
                'search_daily_limit' => (int) env('YOUTUBE_SEARCH_DAILY_LIMIT', 100),
                'general_daily_units' => (int) env('YOUTUBE_GENERAL_DAILY_UNITS', 10000),
            ],
        ],
        'musicbrainz' => [
            'enabled' => (bool) env('MUSICBRAINZ_ENABLED', false),
            'base_url' => env('MUSICBRAINZ_BASE_URL', 'https://musicbrainz.org/ws/2'),
            'user_agent' => env('MUSICBRAINZ_USER_AGENT', 'SongChart/unreleased (contact@example.com)'),
            'connect_timeout_seconds' => (int) env('MUSICBRAINZ_CONNECT_TIMEOUT_SECONDS', 5),
            'timeout_seconds' => (int) env('MUSICBRAINZ_TIMEOUT_SECONDS', 15),
            'rate' => [
                'strategy' => env('MUSICBRAINZ_RATE_STRATEGY', 'minimum_interval'),
                'minimum_interval_ms' => (int) env('MUSICBRAINZ_MINIMUM_INTERVAL_MS', 1100),
                'default_cooldown_seconds' => (int) env('MUSICBRAINZ_DEFAULT_COOLDOWN_SECONDS', 2),
                'maximum_cooldown_seconds' => (int) env('MUSICBRAINZ_MAXIMUM_COOLDOWN_SECONDS', 900),
                'lock_wait_seconds' => (int) env('MUSICBRAINZ_RATE_LOCK_WAIT_SECONDS', 10),
            ],
            'enrichment' => [
                'fresh_for_days' => (int) env('MUSICBRAINZ_ENRICHMENT_FRESH_FOR_DAYS', 30),
                'daily_execution_budget' => (int) env('MUSICBRAINZ_ENRICHMENT_DAILY_BUDGET', 500),
                'candidate_limit' => (int) env('MUSICBRAINZ_ENRICHMENT_CANDIDATE_LIMIT', 5),
            ],
        ],
        'health' => [
            'queue' => env('SONGCHART_PROVIDER_HEALTH_QUEUE', 'provider-health'),
            'schedule_enabled' => (bool) env('SONGCHART_PROVIDER_HEALTH_SCHEDULE_ENABLED', true),
            'stale_after_minutes' => (int) env('SONGCHART_PROVIDER_HEALTH_STALE_AFTER_MINUTES', 15),
        ],
        'ingestion' => [
            'import_queue' => env('SONGCHART_PROVIDER_IMPORT_QUEUE', 'provider-imports'),
            'normalization_queue' => env('SONGCHART_PROVIDER_NORMALIZATION_QUEUE', 'provider-normalization'),
        ],
    ],
];
