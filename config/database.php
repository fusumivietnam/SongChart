<?php

declare(strict_types=1);

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'pgsql'),
    'connections' => [
        'sqlite' => ['driver' => 'sqlite', 'url' => env('DB_URL'), 'database' => env('DB_DATABASE', database_path('database.sqlite')), 'prefix' => '', 'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true)],
        'pgsql' => ['driver' => 'pgsql', 'url' => env('DB_URL'), 'host' => env('DB_HOST', '127.0.0.1'), 'port' => env('DB_PORT', '5432'), 'database' => env('DB_DATABASE', 'songchart'), 'username' => env('DB_USERNAME', 'postgres'), 'password' => env('DB_PASSWORD', ''), 'charset' => 'utf8', 'prefix' => '', 'prefix_indexes' => true, 'search_path' => 'public', 'sslmode' => env('DB_SSLMODE', 'prefer')],
    ],
    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => true],
    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'songchart')).'-database-'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'scheme' => env('REDIS_SCHEME', 'tcp'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'scheme' => env('REDIS_SCHEME', 'tcp'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
        'queue' => [
            'url' => env('REDIS_QUEUE_URL'),
            'scheme' => env('REDIS_QUEUE_SCHEME', env('REDIS_SCHEME', 'tcp')),
            'host' => env('REDIS_QUEUE_HOST', env('REDIS_HOST', '127.0.0.1')),
            'username' => env('REDIS_QUEUE_USERNAME', env('REDIS_USERNAME')),
            'password' => env('REDIS_QUEUE_PASSWORD', env('REDIS_PASSWORD')),
            'port' => env('REDIS_QUEUE_PORT', env('REDIS_PORT', '6379')),
            'database' => env('REDIS_QUEUE_DB', '0'),
        ],
    ],
];
