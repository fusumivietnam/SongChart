<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$signals = [
    '::query()',
    '::factory()',
    'DB::',
    '->save()',
    '->create(',
    '->delete()',
    '->update(',
    'insert(',
];
$isolation = ['RefreshDatabase', 'DatabaseTransactions', 'DatabaseMigrations'];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/tests/Feature'));
foreach ($iterator as $file) {
    if (! $file->isFile() || $file->getExtension() !== 'php') {
        continue;
    }

    $source = (string) file_get_contents($file->getPathname());
    $touchesDatabase = false;
    foreach ($signals as $signal) {
        if (str_contains($source, $signal)) {
            $touchesDatabase = true;
            break;
        }
    }

    if (! $touchesDatabase) {
        continue;
    }

    $isolated = false;
    foreach ($isolation as $guard) {
        if (str_contains($source, $guard)) {
            $isolated = true;
            break;
        }
    }

    if (! $isolated) {
        $errors[] = 'Database-using Feature test has no explicit Laravel database isolation: '.str_replace($root.'/', '', $file->getPathname());
    }
}

$runner = (string) file_get_contents($root.'/scripts/run-database-tests.php');
foreach ([
    "'--prepare-schema'",
    "'artisan', 'migrate:fresh', '--force', '--ansi'",
    'scripts/verify-test-database-safety.php',
    '[SongChart DB diagnostic]',
    '[SongChart test evidence]',
    'postgres-test-last-failure.log',
    'songchart_environment_guard',
] as $signal) {
    if (! str_contains($runner, $signal)) {
        $errors[] = "Database test runner is missing clean-schema safety signal [{$signal}].";
    }
}

if (! str_contains($runner, '[SongChart test evidence] === COPY FROM HERE ===')) {
    $errors[] = 'Database test runner must print copy-ready failure evidence after diagnostics.';
}

if ($errors !== []) {
    fwrite(STDERR, "Database test isolation verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, 'Database test isolation verification passed.'.PHP_EOL);
