<?php

declare(strict_types=1);

$root = dirname(__DIR__);

$env = [];
foreach (array_merge($_SERVER, $_ENV) as $key => $value) {
    if (is_scalar($value) || $value === null) {
        $env[(string) $key] = (string) ($value ?? '');
    }
}
$env['APP_ENV'] = 'testing';
$env['DB_CONNECTION'] = 'pgsql';

$map = [
    'DB_HOST' => ['TEST_PGSQL_HOST', 'DB_HOST', '127.0.0.1'],
    'DB_PORT' => ['TEST_PGSQL_PORT', 'DB_PORT', '5432'],
    'DB_DATABASE' => ['TEST_PGSQL_DATABASE', 'DB_DATABASE', 'songchart_test'],
    'DB_USERNAME' => ['TEST_PGSQL_USERNAME', 'DB_USERNAME', 'songchart_admin'],
    'DB_PASSWORD' => ['TEST_PGSQL_PASSWORD', 'DB_PASSWORD', ''],
];
foreach ($map as $target => [$testKey, $dbKey, $default]) {
    $value = getenv($testKey);
    if ($value === false || $value === '') {
        $value = getenv($dbKey);
    }
    $env[$target] = ($value === false || $value === '') ? $default : (string) $value;
}

$run = static function (array $command, array $environment) use ($root): int {
    $process = proc_open($command, [STDIN, STDOUT, STDERR], $pipes, $root, $environment);

    return is_resource($process) ? proc_close($process) : 1;
};

if ($run([PHP_BINARY, 'scripts/verify-test-database-safety.php'], $env) !== 0) {
    fwrite(STDERR, "Migration upgrade lane refused an unsafe database.\n");
    exit(1);
}

if ($run([PHP_BINARY, 'artisan', 'migrate:fresh', '--force', '--ansi'], $env) !== 0) {
    fwrite(STDERR, "Unable to materialize the current schema before preparing the previous-release fixture.\n");
    exit(1);
}

try {
    $pdo = new PDO(
        'pgsql:host='.$env['DB_HOST'].';port='.$env['DB_PORT'].';dbname='.$env['DB_DATABASE'],
        $env['DB_USERNAME'],
        $env['DB_PASSWORD'],
        [PDO::ATTR_TIMEOUT => 3],
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Rewind only the Stage 16.5.6 corrective so the database matches the
    // supported pre-correction production shape: historical create migration
    // already recorded, attribute_changes absent, forward migration pending.
    $pdo->exec('alter table activity_log drop column if exists attribute_changes');
    $delete = $pdo->prepare('delete from migrations where migration = :migration');
    $delete->execute(['migration' => '2026_08_16_000100_add_attribute_changes_to_activity_log_if_missing']);

    $columnBefore = (bool) $pdo->query(
        "select exists (
            select 1 from information_schema.columns
            where table_schema = current_schema()
              and table_name = 'activity_log'
              and column_name = 'attribute_changes'
        )"
    )->fetchColumn();
    $historicalRecorded = (bool) $pdo->query(
        "select exists (
            select 1 from migrations
            where migration = '2026_08_13_000100_create_activity_log_table'
        )"
    )->fetchColumn();

    if ($columnBefore || ! $historicalRecorded) {
        fwrite(STDERR, "Previous-release migration fixture could not be established.\n");
        exit(1);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to prepare migration upgrade fixture: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Previous-release PostgreSQL schema fixture prepared; running forward migrations...\n");
if ($run([PHP_BINARY, 'artisan', 'migrate', '--force', '--ansi'], $env) !== 0) {
    fwrite(STDERR, "Forward migration upgrade failed.\n");
    exit(1);
}

try {
    $columnAfter = (bool) $pdo->query(
        "select exists (
            select 1 from information_schema.columns
            where table_schema = current_schema()
              and table_name = 'activity_log'
              and column_name = 'attribute_changes'
              and is_nullable = 'YES'
              and data_type in ('json', 'jsonb')
        )"
    )->fetchColumn();
    $forwardRecorded = (bool) $pdo->query(
        "select exists (
            select 1 from migrations
            where migration = '2026_08_16_000100_add_attribute_changes_to_activity_log_if_missing'
        )"
    )->fetchColumn();

    if (! $columnAfter || ! $forwardRecorded) {
        fwrite(STDERR, "Migration upgrade did not materialize the expected current schema.\n");
        exit(1);
    }
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to verify upgraded schema: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

if ($run([PHP_BINARY, 'artisan', 'migrate', '--force', '--ansi'], $env) !== 0) {
    fwrite(STDERR, "Idempotent second migration pass failed.\n");
    exit(1);
}

fwrite(STDOUT, "Migration upgrade verification passed: supported previous-release schema upgraded forward-only.\n");
