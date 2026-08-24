<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'compose.verify.yml',
    'docker/verify/Dockerfile',
    'docker/verify/php.ini',
    'scripts/canonical-verify.sh',
    'scripts/verify-canonical.ps1',
    'scripts/verify-canonical-host.sh',
    'scripts/verify-postgres-major.php',
    'scripts/verify-canonical-php-extensions.php',
    'scripts/record-canonical-verification.php',
    'verify-songchart.bat',
];

$errors = [];

foreach ($required as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = "Missing canonical verification file [{$relative}].";
    }
}

$compose = (string) file_get_contents($root.'/compose.verify.yml');
foreach ([
    'image: postgres:18.4-bookworm',
    'dockerfile: docker/verify/Dockerfile',
    'condition: service_healthy',
    'TEST_PGSQL_DATABASE: songchart_verify_test',
    'songchart_verify_vendor:/workspace/vendor',
    'songchart_verify_node_modules:/workspace/node_modules',
    'songchart_verify_composer_cache:/tmp/composer-cache',
] as $signal) {
    if (! str_contains($compose, $signal)) {
        $errors[] = "compose.verify.yml missing [{$signal}].";
    }
}

$dockerfile = (string) file_get_contents($root.'/docker/verify/Dockerfile');
foreach ([
    'FROM php:8.5-cli-bookworm',
    'FROM composer:2 AS composer',
    'FROM node:22-bookworm-slim AS node',
    'docker-php-ext-install',
    'bcmath',
    'intl',
    'pcntl',
    'pdo_pgsql',
    'zip',
    'pecl install redis',
    'PHP extension smoke check passed.',
] as $signal) {
    if (! str_contains($dockerfile, $signal)) {
        $errors[] = "Verification Dockerfile missing [{$signal}].";
    }
}

$extensionInstallBlock = '';

if (preg_match('/docker-php-ext-install(?<block>.*?)&& pecl install redis/s', $dockerfile, $matches) === 1) {
    $extensionInstallBlock = (string) ($matches['block'] ?? '');
} else {
    $errors[] = 'Verification Dockerfile extension-install block could not be parsed.';
}

foreach (['curl', 'dom', 'mbstring', 'xml'] as $extension) {
    if (preg_match('/\\b'.preg_quote($extension, '/').'\\b/', $extensionInstallBlock) === 1) {
        $errors[] = "Verification Dockerfile must not rebuild core extension [{$extension}] from php:8.5-cli-bookworm.";
    }
}

$canonical = (string) file_get_contents($root.'/scripts/canonical-verify.sh');
$extensionPosition = strpos($canonical, 'php scripts/verify-canonical-php-extensions.php');
$composerInstallPosition = strpos($canonical, 'composer install');
$normalizePosition = strpos($canonical, 'composer quality:normalize');
$closurePosition = strpos($canonical, 'composer canonical:verify');

if (
    $extensionPosition === false
    || $composerInstallPosition === false
    || $extensionPosition >= $composerInstallPosition
) {
    $errors[] = 'Canonical PHP extension smoke check must run before Composer dependency installation.';
}

if ($normalizePosition === false || $closurePosition === false || $normalizePosition >= $closurePosition) {
    $errors[] = 'Canonical verification must normalize with locked Pint before the canonical closure entrypoint.';
}

$topology = json_decode(
    (string) file_get_contents($root.'/docs/project/engineering/verification-topology.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$expectedEntrypoint = $topology['canonical_shell_contract']['single_closure_entrypoint'] ?? null;
if ($expectedEntrypoint !== 'composer canonical:verify') {
    $errors[] = 'Verification topology must own composer canonical:verify as the canonical shell entrypoint.';
}
if (substr_count($canonical, 'composer canonical:verify') !== 1) {
    $errors[] = 'Canonical shell must invoke composer canonical:verify exactly once.';
}
foreach (['composer release:verify', 'composer stage:verify', 'composer quality:verify', 'composer test:postgres', 'npm run build'] as $forbidden) {
    if (str_contains($canonical, $forbidden)) {
        $errors[] = "Canonical shell duplicates consolidated closure gate [{$forbidden}].";
    }
}

if (substr_count($canonical, 'compile-repository-contracts.php --refresh-check') < 2) {
    $errors[] = 'Canonical verification must refresh the exact-container authority manifest before and after normalization.';
}

$workflow = (string) file_get_contents($root.'/.github/workflows/tests.yml');

if (! str_contains($workflow, 'image: postgres:18')) {
    $errors[] = 'CI PostgreSQL service must use major 18.';
}

if ($errors !== []) {
    fwrite(STDERR, "Reproducible verification environment failed:\n- ".implode("\n- ", $errors).PHP_EOL);

    exit(1);
}

fwrite(STDOUT, 'Reproducible verification environment contract passed.'.PHP_EOL);
