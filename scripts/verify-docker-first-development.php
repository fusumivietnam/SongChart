<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$contract = json_decode((string) file_get_contents($root.'/docs/project/stack/docker-development-contract.json'), true, 512, JSON_THROW_ON_ERROR);
$runtime = json_decode((string) file_get_contents($root.'/docs/project/stack/runtime-environments.json'), true, 512, JSON_THROW_ON_ERROR);

if (($contract['primary_local_runtime'] ?? null) !== 'docker-development') {
    $errors[] = 'Docker development must be the primary local runtime.';
}
if (($runtime['primary_development_profile'] ?? null) !== 'docker-development') {
    $errors[] = 'runtime-environments.json must designate docker-development as primary.';
}
if (isset($runtime['profiles']['laragon-development'])) {
    $errors[] = 'Retired Laragon runtime profile must not remain registered.';
}
foreach (['songchart', 'scripts/docker-stage-verify.sh', 'scripts/canonical-verify.sh', 'compose.dev.yml', 'compose.verify.yml'] as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = "Missing Docker-first development file [{$file}].";
    }
}

$cli = (string) file_get_contents($root.'/songchart');
foreach (['dev setup', 'dev ready', 'dev up', 'dev down', 'dev status', 'dev logs', 'dev shell', 'dev url', 'dev test', 'artisan', 'composer', 'npm', 'context', 'candidate', 'test', 'verify'] as $command) {
    if (! in_array($command, $contract['cli']['commands'] ?? [], true)) {
        $errors[] = "Docker CLI contract missing [{$command}].";
    }
}
foreach (['compose.dev.yml', 'compose.verify.yml', 'scripts/docker-stage-verify.sh'] as $signal) {
    if (! str_contains($cli, $signal)) {
        $errors[] = "Unified Linux CLI does not own [{$signal}].";
    }
}

$stageShell = (string) file_get_contents($root.'/scripts/docker-stage-verify.sh');
$canonicalShell = (string) file_get_contents($root.'/scripts/canonical-verify.sh');

$verifyCompose = (string) file_get_contents($root.'/compose.verify.yml');
if (! str_contains($verifyCompose, 'command: ["bash", "scripts/canonical-verify.sh"]')) {
    $errors[] = 'compose.verify.yml must own scripts/canonical-verify.sh.';
}
if (substr_count($canonicalShell, 'compile-repository-contracts.php --refresh-check') < 2) {
    $errors[] = 'Canonical Docker shell must refresh derived authority manifest before and after normalization.';
}
foreach (['composer install --no-interaction --prefer-dist --no-progress', 'npm ci --no-audit --no-fund', 'compile-repository-contracts.php --refresh-check', 'composer quality:normalize', 'composer stage:verify'] as $signal) {
    if (! str_contains($stageShell, $signal)) {
        $errors[] = "Docker stage lane missing [{$signal}].";
    }
}
$dev = (string) file_get_contents($root.'/compose.dev.yml');
$verify = (string) file_get_contents($root.'/compose.verify.yml');
foreach (['postgres:18.4-bookworm', 'redis:7.4-alpine', 'docker/verify/Dockerfile'] as $signal) {
    if (! str_contains($dev, $signal) || ! str_contains($verify, $signal)) {
        $errors[] = "Dev/verify runtime drift for [{$signal}].";
    }
}
if (str_contains($verify, 'songchart_docker') || ! str_contains($verify, 'songchart_verify_test')) {
    $errors[] = 'Canonical/test compose must remain isolated from the development database.';
}

foreach (['songchart.bat', 'stage-verify.bat', 'verify-songchart.bat', 'docker-dev-setup.bat', 'docker-dev-up.bat', 'docker-dev-down.bat', 'scripts/songchart.ps1', 'scripts/verify-canonical.ps1'] as $retiredWindowsFile) {
    if (is_file($root.'/'.$retiredWindowsFile)) {
        $errors[] = "Retired native Windows development wrapper must be removed [{$retiredWindowsFile}].";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Docker-first development verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}
fwrite(STDOUT, "Docker-first development authority passed.\n");
