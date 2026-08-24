<?php

declare(strict_types=1);

use App\Support\Engineering\LaravelMigrationColumnExtractor;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/LaravelMigrationColumnExtractor.php';

$errors = [];

$contracts = json_decode(
    (string) file_get_contents($root.'/docs/project/stack/package-schema-contracts.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$activity = $contracts['contracts']['spatie/laravel-activitylog'] ?? null;

if (! is_array($activity)) {
    $errors[] = 'Spatie Activitylog package schema contract is missing.';
} else {
    $matches = glob($root.'/vendor/spatie/laravel-activitylog/database/migrations/*activity*') ?: [];
    if ($matches === []) {
        $errors[] = 'Unable to locate the installed Spatie Activitylog upstream migration.';
    } else {
        sort($matches);
        $migration = $matches[0];
        $source = (string) file_get_contents($migration);
        $extractor = new LaravelMigrationColumnExtractor;
        $upstreamColumns = $extractor->extract($source);

        $required = $activity['upstream_contract']['required_columns'] ?? [];
        if (! is_array($required)) {
            $errors[] = 'Spatie upstream required-columns contract must be an array.';
            $required = [];
        }

        foreach ($required as $column) {
            if (! is_string($column) || ! in_array($column, $upstreamColumns, true)) {
                $errors[] = "Installed upstream Activitylog migration is missing semantic v5 column [{$column}].";
            }
        }

        $extensions = $activity['songchart_extensions'] ?? [];
        if (! is_array($extensions)) {
            $errors[] = 'SongChart package extensions contract must be an object.';
            $extensions = [];
        }

        foreach (array_keys($extensions) as $extension) {
            if (in_array($extension, $required, true)) {
                $errors[] = "SongChart-only package extension [{$extension}] must not also be classified as upstream-required.";
            }
        }

        $snapshot = [
            'schema_version' => 2,
            'package' => 'spatie/laravel-activitylog',
            'installed_migration' => str_replace('\\', '/', substr($migration, strlen($root) + 1)),
            'upstream_migration_sha256' => hash_file('sha256', $migration),
            'upstream_semantic_columns' => $upstreamColumns,
            'upstream_required_columns' => array_values(array_filter($required, 'is_string')),
            'songchart_allowed_adaptations' => $activity['allowed_adaptations'] ?? [],
            'songchart_extensions' => $extensions,
        ];

        $target = $root.'/storage/framework/package-upstream-adaptations.json';
        if (! is_dir(dirname($target))) {
            mkdir(dirname($target), 0777, true);
        }

        file_put_contents(
            $target,
            json_encode($snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
        );
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Package upstream adaptation verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, "Package upstream adaptation verification passed.\n");
