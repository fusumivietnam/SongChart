<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root.'/vendor/autoload.php';
if (! is_file($autoload)) {
    fwrite(STDERR, "Migration runtime contract failed: vendor/autoload.php is missing.\n");
    exit(1);
}
require $autoload;

try {
    $contracts = json_decode((string) file_get_contents($root.'/docs/project/stack/package-schema-contracts.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Migration runtime contract failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$host = (string) (getenv('TEST_PGSQL_HOST') ?: getenv('DB_HOST') ?: '127.0.0.1');
$port = (int) (getenv('TEST_PGSQL_PORT') ?: getenv('DB_PORT') ?: 5432);
$database = (string) (getenv('TEST_PGSQL_DATABASE') ?: getenv('DB_DATABASE') ?: 'songchart_test');
$username = (string) (getenv('TEST_PGSQL_USERNAME') ?: getenv('DB_USERNAME') ?: 'postgres');
$password = (string) (getenv('TEST_PGSQL_PASSWORD') ?: getenv('DB_PASSWORD') ?: '');

$errors = [];
try {
    $pdo = new PDO("pgsql:host={$host};port={$port};dbname={$database}", $username, $password, [PDO::ATTR_TIMEOUT => 2]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach (($contracts['contracts'] ?? []) as $package => $contract) {
        $table = (string) ($contract['table'] ?? '');
        $statement = $pdo->prepare(
            'select column_name, data_type, is_nullable, character_maximum_length
             from information_schema.columns
             where table_schema = current_schema() and table_name = :table'
        );
        $statement->execute(['table' => $table]);
        $actual = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $actual[$row['column_name']] = $row;
        }

        foreach (($contract['required_columns'] ?? []) as $column => $definition) {
            if (! isset($actual[$column])) {
                $errors[] = "{$package}.{$table} missing runtime column [{$column}].";

                continue;
            }

            if (is_array($definition) && isset($definition['kind'])) {
                $expectedTypes = match ($definition['kind']) {
                    'bigint' => ['bigint'],
                    'string' => ['character varying'],
                    'text' => ['text'],
                    'char' => ['character'],
                    'json' => ['json', 'jsonb'],
                    'uuid' => ['uuid'],
                    default => [],
                };
                if ($expectedTypes !== [] && ! in_array((string) ($actual[$column]['data_type'] ?? ''), $expectedTypes, true)) {
                    $errors[] = "{$package}.{$table}.{$column} type mismatch.";
                }
            }

            if (is_array($definition) && isset($definition['nullable'])) {
                $expected = $definition['nullable'] ? 'YES' : 'NO';
                if (($actual[$column]['is_nullable'] ?? null) !== $expected) {
                    $errors[] = "{$package}.{$table}.{$column} nullability mismatch.";
                }
            }

            if (is_array($definition) && isset($definition['length'])) {
                if ((int) ($actual[$column]['character_maximum_length'] ?? 0) !== (int) $definition['length']) {
                    $errors[] = "{$package}.{$table}.{$column} length mismatch.";
                }
            }
        }
    }
} catch (Throwable $exception) {
    $errors[] = 'Unable to inspect PostgreSQL migration runtime contract: '.$exception->getMessage();
}

if ($errors !== []) {
    fwrite(STDERR, "Migration runtime contract verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Migration runtime contract verification passed on {$database}.\n");
