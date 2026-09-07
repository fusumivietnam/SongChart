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
$developmentAuthority = $read('app/Support/Development/DevelopmentDatabaseAuthority.php');
$developmentStatus = $read('app/Console/Commands/DevelopmentDatabaseStatusCommand.php');
$songchart = $read('songchart');
$compose = $read('compose.dev.yml');
$composer = json_decode($read('composer.json'), true);
$envExample = $read('.env.example');
$envDockerExample = $read('.env.docker.example');

foreach ([
    'app/Support/Testing/TestDatabaseSafetyPolicy.php',
    'database/migrations/2026_08_09_000100_create_songchart_environment_guard_table.php',
    '.env.testing.example',
    'app/Support/Development/DevelopmentDatabaseAuthority.php',
    'app/Console/Commands/DevelopmentDatabaseStatusCommand.php',
    'docs/project/engineering/development-database-contract.json',
] as $relative) {
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

foreach ([
    'Development database mode must explicitly be local or remote.',
    'Remote development database mode requires DB_URL.',
    'must not resolve to a local PostgreSQL host',
    'requires PostgreSQL TLS',
] as $needle) {
    if (! str_contains($developmentAuthority, $needle)) {
        $errors[] = "Development database authority is missing fail-closed invariant: {$needle}";
    }
}
foreach ([
    'current_database() as database_name',
    'current_user as database_user',
    'inet_server_addr()',
    'Development database identity mismatch',
] as $needle) {
    if (! str_contains($developmentStatus, $needle)) {
        $errors[] = "Development database runtime diagnostics are missing identity invariant: {$needle}";
    }
}
if (! str_contains($songchart, '.songchart-db-backups') || str_contains($songchart, 'BACKUP_DIR="$ROOT/.songchart-backups"')) {
    $errors[] = 'Development database backups must use their own artifact directory and must not reuse .songchart-backups source/file recovery authority.';
}
if (! str_contains($songchart, 'Using durable remote PostgreSQL authority; local postgres service will not be started.')) {
    $errors[] = 'Remote development mode must explicitly avoid starting local PostgreSQL through the songchart facade.';
}
if (str_contains($compose, 'DB_HOST: postgres') || str_contains($compose, 'DB_DATABASE: songchart_docker')) {
    $errors[] = 'Compose app/queue services must not override .env.docker development database authority.';
}
foreach (['SONGCHART_DEV_DATABASE_MODE=local', 'SONGCHART_DEV_DATABASE_EXPECTED_NAME=songchart_docker', 'DB_URL=', 'DB_SSLMODE=prefer'] as $needle) {
    if (! str_contains($envDockerExample, $needle)) {
        $errors[] = ".env.docker.example is missing development database authority signal: {$needle}";
    }
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
if (! preg_match('/^TEST_PGSQL_DATABASE=.+$/m', $envExample)
    || ! str_contains($envExample, 'SONGCHART_ADMIN_2FA_MODE=disabled')
    || ! str_contains($envExample, 'SONGCHART_DEV_DATABASE_MODE=local')) {
    $errors[] = '.env.example must declare isolated test database, local 2FA ergonomics and explicit development database authority defaults.';
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
