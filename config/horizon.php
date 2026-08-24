<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    'name' => env('HORIZON_NAME', 'SongChartWeb'),
    'domain' => env('HORIZON_DOMAIN'),
    'path' => env('HORIZON_PATH', 'horizon'),
    'use' => 'default',
    'prefix' => env('HORIZON_PREFIX', Str::slug((string) env('APP_NAME', 'songchart'), '_').'_horizon:'),
    'middleware' => ['web'],
    'waits' => [
        'redis:critical' => 15,
        'redis:discovery-projections' => 60,
        'redis:provider-health' => 45,
        'redis:provider-imports' => 90,
        'redis:provider-normalization' => 60,
        'redis:notifications' => 60,
        'redis:default' => 60,
    ],
    'trim' => [
        'recent' => 60,
        'pending' => 60,
        'completed' => 60,
        'recent_failed' => 10080,
        'failed' => 10080,
        'monitored' => 10080,
    ],
    'silenced' => [],
    'silenced_tags' => [],
    'metrics' => ['trim_snapshots' => ['job' => 288, 'queue' => 288]],
    'fast_termination' => false,
    'memory_limit' => 128,
    'defaults' => [
        'supervisor-default' => [
            'connection' => 'redis',
            'balance' => 'auto',
            'autoScalingStrategy' => 'time',
            'minProcesses' => 1,
            'maxProcesses' => 1,
            'balanceMaxShift' => 1,
            'balanceCooldown' => 3,
            'maxTime' => 0,
            'maxJobs' => 500,
            'memory' => 192,
            'tries' => 3,
            'timeout' => 90,
            'nice' => 0,
        ],
    ],
    'environments' => [
        'production' => [
            'supervisor-critical' => [
                'connection' => 'redis',
                'queue' => ['critical'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 4,
                'tries' => 3,
                'timeout' => 60,
            ],
            'supervisor-discovery' => [
                'connection' => 'redis',
                'queue' => ['discovery-projections'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 4,
                'tries' => 3,
                'timeout' => 150,
            ],
            'supervisor-providers' => [
                'connection' => 'redis',
                'queue' => ['provider-health', 'provider-imports', 'provider-normalization'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 6,
                'tries' => 3,
                'timeout' => 150,
            ],
            'supervisor-default' => [
                'connection' => 'redis',
                'queue' => ['notifications', 'default'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 4,
            ],
        ],
        'staging' => [
            'supervisor-main' => [
                'connection' => 'redis',
                'queue' => ['critical', 'discovery-projections', 'provider-health', 'provider-imports', 'provider-normalization', 'notifications', 'default'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 4,
                'timeout' => 150,
            ],
        ],
        'local' => [
            'supervisor-main' => [
                'connection' => 'redis',
                'queue' => ['critical', 'discovery-projections', 'provider-health', 'provider-imports', 'provider-normalization', 'notifications', 'default'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'timeout' => 150,
            ],
        ],
        '*' => [
            'supervisor-main' => [
                'connection' => 'redis',
                'queue' => ['critical', 'discovery-projections', 'provider-health', 'provider-imports', 'provider-normalization', 'notifications', 'default'],
                'balance' => 'auto',
                'minProcesses' => 1,
                'maxProcesses' => 2,
                'timeout' => 150,
            ],
        ],
    ],
    'watch' => ['app', 'bootstrap', 'config/**/*.php', 'database/**/*.php', 'public/**/*.php', 'resources/**/*.php', 'routes', 'composer.lock', 'composer.json', '.env'],
];
