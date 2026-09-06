<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$read = static fn (string $relative): string => is_file($root.'/'.$relative) ? (string) file_get_contents($root.'/'.$relative) : '';

$runner = $read('scripts/run-database-tests.php');
$safety = $read('scripts/verify-test-database-safety.php');
$policy = $read('app/Support/Testing/TestDatabaseSafetyPolicy.php');
$middleware = $read('app/Http/Middleware/EnsureConfirmedTwoFactorAuthentication.php');
$ensureAdmin = $read('app/Console/Commands/EnsureLocalAdminCommand.php');
$setup = $read('app/Console/Commands/SetupLocalCommand.php');
$composer = json_decode($read('composer.json'), true);
$envExample = $read('.env.example');

foreach (['app/Support/Testing/TestDatabaseSafetyPolicy.php', 'database/migrations/2026_08_09_000100_create_songchart_environment_guard_table.php', '.env.testing.example'] as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = "Missing local data-safety authority: {$relative}";
    }
}

if (str_contains($runner, "'TEST_PGSQL_DATABASE', 'DB_DATABASE'")) {
    $errors[] = 'PostgreSQL test runner must never fall back from TEST_PGSQL_DATABASE to DB_DATABASE.';
}
if (! str_contains($runner, 'scripts/verify-test-database-safety.php')) {
    $errors[] = 'PostgreSQL test runner must invoke the destructive-test database safety guard.';
}
foreach (['must end with _test', 'same as the development database'] as $needle) {
    if (! str_contains($policy, $needle)) {
        $errors[] = "Test database safety policy is missing invariant: {$needle}";
    }
}
foreach (['database_role', "sqlState !== '3D000'", 'TEST_PGSQL_MAINTENANCE_DATABASE', 'select exists(select 1 from pg_database where datname = :database)', 'CREATE DATABASE {$quotedDatabase}', 'creating the isolated test database after name-safety validation'] as $needle) {
    if (! str_contains($safety, $needle)) {
        $errors[] = "Test database safety verifier is missing bootstrap/safety invariant: {$needle}";
    }
}
if (str_contains($safety, 'DROP DATABASE')) {
    $errors[] = 'Test database safety verifier must never drop a database while bootstrapping the test lane.';
}
if (
    ! str_contains($middleware, "app()->environment(['local', 'demo', 'testing'])")
    || ! str_contains($middleware, 'admin_2fa_mode')
    || ! str_contains($middleware, '$twoFactorMode = (string) config(\'songchart.security.admin_2fa_mode\', \'required\')')
    || ! str_contains($middleware, '$requiresTwoFactor = $twoFactorMode === \'required\' || ! $mayDisableTwoFactor')
) {
    $errors[] = '2FA bypass must be explicitly bounded to local/demo/testing runtimes and configuration-driven.';
}
foreach (['password and two-factor state were preserved', "app()->environment('local', 'testing')"] as $needle) {
    if (! str_contains($ensureAdmin, $needle)) {
        $errors[] = "Local administrator command is missing invariant: {$needle}";
    }
}
if (! str_contains($setup, 'admin:ensure-local')) {
    $errors[] = 'Local setup --admin must use the idempotent local administrator command.';
}
if (! preg_match('/^TEST_PGSQL_DATABASE=.+$/m', $envExample) || ! str_contains($envExample, 'SONGCHART_ADMIN_2FA_MODE=disabled')) {
    $errors[] = '.env.example must declare a configurable isolated test database and local 2FA ergonomics defaults.';
}
if (! is_array($composer) || ! isset($composer['scripts']['test-database:safety'], $composer['scripts']['local-data-safety:verify'])) {
    $errors[] = 'Composer is missing local data-safety gates.';
} else {
    $topology = json_decode(
        $read('docs/project/engineering/verification-topology.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $canonical = $composer['scripts']['canonical:verify'] ?? [];
    $stage = $composer['scripts']['stage:verify'] ?? [];

    if (! is_array($canonical) || ! is_array($stage)) {
        $errors[] = 'Canonical/stage verification topology must be ordered pipelines.';
    } else {
        $safetyPosition = array_search('@test-database:safety', $canonical, true);
        $stagePosition = array_search('@stage:verify', $canonical, true);
        $postgresPosition = array_search('@test:postgres', $stage, true);

        if (! is_int($safetyPosition) || ! is_int($stagePosition) || $safetyPosition >= $stagePosition) {
            $errors[] = 'canonical:verify must run test-database:safety before entering stage:verify.';
        }
        if (! is_int($postgresPosition)) {
            $errors[] = 'stage:verify must contain the destructive PostgreSQL release test lane.';
        }
        if (($topology['lanes']['canonical']['ordered_steps'] ?? null) !== $canonical) {
            $errors[] = 'canonical:verify drifted from verification-topology.json.';
        }
        if (($topology['lanes']['stage']['ordered_steps'] ?? null) !== $stage) {
            $errors[] = 'stage:verify drifted from verification-topology.json.';
        }
        if (array_key_exists('release:verify', $composer['scripts']) || array_key_exists('verify', $composer['scripts'])) {
            $errors[] = 'Legacy verification aliases must remain removed.';
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Local data safety verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, "Local data safety verification passed.\n");
