<?php

declare(strict_types=1);

use App\Support\Engineering\PhpSemanticFingerprint;

$root = dirname(__DIR__);
$errors = [];
require_once $root.'/app/Support/Engineering/PhpSemanticFingerprint.php';

$contractPath = $root.'/docs/project/stack/migration-lifecycle-contract.json';
$contract = json_decode((string) file_get_contents($contractPath), true, 512, JSON_THROW_ON_ERROR);

$fingerprintAlgorithm = $contract['fingerprint']['algorithm'] ?? null;
if ($fingerprintAlgorithm !== PhpSemanticFingerprint::ALGORITHM) {
    $errors[] = 'Migration lifecycle contract uses an unsupported semantic fingerprint algorithm.';
}

$baselineRelative = $contract['baseline']['path'] ?? null;
$baselinePath = is_string($baselineRelative) ? $root.'/'.$baselineRelative : null;
$historical = $contract['historical_migrations'] ?? [];

if (! is_array($historical) || $historical === []) {
    $errors[] = 'Migration lifecycle contract must declare historical migrations.';
} elseif ($baselinePath === null || ! is_file($baselinePath)) {
    $candidatePath = $root.'/candidate-verification.json';
    $candidate = is_file($candidatePath)
        ? json_decode((string) file_get_contents($candidatePath), true, 512, JSON_THROW_ON_ERROR)
        : [];

    if (($candidate['stage'] ?? null) !== '16.5.6' || ($candidate['closure_ready'] ?? false) === true) {
        $errors[] = 'Migration history baseline is missing outside the Stage 16.5.6 bootstrap window.';
    } else {
        fwrite(STDOUT, "Migration lifecycle bootstrap: exact canonical target baseline is not sealed yet.\n");
    }
} else {
    $baseline = json_decode((string) file_get_contents($baselinePath), true, 512, JSON_THROW_ON_ERROR);
    if (($baseline['algorithm'] ?? null) !== PhpSemanticFingerprint::ALGORITHM) {
        $errors[] = 'Migration history baseline uses an unsupported fingerprint algorithm.';
    }

    $frozen = $baseline['historical_migrations'] ?? [];
    foreach ($historical as $relative) {
        if (! is_string($relative)) {
            $errors[] = 'Migration lifecycle contract contains a non-string historical migration path.';

            continue;
        }

        $path = $root.'/'.$relative;
        if (! is_file($path)) {
            $errors[] = "Historical migration is missing [{$relative}].";

            continue;
        }

        $expected = is_array($frozen) ? ($frozen[$relative] ?? null) : null;
        $actual = PhpSemanticFingerprint::fromFile($path);
        if (! is_string($expected) || $actual !== $expected) {
            $errors[] = "Historical migration semantics changed after canonical freeze [{$relative}].";
        }
    }
}

foreach (($contract['current_forward_migrations'] ?? []) as $entry) {
    $relative = is_array($entry) ? ($entry['path'] ?? null) : null;
    if (! is_string($relative) || ! is_file($root.'/'.$relative)) {
        $errors[] = 'Forward migration contract references a missing migration.';

        continue;
    }

    $source = (string) file_get_contents($root.'/'.$relative);
    foreach (['Schema::hasTable', 'Schema::hasColumn'] as $guard) {
        if (! str_contains($source, $guard)) {
            $errors[] = "Forward corrective migration [{$relative}] must fail closed through {$guard}.";
        }
    }

    if (str_contains($source, 'dropColumn(')) {
        $errors[] = "Forward corrective migration [{$relative}] must not destructively remove repaired schema during rollback.";
    }
}

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$scripts = $composer['scripts'] ?? [];
if (($scripts['migration-lifecycle:verify'] ?? null) !== '@php scripts/verify-migration-lifecycle.php') {
    $errors[] = 'Composer must expose migration-lifecycle:verify.';
}
if (($scripts['migration-upgrade:verify'] ?? null) !== '@php scripts/run-migration-upgrade-test.php') {
    $errors[] = 'Composer must expose migration-upgrade:verify.';
}
if (($scripts['migration-lifecycle:seal'] ?? null) !== '@php scripts/seal-migration-lifecycle.php') {
    $errors[] = 'Composer must expose migration-lifecycle:seal.';
}

$historicalActivityPath = 'database/migrations/2026_08_13_000100_create_activity_log_table.php';
$forbiddenHistoricalColumnAssertion = "json('attribute_changes')";
foreach (['scripts/verify-*.php', 'tests/Architecture/*.php'] as $pattern) {
    foreach (glob($root.'/'.$pattern) ?: [] as $consumerPath) {
        $relative = str_replace('\\', '/', substr($consumerPath, strlen($root) + 1));
        if (in_array($relative, [
            'scripts/verify-migration-lifecycle.php',
            'tests/Architecture/MigrationLifecycleUpgradeSafetyTest.php',
        ], true)) {
            continue;
        }

        $consumer = (string) file_get_contents($consumerPath);
        if (str_contains($consumer, $historicalActivityPath)
            && str_contains($consumer, $forbiddenHistoricalColumnAssertion)
        ) {
            $errors[] = "Consumer [{$relative}] asserts a forward schema correction against frozen historical migration [{$historicalActivityPath}].";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Migration lifecycle verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Migration lifecycle verification passed: historical migrations are frozen and corrections are forward-only.'.PHP_EOL);
