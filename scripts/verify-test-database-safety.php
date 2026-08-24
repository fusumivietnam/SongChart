<?php

declare(strict_types=1);

use App\Support\Testing\TestDatabaseSafetyPolicy;

$root = dirname(__DIR__);
$autoload = $root.'/vendor/autoload.php';
if (! is_file($autoload)) {
    fwrite(STDERR, "Test database safety verification failed:\n- vendor/autoload.php is missing.\n");
    exit(1);
}
require $autoload;

$dotenv = [];
if (is_file($root.'/.env')) {
    foreach (file($root.'/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $dotenv[$key] = trim($value, "\"'");
    }
}

$get = static function (string $testKey, string $dbKey, string $default) use ($dotenv): string {
    $value = getenv($testKey);
    if ($value === false || $value === '') {
        $value = $dotenv[$testKey] ?? null;
    }
    if (($value === '' || $value === null) && $dbKey !== '') {
        $fallback = getenv($dbKey);
        $value = $fallback === false ? null : $fallback;
    }
    if (($value === '' || $value === null) && $dbKey !== '' && isset($dotenv[$dbKey])) {
        $value = $dotenv[$dbKey];
    }

    return ($value === '' || $value === null) ? $default : $value;
};

$developmentDatabase = (string) (getenv('SONGCHART_DEVELOPMENT_DATABASE') ?: ($dotenv['DB_DATABASE'] ?? (getenv('DB_DATABASE') ?: 'songchart')));
$testDatabase = $get('TEST_PGSQL_DATABASE', '', 'songchart_test');
$policy = new TestDatabaseSafetyPolicy;
$errors = $policy->validateNames($developmentDatabase, $testDatabase);

if (in_array('--names-only', $argv, true)) {
    if ($errors !== []) {
        fwrite(STDERR, "Test database safety verification failed:\n- ".implode("\n- ", $errors)."\n");
        exit(1);
    }
    fwrite(STDOUT, "Test database name isolation passed: development={$developmentDatabase}; testing={$testDatabase}.\n");
    exit(0);
}

$host = $get('TEST_PGSQL_HOST', 'DB_HOST', '127.0.0.1');
$port = (int) $get('TEST_PGSQL_PORT', 'DB_PORT', '5432');
$username = $get('TEST_PGSQL_USERNAME', 'DB_USERNAME', 'postgres');
$password = $get('TEST_PGSQL_PASSWORD', 'DB_PASSWORD', '');

if ($errors === []) {
    try {
        $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$testDatabase}", $username, $password, [PDO::ATTR_TIMEOUT => 2]);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $markerExists = (int) $pdo->query("select count(*) from information_schema.tables where table_schema = current_schema() and table_name = 'songchart_environment_guard'")->fetchColumn() > 0;
        if ($markerExists) {
            $statement = $pdo->query("select environment, database_role from songchart_environment_guard where marker = 'songchart' limit 1");
            $marker = $statement->fetch(PDO::FETCH_ASSOC);
            if (! is_array($marker) || ($marker['database_role'] ?? null) !== 'testing') {
                $errors[] = sprintf('Database marker for "%s" is not role=testing.', $testDatabase);
            }
        } else {
            $migrationsExists = (int) $pdo->query("select count(*) from information_schema.tables where table_schema = current_schema() and table_name = 'migrations'")->fetchColumn() > 0;
            if ($migrationsExists) {
                $statement = $pdo->prepare('select exists(select 1 from migrations where migration = :migration)');
                $statement->execute(['migration' => '2026_08_09_000100_create_songchart_environment_guard_table']);
                if (in_array($statement->fetchColumn(), [true, 1, '1', 't', 'true'], true)) {
                    $errors[] = sprintf('Database "%s" recorded the environment-guard migration but the marker table is missing.', $testDatabase);
                }
            }
        }
    } catch (Throwable $exception) {
        $errors[] = 'Unable to verify PostgreSQL test database marker: '.$exception->getMessage();
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Test database safety verification failed:\n- ".implode("\n- ", $errors)."\n");
    fwrite(STDERR, "Refusing to run destructive PostgreSQL tests. Development data was not touched.\n");
    exit(1);
}

fwrite(STDOUT, "Test database safety verification passed.\n");
fwrite(STDOUT, "- Development database: {$developmentDatabase}\n");
fwrite(STDOUT, "- PostgreSQL test database: {$testDatabase}\n");
fwrite(STDOUT, "- Isolation: PASS\n");
