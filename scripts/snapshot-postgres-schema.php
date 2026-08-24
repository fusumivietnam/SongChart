<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$host = (string) (getenv('TEST_PGSQL_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1');
$port = (int) (getenv('TEST_PGSQL_PORT') ?: getenv('DB_PORT') ?: 5432);
$database = (string) (getenv('TEST_PGSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'songchart_test');
$username = (string) (getenv('TEST_PGSQL_USERNAME') ?: getenv('DB_USERNAME') ?: 'postgres');
$password = (string) (getenv('TEST_PGSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '');

try {
    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password, [PDO::ATTR_TIMEOUT => 2]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $columns = $pdo->query(
        'select table_name, column_name, data_type, is_nullable, character_maximum_length, ordinal_position
         from information_schema.columns
         where table_schema = current_schema()
         order by table_name, ordinal_position'
    )->fetchAll(PDO::FETCH_ASSOC);

    $indexes = $pdo->query(
        'select tablename, indexname, indexdef
         from pg_indexes
         where schemaname = current_schema()
         order by tablename, indexname'
    )->fetchAll(PDO::FETCH_ASSOC);

    $snapshot = [
        'schema_version' => 1,
        'database' => $database,
        'columns' => $columns,
        'indexes' => $indexes,
    ];

    $target = $root.'/storage/framework/postgres-schema-snapshot.json';
    if (! is_dir(dirname($target))) {
        mkdir(dirname($target), 0777, true);
    }

    file_put_contents($target, json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL);
    fwrite(STDOUT, "PostgreSQL schema snapshot written: storage/framework/postgres-schema-snapshot.json\n");
} catch (Throwable $exception) {
    fwrite(STDERR, 'PostgreSQL schema snapshot failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}
