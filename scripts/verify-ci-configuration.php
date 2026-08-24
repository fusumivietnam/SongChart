<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$requiredFiles = [
    '.github/workflows/tests.yml',
    'phpunit.xml',
    'composer.json',
    'package.json',
];

foreach ($requiredFiles as $file) {
    if (! is_file($root.DIRECTORY_SEPARATOR.$file)) {
        $errors[] = "Required CI file is missing: {$file}";
    }
}

$phpunit = is_file($root.'/phpunit.xml') ? file_get_contents($root.'/phpunit.xml') : '';
foreach (['tests/Unit', 'tests/Architecture', 'tests/Feature'] as $suitePath) {
    if (! is_string($phpunit) || ! str_contains($phpunit, $suitePath)) {
        $errors[] = "PHPUnit suite is not registered: {$suitePath}";
    }
}

$composer = is_file($root.'/composer.json')
    ? json_decode((string) file_get_contents($root.'/composer.json'), true)
    : null;

if (! is_array($composer)) {
    $errors[] = 'composer.json is not valid JSON.';
} else {
    $scripts = $composer['scripts'] ?? [];
    foreach (['quality:verify', 'test:unit', 'test:architecture', 'test:feature', 'test:postgres', 'ci:configuration', 'locks:verify', 'canonical:verify', 'stage:verify'] as $script) {
        if (! is_array($scripts) || ! array_key_exists($script, $scripts)) {
            $errors[] = "Composer CI script is missing: {$script}";
        }
    }
}

$workflow = is_file($root.'/.github/workflows/tests.yml')
    ? file_get_contents($root.'/.github/workflows/tests.yml')
    : '';

foreach (['quality:', 'tests-postgres:', 'frontend-build:'] as $job) {
    if (! is_string($workflow) || ! str_contains($workflow, $job)) {
        $errors[] = "GitHub Actions job is missing: {$job}";
    }
}

foreach (['composer validate --strict', 'composer quality:verify', 'composer test:postgres', 'npm ci --no-audit --no-fund', 'npm run build'] as $command) {
    if (! is_string($workflow) || ! str_contains($workflow, $command)) {
        $errors[] = "GitHub Actions command is missing: {$command}";
    }
}

if ($errors !== []) {
    foreach ($errors as $error) {
        fwrite(STDERR, $error.PHP_EOL);
    }

    exit(1);
}

echo 'CI configuration verification passed.'.PHP_EOL;
