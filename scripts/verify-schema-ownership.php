<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

try {
    $ownership = json_decode((string) file_get_contents($root.'/docs/project/domain/schema-ownership.json'), true, flags: JSON_THROW_ON_ERROR);
    $operational = json_decode((string) file_get_contents($root.'/docs/project/domain/operational-contracts.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Schema ownership verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$owners = is_array($ownership['tables'] ?? null) ? $ownership['tables'] : [];
$framework = is_array($ownership['framework_tables'] ?? null) ? $ownership['framework_tables'] : [];
$migrationTables = [];
foreach (glob($root.'/database/migrations/*.php') ?: [] as $migration) {
    $source = (string) file_get_contents($migration);
    if (preg_match_all("/Schema::create\\('([^']+)'/", $source, $matches) > 0) {
        foreach ($matches[1] as $table) {
            $migrationTables[(string) $table] = basename($migration);
        }
    }
}

foreach ($migrationTables as $table => $migration) {
    if (! array_key_exists($table, $owners) && ! in_array($table, $framework, true)) {
        $errors[] = "Migration-owned table {$table} ({$migration}) has no schema owner.";
    }
}
foreach ($owners as $table => $owner) {
    if (! is_string($owner) || $owner === '') {
        $errors[] = "Table {$table} has an invalid owner.";
    }
    if (! array_key_exists($table, $migrationTables)) {
        $errors[] = "Schema ownership declares {$table}, but no migration creates it.";
    }
}

$surfaces = is_array($operational['surfaces'] ?? null) ? $operational['surfaces'] : [];
foreach ($surfaces as $surface => $definition) {
    if (! is_array($definition) || ! is_string($definition['table'] ?? null)) {
        continue;
    }
    $table = $definition['table'];
    if (($owners[$table] ?? null) !== 'operational-contracts') {
        $errors[] = "Operational surface {$surface} must own table {$table} through operational-contracts.";
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Schema ownership verification failed:\n- ".implode("\n- ", array_unique($errors))."\n");
    exit(1);
}

fwrite(STDOUT, 'Schema ownership verification passed.'.PHP_EOL);
