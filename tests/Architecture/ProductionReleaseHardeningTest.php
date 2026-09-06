<?php

declare(strict_types=1);

it('keeps production release provenance transport recovery and operator boundaries explicit', function (): void {
    $manager = file_get_contents(base_path('scripts/production/manage.sh'));
    $env = file_get_contents(base_path('.env.production.example'));
    $compose = file_get_contents(base_path('compose.production.yml'));
    $caddy = file_get_contents(base_path('docker/production/Caddyfile'));
    $database = file_get_contents(base_path('config/database.php'));
    $verifyDockerfile = file_get_contents(base_path('docker/verify/Dockerfile'));
    $testsWorkflow = (string) file_get_contents(base_path('.github/workflows/tests.yml'));

    expect($manager)
        ->toContain('validate_release_provenance')
        ->toContain('SONGCHART_RELEASE_CHANNEL')
        ->toContain('SONGCHART_ACCEPTED_MAIN_SHA')
        ->toContain('git -C "$ROOT" diff --quiet')
        ->toContain('songchart:production-runtime-check')
        ->toContain('SONGCHART_FIRST_ADMIN_EMAIL')
        ->toContain('--require-existing')
        ->toContain('--if-missing')
        ->toContain('SONGCHART_BACKUP_MIRROR_DIR')
        ->toContain('chmod 600 "$target"')
        ->toContain('SONGCHART_MIN_MEMORY_MB')
        ->toContain('SONGCHART_MIN_DISK_MB')
        ->toContain('External PostgreSQL requires DB_SSLMODE=require, verify-ca, or verify-full.')
        ->toContain('External Redis requires REDIS_SCHEME=tls.');

    expect($env)
        ->toContain('SONGCHART_RELEASE_CHANNEL=local')
        ->toContain('SONGCHART_ACCEPTED_MAIN_SHA=')
        ->toContain('SONGCHART_FIRST_ADMIN_EMAIL=')
        ->toContain('SONGCHART_BACKUP_MIRROR_DIR=')
        ->toContain('REDIS_SCHEME=tcp');

    expect($compose)
        ->toContain('php artisan horizon:status --no-ansi | grep -qi running')
        ->toContain('artisan schedule:work')
        ->toContain('condition: service_healthy');

    expect($caddy)
        ->toContain('Strict-Transport-Security "max-age=31536000"')
        ->toContain('Permissions-Policy "camera=(), microphone=(), geolocation=()"')
        ->toContain('-Server');

    expect($database)
        ->toContain("'scheme' => env('REDIS_SCHEME', 'tcp')")
        ->toContain("'scheme' => env('REDIS_QUEUE_SCHEME', env('REDIS_SCHEME', 'tcp'))");

    expect($verifyDockerfile)
        ->toContain('composer:2@sha256:')
        ->toContain('node:24-bookworm-slim@sha256:')
        ->toContain('php:8.5-cli-bookworm@sha256:');

    foreach ([
        'actions/checkout',
        'actions/setup-node',
        'actions/cache',
        'actions/upload-artifact',
        'shivammathur/setup-php',
    ] as $action) {
        expect(preg_match('/uses:\s*'.preg_quote($action, '/').'@[0-9a-f]{40}/m', $testsWorkflow))->toBe(1);
    }
});
