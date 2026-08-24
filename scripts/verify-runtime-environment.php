<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$release = in_array('--release', $argv, true);
$errors = [];
$notes = [];

foreach (['vendor/autoload.php', 'artisan'] as $relative) {
    if (! is_file($root.'/'.$relative)) {
        $errors[] = "Missing runtime prerequisite: {$relative}";
    }
}
if ($release) {
    foreach (['composer.lock', 'package-lock.json'] as $relative) {
        if (! is_file($root.'/'.$relative)) {
            $errors[] = "Missing release prerequisite: {$relative}";
        }
    }
}
if (! extension_loaded('pdo_pgsql')) {
    $errors[] = 'PHP extension pdo_pgsql is not loaded.';
}

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
        $value = getenv($dbKey);
    }
    if (($value === false || $value === '') && isset($dotenv[$dbKey])) {
        $value = $dotenv[$dbKey];
    }

    return ($value === false || $value === '') ? $default : (string) $value;
};
$host = $get('TEST_PGSQL_HOST', 'DB_HOST', '127.0.0.1');
$port = (int) $get('TEST_PGSQL_PORT', 'DB_PORT', '5432');
$database = $get('TEST_PGSQL_DATABASE', '', 'songchart_test');
$username = $get('TEST_PGSQL_USERNAME', 'DB_USERNAME', 'postgres');
$password = $get('TEST_PGSQL_PASSWORD', 'DB_PASSWORD', '');

$errno = 0;
$errstr = '';
$socket = @fsockopen($host, $port, $errno, $errstr, 1.0);
if (! is_resource($socket)) {
    $errors[] = "PostgreSQL TCP unavailable at {$host}:{$port} ({$errno}: {$errstr}). Start PostgreSQL before runtime tests.";
} else {
    fclose($socket);
    try {
        $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password, [PDO::ATTR_TIMEOUT => 2]);
        $pdo->query('select 1');
        $versionStatement = $pdo->query("select current_setting('server_version_num')");

        if ($versionStatement === false) {
            throw new RuntimeException('PostgreSQL version query failed.');
        }

        $versionNum = (int) $versionStatement->fetchColumn();
        $major = intdiv($versionNum, 10000);

        if ($major !== 18) {
            $errors[] = "PostgreSQL 18 is required; connected major is {$major}.";
        } else {
            $notes[] = "PostgreSQL 18 connection OK: {$host}:{$port}/{$database}";
        }
    } catch (Throwable $exception) {
        $errors[] = 'PostgreSQL authentication/database preflight failed: '.$exception->getMessage();
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Runtime environment verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}
fwrite(STDOUT, "Runtime environment verification passed.\n");
foreach ($notes as $note) {
    fwrite(STDOUT, "- {$note}\n");
}
