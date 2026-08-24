<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

/** @return array<string,list<string>> */
function expectedSchema(string $root): array
{
    $tables = [];
    foreach (glob($root.'/database/migrations/*.php') ?: [] as $file) {
        $source = (string) file_get_contents($file);
        if (preg_match_all("/Schema::create\\('([^']+)'\\s*,\\s*function\\s*\\([^)]*\\)(?:\\s*:\\s*void)?\\s*\\{(.*?)\\n\\s*\\}\\);/s", $source, $creates, PREG_SET_ORDER) === 0) {
            continue;
        }
        foreach ($creates as $create) {
            $table = (string) $create[1];
            $body = (string) $create[2];
            $columns = [];
            if (preg_match('/\\$table->id\\(\\s*\\)/', $body) === 1) {
                $columns[] = 'id';
            }
            $columnPattern = <<<'REGEX'
~\$table->([A-Za-z_][A-Za-z0-9_]*)\(\s*['"]([^'"]+)['"]~
REGEX;
            if (preg_match_all($columnPattern, $body, $columnMatches, PREG_SET_ORDER) > 0) {
                foreach ($columnMatches as $columnMatch) {
                    if (! in_array($columnMatch[1], ['morphs', 'nullableMorphs'], true)) {
                        $columns[] = (string) $columnMatch[2];
                    }
                }
            }
            $morphPattern = <<<'REGEX'
~\$table->(?:morphs|nullableMorphs)\(\s*['"]([^'"]+)['"]~
REGEX;
            if (preg_match_all($morphPattern, $body, $morphMatches) > 0) {
                foreach ($morphMatches[1] as $name) {
                    $columns[] = $name.'_type';
                    $columns[] = $name.'_id';
                }
            }
            if (preg_match('/\\$table->timestamps\\(\\s*\\)/', $body) === 1) {
                $columns[] = 'created_at';
                $columns[] = 'updated_at';
            }
            $tables[$table] = array_values(array_unique($columns));
        }
    }
    ksort($tables);

    return $tables;
}

try {
    if (! extension_loaded('pdo_pgsql')) {
        throw new RuntimeException('pdo_pgsql is required for project-context runtime verification.');
    }

    $host = (string) (getenv('TEST_PGSQL_HOST') ?: getenv('DB_HOST') ?: 'postgres');
    $port = (int) (getenv('TEST_PGSQL_PORT') ?: getenv('DB_PORT') ?: 5432);
    $database = (string) (getenv('TEST_PGSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'songchart_verify_test');
    $username = (string) (getenv('TEST_PGSQL_USERNAME') ?: getenv('DB_USERNAME') ?: 'postgres');
    $password = (string) (getenv('TEST_PGSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '');

    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password, [PDO::ATTR_TIMEOUT => 2]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $rows = $pdo->query(
        'select table_name, column_name
         from information_schema.columns
         where table_schema = current_schema()
         order by table_name, ordinal_position'
    )->fetchAll(PDO::FETCH_ASSOC);

    $actual = [];
    foreach ($rows as $row) {
        $actual[(string) $row['table_name']][] = (string) $row['column_name'];
    }

    foreach (expectedSchema($root) as $table => $columns) {
        if (! array_key_exists($table, $actual)) {
            $errors[] = "Migration-owned table is missing from PostgreSQL [{$table}].";

            continue;
        }
        foreach ($columns as $column) {
            if (! in_array($column, $actual[$table], true)) {
                $errors[] = "Migration-owned column is missing from PostgreSQL [{$table}.{$column}].";
            }
        }
    }
} catch (Throwable $exception) {
    $errors[] = $exception->getMessage();
}

if ($errors !== []) {
    fwrite(STDERR, "Project context runtime drift verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Project context runtime drift verification passed.'.PHP_EOL);
