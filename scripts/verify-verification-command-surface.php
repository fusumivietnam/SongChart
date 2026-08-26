<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$contract = json_decode(
    (string) file_get_contents($root.'/docs/project/engineering/verification-command-surface.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];

foreach (array_keys($contract['removed_aliases'] ?? []) as $alias) {
    if (array_key_exists($alias, $scripts)) {
        $errors[] = "Removed Composer alias [{$alias}] must not be defined.";
    }
}

foreach ([
    'stage:verify',
    'canonical:verify',
    'release:package',
    'quality:verify',
    'test:postgres',
    'verification-topology:verify',
    'verification-surface:verify',
] as $required) {
    if (! array_key_exists($required, $scripts)) {
        $errors[] = "Required verification command [{$required}] is missing.";
    }
}

foreach (($contract['historical_orchestration']['retired_files'] ?? []) as $relative) {
    if (is_file($root.'/'.$relative)) {
        $errors[] = "Retired orchestration file is still executable in active scripts [{$relative}].";
    }
}

$activeDocs = [
    'PROJECT_AUTHORITY.md',
    'README.md',
    'docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md',
    'docs/project/engineering/DELIVERY_WORKFLOW.md',
    'docs/project/DEVELOPMENT_STATE.md',
    'docs/project/RELEASE_BASELINE_STATUS.md',
    'docs/project/docs/TESTING.md',
    'docs/project/stack/TESTING_TOOLCHAIN.md',
    'docs/operations/canonical-verification.md',
];

$forbiddenActiveText = [
    'composer release:verify',
    'composer verify',
    'composer test:all',
    'composer test:postgres-clean',
    'composer delivery:verify',
    'composer release-contract:verify',
    'scripts/export-release-baseline.ps1',
    'scripts/finalize-stage-11-release',
    'verify-songchart.bat',
    'stage-verify.bat',
    'songchart.bat',
    'docker-dev-setup.bat',
    'scripts/songchart.ps1',
    'scripts/verify-canonical.ps1',
    'Laragon-ready',
    'incremental changeset ZIP',
];

foreach ($activeDocs as $relative) {
    $path = $root.'/'.$relative;
    if (! is_file($path)) {
        $errors[] = "Active documentation surface is missing [{$relative}].";
        continue;
    }

    $source = (string) file_get_contents($path);
    foreach ($forbiddenActiveText as $forbidden) {
        if (str_contains($source, $forbidden)) {
            $errors[] = "Active documentation [{$relative}] still references removed workflow [{$forbidden}].";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Verification command surface failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Verification command surface passed: active workflow uses only canonical entrypoints.\n");
