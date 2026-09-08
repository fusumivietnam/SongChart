<?php

declare(strict_types=1);

$dotenv = [];
$root = dirname(__DIR__);

try {
    $stack = json_decode(
        (string) file_get_contents($root.'/docs/project/stack/stack-manifest.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to resolve PostgreSQL release authority: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$requiredMajor = $stack['policies']['release_database_major'] ?? null;
if (! is_int($requiredMajor) || $requiredMajor < 14) {
    fwrite(STDERR, 'Stack authority does not declare a supported PostgreSQL release major target.'.PHP_EOL);
    exit(1);
}

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

    if (($value === false || $value === '') && $dbKey !== '') {
        $value = getenv($dbKey);
    }

    if (($value === false || $value === '') && $dbKey !== '' && isset($dotenv[$dbKey])) {
        $value = $dotenv[$dbKey];
    }

    return ($value === false || $value === '') ? $default : (string) $value;
};

$host = $get('TEST_PGSQL_HOST', 'DB_HOST', '127.0.0.1');
$port = (int) $get('TEST_PGSQL_PORT', 'DB_PORT', '5432');
$database = $get('TEST_PGSQL_DATABASE', 'DB_DATABASE', 'songchart_test');
$username = $get('TEST_PGSQL_USERNAME', 'DB_USERNAME', 'postgres');
$password = $get('TEST_PGSQL_PASSWORD', 'DB_PASSWORD', '');

try {
    $pdo = new PDO(
        "pgsql:host={$host};port={$port};dbname={$database}",
        $username,
        $password,
        [PDO::ATTR_TIMEOUT => 3],
    );
    $versionStatement = $pdo->query("select current_setting('server_version_num')");
    $descriptionStatement = $pdo->query('select version()');

    if ($versionStatement === false || $descriptionStatement === false) {
        throw new RuntimeException('PostgreSQL version query failed.');
    }

    $versionNum = (int) $versionStatement->fetchColumn();
    $version = (string) $descriptionStatement->fetchColumn();
} catch (Throwable $exception) {
    fwrite(STDERR, 'Unable to verify PostgreSQL major version: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$major = intdiv($versionNum, 10000);

if ($major !== $requiredMajor) {
    fwrite(STDERR, "PostgreSQL {$requiredMajor} is the current release target; connected major is {$major} ({$version}).".PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "PostgreSQL {$requiredMajor} current release target verified: {$version}".PHP_EOL);
