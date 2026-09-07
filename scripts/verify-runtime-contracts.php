<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

try {
    $authority = json_decode((string) file_get_contents($root.'/docs/project/stack/runtime-environments.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Runtime contract verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$compose = (string) file_get_contents($root.'/compose.verify.yml');
$profile = $authority['profiles']['canonical-test'] ?? [];
$signals = [
    'APP_ENV: '.($profile['app_env'] ?? ''),
    'DB_CONNECTION: '.($profile['db_connection'] ?? ''),
    'DB_HOST: '.($profile['db_host'] ?? ''),
    'DB_DATABASE: '.($profile['db_database'] ?? ''),
    'REDIS_HOST: '.($profile['redis_host'] ?? ''),
    'postgres:18.4-bookworm',
];
foreach ($signals as $signal) {
    if (! str_contains($compose, $signal)) {
        $errors[] = "Canonical Compose runtime does not match authority [{$signal}].";
    }
}

$devCompose = (string) file_get_contents($root.'/compose.dev.yml');
$devEnvExample = (string) file_get_contents($root.'/.env.docker.example');
$devProfile = $authority['profiles']['docker-development'] ?? [];
foreach ([
    'APP_ENV: '.($devProfile['app_env'] ?? ''),
    'REDIS_HOST: '.($devProfile['redis_host'] ?? ''),
    'env_file:',
    '- .env.docker',
] as $signal) {
    if (! str_contains($devCompose, $signal)) {
        $errors[] = "Docker development runtime does not match authority [{$signal}].";
    }
}

if (($devProfile['database_authority_source'] ?? null) !== '.env.docker'
    || ($devProfile['database_mode_variable'] ?? null) !== 'SONGCHART_DEV_DATABASE_MODE'
    || ($devProfile['compose_must_not_override_database_identity'] ?? null) !== true
    || ($devProfile['database_modes'] ?? null) !== ['local', 'remote']) {
    $errors[] = 'Docker development database authority must be mode-aware and owned by .env.docker.';
}

foreach (['DB_CONNECTION:', 'DB_HOST:', 'DB_DATABASE:', 'DB_USERNAME:', 'DB_PASSWORD:'] as $forbiddenOverride) {
    if (str_contains($devCompose, $forbiddenOverride)) {
        $errors[] = "Docker development Compose must not override development database identity [{$forbiddenOverride}].";
    }
}

$localFallback = $devProfile['local_database_fallback'] ?? [];
foreach ([
    'SONGCHART_DEV_DATABASE_MODE=local',
    'DB_CONNECTION='.($devProfile['db_connection'] ?? ''),
    'DB_HOST='.($localFallback['db_host'] ?? ''),
    'DB_DATABASE='.($localFallback['db_database'] ?? ''),
    'DB_SSLMODE='.($localFallback['db_sslmode'] ?? ''),
] as $signal) {
    if (! str_contains($devEnvExample, $signal)) {
        $errors[] = "Docker development local fallback does not match authority [{$signal}].";
    }
}

$remoteDatabase = $devProfile['remote_database'] ?? [];
if (($remoteDatabase['requires_db_url'] ?? null) !== true
    || ($remoteDatabase['requires_tls'] ?? null) !== true
    || ($remoteDatabase['allows_local_host_fallback'] ?? null) !== false) {
    $errors[] = 'Docker development remote PostgreSQL authority must fail closed without DB_URL/TLS and must forbid local fallback.';
}

if (($authority['primary_development_profile'] ?? null) !== 'docker-development') {
    $errors[] = 'Docker development must be the primary runtime profile.';
}

$dockerfile = (string) file_get_contents($root.'/docker/verify/Dockerfile');
if (preg_match('/docker-php-ext-install\s+(.+?)\s+&&\s+pecl install redis/s', $dockerfile, $match) !== 1) {
    $errors[] = 'Unable to resolve docker-php-ext-install contract from canonical Dockerfile.';
} else {
    $compileBlock = preg_replace('/\\\\\s*/', ' ', $match[1]) ?? $match[1];
    foreach (($authority['php_extensions']['base_image'] ?? []) as $extension) {
        if (preg_match('/(^|\s)'.preg_quote((string) $extension, '/').'(\s|$)/', $compileBlock) === 1) {
            $errors[] = "Base-image PHP extension must not be rebuilt [{$extension}].";
        }
    }
    foreach (($authority['php_extensions']['compiled'] ?? []) as $extension) {
        if (preg_match('/(^|\s)'.preg_quote((string) $extension, '/').'(\s|$)/', $compileBlock) !== 1) {
            $errors[] = "Canonical Dockerfile must compile declared PHP extension [{$extension}].";
        }
    }
}

$smoke = (string) file_get_contents($root.'/scripts/verify-canonical-php-extensions.php');
foreach (($authority['php_extensions']['required'] ?? []) as $extension) {
    if (! str_contains($smoke, "'{$extension}'") && ! str_contains($smoke, "\"{$extension}\"")) {
        $errors[] = "PHP extension smoke contract is missing [{$extension}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Runtime contract verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Runtime contract verification passed.'.PHP_EOL);
