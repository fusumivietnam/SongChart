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
if (($runtime['profiles']['laragon-development']['authority'] ?? null) !== 'compatibility-only') {
    $errors[] = 'Laragon must be compatibility-only.';
}
foreach (['songchart.bat', 'scripts/songchart.ps1', 'scripts/docker-stage-verify.sh', 'compose.dev.yml', 'compose.verify.yml'] as $file) {
    if (! is_file($root.'/'.$file)) {
        $errors[] = "Missing Docker-first development file [{$file}].";
    }
}
$cli = (string) file_get_contents($root.'/scripts/songchart.ps1');
foreach (['dev setup', 'dev up', 'dev down', 'dev status', 'dev logs', 'dev shell', 'artisan', 'composer', 'npm', 'test', 'verify'] as $command) {
    if (! in_array($command, $contract['cli']['commands'] ?? [], true)) {
        $errors[] = "Docker CLI contract missing [{$command}].";
    }
}
foreach (['compose.dev.yml', 'compose.verify.yml', 'scripts/docker-stage-verify.sh', 'verify-canonical.ps1'] as $signal) {
    if (! str_contains($cli, $signal)) {
        $errors[] = "Unified CLI does not own [{$signal}].";
    }
}

if (preg_match('/function\s+(?:Dev|VerifyCompose)\s*\([^)]*\$Args/i', $cli) === 1) {
    $errors[] = 'Docker compose helpers must not use PowerShell automatic variable name $Args as a parameter.';
}
if (! str_contains($cli, 'Dev -ComposeArgs') || ! str_contains($cli, 'VerifyCompose -ComposeArgs')) {
    $errors[] = 'Docker compose helpers must receive explicit -ComposeArgs arrays.';
}

$stageShell = (string) file_get_contents($root.'/scripts/docker-stage-verify.sh');
$canonicalShell = (string) file_get_contents($root.'/scripts/canonical-verify.sh');
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
foreach (['docker-dev-setup.bat' => 'dev setup', 'docker-dev-up.bat' => 'dev up', 'docker-dev-down.bat' => 'dev down', 'verify-songchart.bat' => 'verify'] as $file => $signal) {
    $source = (string) file_get_contents($root.'/'.$file);
    if (! str_contains($source, 'songchart.bat') || ! str_contains($source, $signal)) {
        $errors[] = "Legacy wrapper [{$file}] must be a compatibility shim to the unified CLI.";
    }
}
if ($errors !== []) {
    fwrite(STDERR, "Docker-first development verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}
fwrite(STDOUT, "Docker-first development authority passed.\n");
