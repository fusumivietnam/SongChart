<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$path = $root.'/docs/project/domain/operational-contracts.json';

try {
    $registry = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, "Operational contract verification failed:\n- Unable to decode registry: {$exception->getMessage()}\n");
    exit(1);
}

if (! is_array($registry) || ($registry['schema_version'] ?? null) !== 1) {
    $errors[] = 'operational-contracts.json must use schema_version 1.';
}

$migrationCorpus = '';
foreach (glob($root.'/database/migrations/*.php') ?: [] as $migration) {
    $migrationCorpus .= "\n".(string) file_get_contents($migration);
}

foreach (($registry['surfaces'] ?? []) as $key => $surface) {
    if (! is_string($key) || ! is_array($surface)) {
        $errors[] = 'Every operational surface must be a keyed object.';

        continue;
    }

    $table = $surface['table'] ?? null;
    $fields = $surface['fields'] ?? null;
    if (! is_string($table) || $table === '' || ! is_array($fields) || $fields === []) {
        $errors[] = "Operational surface [{$key}] must declare table and fields.";

        continue;
    }

    preg_match_all(
        "/Schema::(?:create|table)\\('".preg_quote($table, '/')."'.*?^\\s*}\\);/ms",
        $migrationCorpus,
        $blocks,
    );
    $tableSource = implode("\n", $blocks[0] ?? []);
    if ($tableSource === '') {
        $errors[] = "Operational surface [{$key}] references table [{$table}] not found in migrations.";

        continue;
    }

    foreach ($fields as $field) {
        if (! is_string($field) || $field === '') {
            $errors[] = "Operational surface [{$key}] contains an invalid field declaration.";

            continue;
        }

        $declared = preg_match('/\\$table->\\w+\\(\\s*[\'\"]'.preg_quote($field, '/').'[\'\"]/', $tableSource) === 1;
        if (! $declared && in_array($field, ['created_at', 'updated_at'], true) && str_contains($tableSource, '$table->timestamps()')) {
            $declared = true;
        }
        if (! $declared) {
            $errors[] = "Operational surface [{$key}] field [{$field}] is not declared for table [{$table}] in migrations.";
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Operational contract verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, "Operational contract verification passed.\n");
