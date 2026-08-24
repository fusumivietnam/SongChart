<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';
$errors = [];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
foreach ($iterator as $file) {
    if (! $file->isFile()) {
        continue;
    }

    $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (
        ! str_ends_with($relative, '.ps1')
        && ! str_ends_with($relative, '.bat')
        && ! str_ends_with($relative, '.sh')
    ) {
        continue;
    }

    if (str_contains($relative, '.songchart-backups/') || str_contains($relative, 'vendor/')) {
        continue;
    }

    $source = (string) file_get_contents($file->getPathname());
    if (preg_match('/php\s+composer\.phar/i', $source) === 1) {
        $errors[] = "Release/local orchestration must not assume php composer.phar [{$relative}].";
    }

    if (preg_match('/php\s+artisan\s+test\s+tests[\\\\\/]Feature/i', $source) === 1) {
        $errors[] = "Orchestration must not run database Feature tests directly against ambient environment [{$relative}].";
    }
}

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
$releaseContract = (new RepositoryContractResolver($root))->value('release-pipeline');

if (($scripts['stage:verify'] ?? null) !== ($releaseContract['stage_steps'] ?? null)) {
    $errors[] = 'stage:verify drifted from the shared verification topology.';
}
if (($scripts['canonical:verify'] ?? null) !== ($releaseContract['canonical_steps'] ?? null)) {
    $errors[] = 'canonical:verify drifted from the shared verification topology.';
}
if (array_key_exists('release:verify', $scripts) || array_key_exists('verify', $scripts)) {
    $errors[] = 'Legacy verify/release:verify aliases must remain removed.';
}

$canonical = (string) file_get_contents($root.'/scripts/canonical-verify.sh');
foreach ([
    'composer install --no-interaction --prefer-dist --no-progress',
    'npm ci --no-audit --no-fund',
    'composer quality:normalize',
    'composer canonical:verify',
] as $signal) {
    if (! str_contains($canonical, $signal)) {
        $errors[] = "Canonical verification orchestration is missing [{$signal}].";
    }
}
foreach ([
    'composer release:verify',
    'composer quality:verify',
    'composer test:postgres',
    'npm run build',
    'php scripts/record-canonical-verification.php',
] as $forbidden) {
    if (str_contains($canonical, $forbidden)) {
        $errors[] = "Canonical shell duplicates closure work [{$forbidden}].";
    }
}
if (str_contains($canonical, 'composer update')) {
    $errors[] = 'Canonical verification must install from lockfile, never update dependencies.';
}

if ($errors !== []) {
    fwrite(STDERR, "Release orchestration verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Release orchestration verification passed.'.PHP_EOL);
