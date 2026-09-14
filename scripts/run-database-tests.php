<?php

declare(strict_types=1);

$lane = $argv[1] ?? '';
if (! in_array($lane, ['sqlite', 'postgres'], true)) {
    fwrite(STDERR, 'Usage: php scripts/run-database-tests.php [sqlite|postgres]'.PHP_EOL);
    exit(2);
}

$prepareSchema = in_array('--prepare-schema', $argv, true);
$testArguments = array_values(array_filter(
    array_slice($argv, 2),
    static fn (string $argument): bool => $argument !== '--prepare-schema',
));

$root = dirname(__DIR__);
$diagnosticDirectory = $root.'/storage/logs';
if (! is_dir($diagnosticDirectory)) {
    @mkdir($diagnosticDirectory, 0777, true);
}
$resultPath = $diagnosticDirectory.'/postgres-test-result.json';

$writeResult = static function (
    string $status,
    string $phase,
    ?string $failureClass = null,
    ?string $failedTest = null,
    ?string $evidencePath = null,
) use ($resultPath, $lane): void {
    if ($lane !== 'postgres') {
        return;
    }

    $payload = [
        'schema_version' => 1,
        'lane' => 'postgres',
        'status' => $status,
        'phase' => $phase,
        'failure_class' => $failureClass,
        'failed_test' => $failedTest,
        'evidence_path' => $evidencePath,
    ];

    @file_put_contents(
        $resultPath,
        json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
    );
};

$env = [];
foreach (array_merge($_SERVER, $_ENV) as $key => $value) {
    if (is_scalar($value) || $value === null) {
        $env[(string) $key] = (string) ($value ?? '');
    }
}
$dotenv = [];
$dotenvPath = $root.'/.env';
if (is_file($dotenvPath)) {
    foreach (file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }
        [$key, $value] = array_map('trim', explode('=', $line, 2));
        $dotenv[$key] = trim($value, "\"'");
    }
}
$env['APP_ENV'] = 'testing';
$env['SONGCHART_DEMO_SEARCH'] = 'true';

if ($lane === 'sqlite') {
    $env['DB_CONNECTION'] = 'sqlite';
    $env['DB_DATABASE'] = ':memory:';
} else {
    $map = [
        'DB_HOST' => ['TEST_PGSQL_HOST', 'DB_HOST', '127.0.0.1'],
        'DB_PORT' => ['TEST_PGSQL_PORT', 'DB_PORT', '5432'],
        'DB_DATABASE' => ['TEST_PGSQL_DATABASE', '', 'songchart_test'],
        'DB_USERNAME' => ['TEST_PGSQL_USERNAME', 'DB_USERNAME', 'songchart_admin'],
        'DB_PASSWORD' => ['TEST_PGSQL_PASSWORD', 'DB_PASSWORD', ''],
    ];
    $env['SONGCHART_DEVELOPMENT_DATABASE'] = (string) (
        getenv('SONGCHART_DEVELOPMENT_DATABASE')
        ?: ($dotenv['DB_DATABASE'] ?? 'songchart')
    );
    $env['DB_CONNECTION'] = 'pgsql';
    foreach ($map as $target => [$testKey, $dbKey, $default]) {
        $value = getenv($testKey);
        if (($value === false || $value === '') && $dbKey !== '') {
            $value = getenv($dbKey);
        }
        if (($value === false || $value === '') && $dbKey !== '' && array_key_exists($dbKey, $dotenv)) {
            $value = $dotenv[$dbKey];
        }
        $env[$target] = ($value === false || $value === '') ? $default : $value;
    }
}

if ($lane === 'postgres') {
    $writeResult('running', 'safety-precheck');
    $safety = proc_open([PHP_BINARY, 'scripts/verify-test-database-safety.php'], [STDIN, STDOUT, STDERR], $pipes, $root, $env);
    if (! is_resource($safety) || proc_close($safety) !== 0) {
        $writeResult('failed', 'safety-precheck', 'database-safety');
        fwrite(STDERR, 'PostgreSQL test database safety guard refused the test lane.'.PHP_EOL);
        exit(1);
    }
}

if ($prepareSchema) {
    if ($lane !== 'postgres') {
        fwrite(STDERR, '--prepare-schema is supported only for the PostgreSQL test lane.'.PHP_EOL);
        exit(2);
    }

    $writeResult('running', 'migration');
    fwrite(STDOUT, 'Preparing isolated PostgreSQL test schema with migrate:fresh...'.PHP_EOL);
    $migration = proc_open(
        [PHP_BINARY, 'artisan', 'migrate:fresh', '--force', '--ansi'],
        [STDIN, STDOUT, STDERR],
        $pipes,
        $root,
        $env,
    );

    if (! is_resource($migration) || proc_close($migration) !== 0) {
        $writeResult('failed', 'migration', 'migration');
        fwrite(STDERR, 'Unable to prepare the isolated PostgreSQL test schema.'.PHP_EOL);
        exit(1);
    }

    $writeResult('running', 'safety-postcheck');
    $postMigrationSafety = proc_open(
        [PHP_BINARY, 'scripts/verify-test-database-safety.php'],
        [STDIN, STDOUT, STDERR],
        $pipes,
        $root,
        $env,
    );

    if (! is_resource($postMigrationSafety) || proc_close($postMigrationSafety) !== 0) {
        $writeResult('failed', 'safety-postcheck', 'database-safety');
        fwrite(STDERR, 'Post-migration PostgreSQL test database safety verification failed.'.PHP_EOL);
        exit(1);
    }
}

$writeResult('running', 'test');
$command = array_merge([PHP_BINARY, 'artisan', 'test', '--ansi', '--display-warnings'], $testArguments);
$descriptors = [
    0 => STDIN,
    1 => ['pipe', 'w'],
    2 => ['pipe', 'w'],
];
$process = proc_open($command, $descriptors, $pipes, $root, $env);
if (! is_resource($process)) {
    $writeResult('failed', 'test', 'test-runner');
    fwrite(STDERR, 'Unable to start Laravel test process.'.PHP_EOL);
    exit(1);
}

stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);

$captured = '';
while (true) {
    $status = proc_get_status($process);
    $read = [];

    foreach ([1, 2] as $index) {
        if (! feof($pipes[$index])) {
            $read[] = $pipes[$index];
        }
    }

    if ($read !== []) {
        $write = null;
        $except = null;
        $selected = stream_select($read, $write, $except, 0, 200000);
        if ($selected !== false && $selected > 0) {
            foreach ($read as $stream) {
                $chunk = stream_get_contents($stream);
                if ($chunk === false || $chunk === '') {
                    continue;
                }

                $captured .= $chunk;
                if ($stream === $pipes[2]) {
                    fwrite(STDERR, $chunk);
                } else {
                    fwrite(STDOUT, $chunk);
                }
            }
        }
    }

    if (! ($status['running'] ?? false)) {
        foreach ([1, 2] as $index) {
            $chunk = stream_get_contents($pipes[$index]);
            if ($chunk !== false && $chunk !== '') {
                $captured .= $chunk;
                if ($index === 2) {
                    fwrite(STDERR, $chunk);
                } else {
                    fwrite(STDOUT, $chunk);
                }
            }
            fclose($pipes[$index]);
        }
        break;
    }
}

$exitCode = proc_close($process);
$redacted = preg_replace(
    '/(DB_PASSWORD|TEST_PGSQL_PASSWORD|API[_ -]?KEY|TOKEN|SECRET|AUTHORIZATION|password)(\s*[:=]\s*)[^\s]+/i',
    '$1$2[REDACTED]',
    $captured,
) ?? $captured;
@file_put_contents($diagnosticDirectory.'/focused-test-last.log', $redacted);

$failureEvidenceTail = null;
$failureEvidencePath = null;
$failedTest = null;
$failureClass = null;

if ($exitCode !== 0) {
    $diagnosticPath = $diagnosticDirectory.'/postgres-test-last-failure.log';
    @file_put_contents($diagnosticPath, $redacted);

    $plain = preg_replace('/\x1B\[[0-?]*[ -\/]*[@-~]/', '', $redacted) ?? $redacted;
    $lines = preg_split('/\R/', $plain) ?: [];
    $failureEvidenceTail = implode(PHP_EOL, array_slice($lines, -200));
    $failureEvidencePath = 'storage/logs/postgres-test-last-failure.log';

    if (preg_match('/\b(?:FAIL|FAILED)\s+(Tests\\\\[^\r\n]+)/', $plain, $matches) === 1) {
        $failedTest = trim($matches[1]);
    }

    if ($failedTest !== null || str_contains($plain, 'Failed asserting') || str_contains($plain, 'ExpectationFailedException')) {
        $failureClass = 'assertion';
    } elseif (str_contains($plain, 'SQLSTATE')) {
        $failureClass = 'database-error';
    } else {
        $failureClass = 'test-process';
    }

    $writeResult('failed', 'test', $failureClass, $failedTest, $failureEvidencePath);
} else {
    $writeResult('passed', 'test');
}

if ($exitCode !== 0 && $lane === 'postgres') {
    fwrite(STDERR, PHP_EOL.'[SongChart DB diagnostic] Test lane failed; collecting non-secret PostgreSQL state...'.PHP_EOL);
    fwrite(STDERR, '[SongChart DB diagnostic] APP_ENV='.($env['APP_ENV'] ?? 'unknown').PHP_EOL);
    fwrite(STDERR, '[SongChart DB diagnostic] Connection=pgsql'.PHP_EOL);
    fwrite(STDERR, '[SongChart DB diagnostic] Host='.($env['DB_HOST'] ?? 'unknown').':'.($env['DB_PORT'] ?? 'unknown').PHP_EOL);
    fwrite(STDERR, '[SongChart DB diagnostic] Database='.($env['DB_DATABASE'] ?? 'unknown').PHP_EOL);

    try {
        $pdo = new PDO(
            'pgsql:host='.$env['DB_HOST'].';port='.$env['DB_PORT'].';dbname='.$env['DB_DATABASE'],
            $env['DB_USERNAME'],
            $env['DB_PASSWORD'],
            [PDO::ATTR_TIMEOUT => 2],
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $version = (string) $pdo->query('select version()')->fetchColumn();
        fwrite(STDERR, "[SongChart DB diagnostic] PostgreSQL={$version}".PHP_EOL);

        $migrationTableExists = (bool) $pdo
            ->query("select to_regclass(current_schema() || '.migrations') is not null")
            ->fetchColumn();
        if ($migrationTableExists) {
            $migrationCount = (int) $pdo->query('select count(*) from migrations')->fetchColumn();
            fwrite(STDERR, "[SongChart DB diagnostic] Migrations={$migrationCount}".PHP_EOL);
        } else {
            fwrite(STDERR, '[SongChart DB diagnostic] Migrations=table-missing'.PHP_EOL);
        }

        $guardExists = (bool) $pdo
            ->query("select to_regclass(current_schema() || '.songchart_environment_guard') is not null")
            ->fetchColumn();
        if ($guardExists) {
            $guard = $pdo
                ->query("select environment, database_role from songchart_environment_guard where marker = 'songchart' limit 1")
                ->fetch(PDO::FETCH_ASSOC);

            if (is_array($guard)) {
                fwrite(
                    STDERR,
                    '[SongChart DB diagnostic] Marker='
                    .($guard['environment'] ?? 'unknown')
                    .'/'
                    .($guard['database_role'] ?? 'unknown')
                    .PHP_EOL,
                );
            }
        } else {
            fwrite(STDERR, '[SongChart DB diagnostic] Marker=table-missing'.PHP_EOL);
        }
    } catch (Throwable $exception) {
        fwrite(STDERR, '[SongChart DB diagnostic] Unable to inspect database state: '.$exception->getMessage().PHP_EOL);
    }
}

if ($exitCode !== 0) {
    fwrite(STDERR, PHP_EOL.'[SongChart test evidence] === COPY FROM HERE ==='.PHP_EOL);
    fwrite(STDERR, '[SongChart test evidence] Exact tail from failing Laravel test process:'.PHP_EOL);
    fwrite(STDERR, ($failureEvidenceTail ?? '[captured output unavailable]').PHP_EOL);
    fwrite(
        STDERR,
        '[SongChart test evidence] Full captured failure: '.($failureEvidencePath ?? 'storage/logs/postgres-test-last-failure.log').PHP_EOL,
    );
    fwrite(STDERR, '[SongChart test evidence] Machine result: storage/logs/postgres-test-result.json'.PHP_EOL);
    fwrite(STDERR, '[SongChart test evidence] === END COPY ==='.PHP_EOL);
}

exit($exitCode);
