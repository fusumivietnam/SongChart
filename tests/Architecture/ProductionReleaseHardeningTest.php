<?php

declare(strict_types=1);

it('keeps production release provenance transport recovery and operator boundaries explicit', function (): void {
    $manager = file_get_contents(base_path('scripts/production/manage.sh'));
    $env = file_get_contents(base_path('.env.production.example'));
    $compose = file_get_contents(base_path('compose.production.yml'));
    $caddy = file_get_contents(base_path('docker/production/Caddyfile'));
    $database = file_get_contents(base_path('config/database.php'));
    $verifyDockerfile = file_get_contents(base_path('docker/verify/Dockerfile'));
    $testsWorkflow = file_get_contents(base_path('.github/workflows/tests.yml'));

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

    expect($testsWorkflow)
        ->toContain('actions/checkout@d23441a48e516b6c34aea4fa41551a30e30af803')
        ->toContain('actions/setup-node@249970729cb0ef3589644e2896645e5dc5ba9c38')
        ->toContain('actions/cache@0057852bfaa89a56745cba8c7296529d2fc39830')
        ->toContain('actions/upload-artifact@ea165f8d65b6e75b540449e92b4886f43607fa02')
        ->toContain('shivammathur/setup-php@f3e473d116dcccaddc5834248c87452386958240');
});
