<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

try {
    $contracts = json_decode((string) file_get_contents($root.'/docs/project/domain/model-schema-contracts.json'), true, flags: JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, 'Model/schema verification failed: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$migrationSources = [];
foreach (glob($root.'/database/migrations/*.php') ?: [] as $migration) {
    $source = (string) file_get_contents($migration);
    if (preg_match_all("/Schema::(?:create|table)\\('([^']+)'/", $source, $matches) > 0) {
        foreach (array_unique($matches[1]) as $table) {
            $migrationSources[(string) $table] = ($migrationSources[(string) $table] ?? '')."\n".$source;
        }
    }
}

foreach (($contracts['contracts'] ?? []) as $class => $contract) {
    if (! is_array($contract)) {
        $errors[] = "Invalid model schema contract [{$class}].";

        continue;
    }

    $file = $root.'/'.(string) ($contract['file'] ?? '');
    if (! is_file($file)) {
        $errors[] = "Model contract source is missing [{$class}].";

        continue;
    }

    $source = (string) file_get_contents($file);
    $table = (string) ($contract['table'] ?? '');
    $migration = $migrationSources[$table] ?? '';
    if ($migration === '') {
        $errors[] = "No migration source owns model table [{$class}:{$table}].";

        continue;
    }

    foreach (($contract['required_columns'] ?? []) as $column) {
        $column = (string) $column;
        if (! preg_match("/\\\$table->[^;\\n]*\\(['\"]".preg_quote($column, '/')."['\"]/", $migration)) {
            $errors[] = "Model contract {$class} requires missing database column [{$column}].";
        }
    }

    foreach (($contract['required_casts'] ?? []) as $cast) {
        if (! preg_match("/['\"]".preg_quote((string) $cast, '/')."['\"]\\s*=>/", $source)) {
            $errors[] = "Model contract {$class} requires cast [{$cast}].";
        }
    }

    foreach (($contract['forbidden_property_references'] ?? []) as $property => $paths) {
        foreach ((array) $paths as $relativePath) {
            $absolute = $root.'/'.(string) $relativePath;
            $candidates = is_dir($absolute)
                ? new RecursiveIteratorIterator(new RecursiveDirectoryIterator($absolute))
                : [$absolute];

            foreach ($candidates as $candidate) {
                $pathname = is_string($candidate) ? $candidate : $candidate->getPathname();
                if (! is_file($pathname) || pathinfo($pathname, PATHINFO_EXTENSION) !== 'php') {
                    continue;
                }

                if (str_contains((string) file_get_contents($pathname), '->'.(string) $property)) {
                    $errors[] = "Forbidden legacy property reference for {$class}::\${$property} in ".str_replace($root.'/', '', $pathname).'.';
                }
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Model/schema contract verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Model/schema contract verification passed.'.PHP_EOL);
