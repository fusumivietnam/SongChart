<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$releaseMode = in_array('--release', $argv, true);
$errors = [];
$blockers = [];

$requiredFiles = [
    'AGENTS.md',
    'README.md',
    'composer.json',
    'package.json',
    'phpunit.xml',
    '.env.example',
    '.github/workflows/tests.yml',
    'docs/foundation/STAGE_11_FOUNDATION_BASELINE.md',
    'docs/foundation/STAGE_11_IMPLEMENTATION_STATUS.md',
    'docs/foundation/STAGE_11_9_TASK_CONTRACT.md',
    'docs/foundation/STAGE_11_9_FOUNDATION_CLOSURE_AUDIT.md',
    'docs/foundation/STAGE_11_10_TASK_CONTRACT.md',
    'docs/foundation/STAGE_11_10_FOUNDATION_RELEASE_CLOSURE.md',
    'scripts/verify-release-locks.php',
];

foreach ($requiredFiles as $relativePath) {
    if (! is_file($root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath))) {
        $errors[] = "Missing foundation closure file: {$relativePath}";
    }
}

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true);
if (! is_array($composer)) {
    $errors[] = 'composer.json is not valid JSON.';
} else {
    $scripts = $composer['scripts'] ?? [];
    foreach (['docs:verify', 'alignment:verify', 'source:verify', 'ci:configuration', 'foundation:audit', 'foundation:release', 'locks:verify', 'quality:verify', 'stage:verify', 'canonical:verify'] as $script) {
        if (! is_array($scripts) || ! array_key_exists($script, $scripts)) {
            $errors[] = "Missing Composer foundation command: {$script}";
        }
    }

    $quality = is_array($scripts) ? ($scripts['quality:verify'] ?? []) : [];
    foreach (['@docs:verify', '@alignment:verify', '@source:verify', '@ci:configuration', '@foundation:audit'] as $command) {
        if (! is_array($quality) || ! in_array($command, $quality, true)) {
            $errors[] = "quality:verify does not include {$command}.";
        }
    }

    $topology = json_decode(
        (string) file_get_contents($root.'/docs/project/engineering/verification-topology.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $stage = is_array($scripts) ? ($scripts['stage:verify'] ?? null) : null;
    $expectedStage = $topology['lanes']['stage']['ordered_steps'] ?? null;

    foreach (['verify', 'release:verify', 'test:all', 'test:postgres-clean', 'release-contract:verify', 'delivery:verify'] as $removedAlias) {
        if (array_key_exists($removedAlias, $scripts)) {
            $errors[] = "Removed Composer alias [{$removedAlias}] must not be restored.";
        }
    }
    if (! is_array($stage) || $stage !== $expectedStage) {
        $errors[] = 'stage:verify must match verification-topology.json.';
    } else {
        foreach (['@quality:verify', '@test:postgres', 'npm run build'] as $command) {
            if (! in_array($command, $stage, true)) {
                $errors[] = "stage:verify does not include {$command}.";
            }
        }
    }
}

$phpunit = (string) file_get_contents($root.'/phpunit.xml');
foreach (['tests/Unit', 'tests/Architecture', 'tests/Feature'] as $suite) {
    if (! str_contains($phpunit, $suite)) {
        $errors[] = "PHPUnit suite is not registered: {$suite}";
    }
}

$routes = (string) file_get_contents($root.'/routes/web.php');
foreach (['can:access-admin', 'can:manage-extensions'] as $gate) {
    if (! str_contains($routes, $gate)) {
        $errors[] = "Authorization route boundary is missing: {$gate}";
    }
}

$consoleRoutes = (string) file_get_contents($root.'/routes/console.php');
foreach (["Schedule::command('providers:health-check')", 'withoutOverlapping', 'onOneServer'] as $schedulerRule) {
    if (! str_contains($consoleRoutes, $schedulerRule)) {
        $errors[] = "Provider scheduler rule is missing: {$schedulerRule}";
    }
}

$environment = (string) file_get_contents($root.'/.env.example');
foreach (['SONGCHART_PROVIDER_HEALTH_QUEUE=', 'SONGCHART_PROVIDER_HEALTH_SCHEDULE_ENABLED=', 'SONGCHART_PROVIDER_HEALTH_STALE_AFTER_MINUTES='] as $key) {
    if (! str_contains($environment, $key)) {
        $errors[] = "Provider runtime configuration is missing from .env.example: {$key}";
    }
}

$workflow = (string) file_get_contents($root.'/.github/workflows/tests.yml');
foreach (['quality:', 'tests-postgres:', 'frontend-build:'] as $job) {
    if (! str_contains($workflow, $job)) {
        $errors[] = "CI job is missing: {$job}";
    }
}

$lockfiles = [
    'composer.lock' => 'PHP dependencies are not reproducibly locked.',
    'package-lock.json' => 'Frontend dependencies are not reproducibly locked.',
];

foreach ($lockfiles as $lockfile => $message) {
    if (! is_file($root.DIRECTORY_SEPARATOR.$lockfile)) {
        $blockers[] = "{$lockfile}: {$message}";
    }
}

if (str_contains($workflow, 'tests-sqlite:')) {
    $errors[] = 'CI still contains the deprecated release-authoritative SQLite job.';
}

if (str_contains($workflow, 'npm install --no-audit --no-fund')) {
    $errors[] = 'CI still contains the transitional npm install fallback.';
}

if (! str_contains($workflow, 'npm ci --no-audit --no-fund')) {
    $errors[] = 'CI does not install frontend dependencies with npm ci.';
}

if (! str_contains($workflow, 'composer validate --strict')) {
    $errors[] = 'CI does not validate Composer metadata and lockfiles strictly.';
}

if ($releaseMode && $blockers !== []) {
    array_push($errors, ...$blockers);
}

if ($errors !== []) {
    fwrite(STDERR, "Foundation closure verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, 'Foundation closure audit passed.'.PHP_EOL);

if ($blockers !== []) {
    fwrite(STDOUT, "Release blockers remain:\n- ".implode("\n- ", $blockers)."\n");
} else {
    fwrite(STDOUT, 'No lockfile release blockers detected.'.PHP_EOL);
}
