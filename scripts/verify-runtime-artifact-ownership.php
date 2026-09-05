<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$runtimePrefixes = [
    'storage/framework/',
    'storage/logs/',
    'storage/upgrade-backups/',
    '.songchart-backups/',
];
$allowedTracked = [
    'storage/framework/cache/data/.gitignore',
    'storage/framework/sessions/.gitignore',
    'storage/framework/views/.gitignore',
    'storage/logs/.gitignore',
];

$command = 'git -C '.escapeshellarg($root).' ls-files -z';
exec($command, $lines, $status);
if ($status !== 0) {
    fwrite(STDERR, "Runtime artifact ownership verification failed: unable to enumerate tracked files.\n");
    exit(1);
}

$tracked = array_values(array_filter(explode("\0", implode("\n", $lines)), static fn (string $path): bool => $path !== ''));
$violations = [];

foreach ($tracked as $path) {
    if (in_array($path, $allowedTracked, true)) {
        continue;
    }

    foreach ($runtimePrefixes as $prefix) {
        if (str_starts_with($path, $prefix)) {
            $violations[] = "tracked runtime artifact [{$path}] must remain runtime-only";
            break;
        }
    }
}

$requiredProductionFiles = [
    'compose.production.yml',
    'docker/production/Dockerfile',
    'docker/production/Caddyfile',
    'scripts/production/manage.sh',
    'docs/project/stack/production-installation-contract.json',
    '.env.production.example',
];
foreach ($requiredProductionFiles as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $violations[] = "production artifact authority [{$relative}] is missing";
    }
}

$contractPath = $root.'/docs/project/stack/production-installation-contract.json';
if (is_file($contractPath)) {
    try {
        $contract = json_decode((string) file_get_contents($contractPath), true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($contract) || ($contract['schema_version'] ?? null) !== 1) {
            $violations[] = 'production installation contract must use schema_version 1';
        }
        if (($contract['entrypoint'] ?? null) !== './songchart prod') {
            $violations[] = 'production installation contract must delegate through ./songchart prod';
        }
        if (($contract['stable_internal_authority']['database_major'] ?? null) !== 18) {
            $violations[] = 'production installation contract must keep PostgreSQL major 18 authoritative';
        }
    } catch (Throwable $exception) {
        $violations[] = 'production installation contract is invalid JSON: '.$exception->getMessage();
    }
}

$composePath = $root.'/compose.production.yml';
if (is_file($composePath)) {
    $compose = (string) file_get_contents($composePath);
    foreach (['edge:', 'app:', 'queue:', 'scheduler:', 'postgres:', 'redis:', 'profiles: ["bundled-db"]', 'profiles: ["bundled-redis"]'] as $needle) {
        if (! str_contains($compose, $needle)) {
            $violations[] = "production Compose must contain [{$needle}]";
        }
    }
    if (preg_match('/ports:\s*\n\s*-\s*["\']?[^\n]*:(?:5432|6379)["\']?/m', $compose) === 1) {
        $violations[] = 'production PostgreSQL/Redis must not publish host ports by default';
    }
    if (! str_contains($compose, 'command: ["php", "artisan", "horizon"]')) {
        $violations[] = 'production queue service must delegate to Laravel Horizon';
    }
    if (! str_contains($compose, 'command: ["php", "artisan", "schedule:work"]')) {
        $violations[] = 'production scheduler service must delegate to Laravel schedule:work';
    }
}

$dockerfilePath = $root.'/docker/production/Dockerfile';
if (is_file($dockerfilePath)) {
    $dockerfile = (string) file_get_contents($dockerfilePath);
    foreach (['php:8.5-fpm-bookworm', 'node:24-bookworm-slim', '--no-dev', 'FROM php-base AS app', 'FROM caddy:2.11.3-alpine AS edge', 'org.opencontainers.image.revision'] as $needle) {
        if (! str_contains($dockerfile, $needle)) {
            $violations[] = "production Dockerfile must contain [{$needle}]";
        }
    }
    foreach (['mbstring', 'opcache', 'pcntl', 'pdo_pgsql', 'posix', 'redis'] as $extension) {
        if (! str_contains($dockerfile, $extension)) {
            $violations[] = "production Dockerfile must provide runtime extension [{$extension}]";
        }
    }
    if (str_contains($dockerfile, 'artisan serve')) {
        $violations[] = 'production Dockerfile must not use the PHP built-in development server';
    }
}

$managerPath = $root.'/scripts/production/manage.sh';
if (is_file($managerPath)) {
    $manager = (string) file_get_contents($managerPath);
    foreach (['configure)', 'install)', 'doctor)', 'build)', 'up)', 'down)', 'status)', 'smoke)', 'backup)', 'restore-drill)', 'chmod 600', 'migrate --force', 'pg_dump', 'pg_restore', 'songchart-production-latest.dump'] as $needle) {
        if (! str_contains($manager, $needle)) {
            $violations[] = "production manager must contain [{$needle}]";
        }
    }
    if (str_contains($manager, 'down -v') || str_contains($manager, 'down --volumes')) {
        $violations[] = 'normal production down must never delete durable volumes';
    }
    if (! str_contains($manager, 'songchart-restore-pg-') || ! str_contains($manager, 'songchart_restore')) {
        $violations[] = 'production restore verification must use an isolated PostgreSQL target';
    }
}

$environmentPath = $root.'/.env.production.example';
if (is_file($environmentPath)) {
    $environment = (string) file_get_contents($environmentPath);
    foreach (['SONGCHART_DATABASE_MODE=', 'SONGCHART_REDIS_MODE=', 'SONGCHART_DOMAIN=', 'SONGCHART_HTTP_PORT=', 'SONGCHART_HTTPS_PORT=', 'SONGCHART_BACKUP_RETENTION_COUNT=', 'DB_SSLMODE='] as $needle) {
        if (! str_contains($environment, $needle)) {
            $violations[] = "production environment template must contain [{$needle}]";
        }
    }
}

$songchartPath = $root.'/songchart';
if (is_file($songchartPath) && ! str_contains((string) file_get_contents($songchartPath), 'scripts/production/manage.sh')) {
    $violations[] = 'SongChart CLI must delegate production operations to scripts/production/manage.sh';
}

if ($violations !== []) {
    fwrite(STDERR, "Runtime artifact ownership verification failed:\n- ".implode("\n- ", array_unique($violations))."\n");
    exit(1);
}

fwrite(STDOUT, "Runtime artifact ownership verification passed.\n");
