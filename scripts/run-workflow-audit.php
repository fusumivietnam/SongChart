<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$composerPath = $root.'/composer.json';

try {
    $composer = json_decode((string) file_get_contents($composerPath), true, 512, JSON_THROW_ON_ERROR);
} catch (Throwable $exception) {
    fwrite(STDERR, '[SongChart audit] Unable to read composer.json: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

$quality = $composer['scripts']['quality:verify'] ?? null;
if (! is_array($quality)) {
    fwrite(STDERR, "[SongChart audit] composer quality:verify must be an ordered script array.\n");
    exit(1);
}

$forbidden = [
    '@stage:verify',
    '@canonical:verify',
    '@test:postgres',
    'npm run build',
];

$failures = [];
$executed = 0;

fwrite(STDOUT, "[SongChart audit] Collecting quality/static/governance diagnostics without fail-fast.\n");

foreach ($quality as $index => $command) {
    if (! is_string($command) || trim($command) === '') {
        $failures[] = '#'.($index + 1).' invalid empty/non-string quality command';
        continue;
    }

    $command = trim($command);
    foreach ($forbidden as $blocked) {
        if ($command === $blocked || str_contains($command, $blocked)) {
            $failures[] = "{$command} is a closure/runtime command and must not run inside audit";
            continue 2;
        }
    }

    $executed++;
    fwrite(STDOUT, PHP_EOL."[SongChart audit] >>> {$command}".PHP_EOL);

    if (str_starts_with($command, '@')) {
        $script = substr($command, 1);
        $shell = 'composer run-script '.escapeshellarg($script);
    } else {
        $shell = $command;
    }

    passthru('cd '.escapeshellarg($root).' && '.$shell, $status);
    if ($status !== 0) {
        $failures[] = "{$command} (exit {$status})";
    }
}

fwrite(STDOUT, PHP_EOL."[SongChart audit] Executed {$executed} checks.".PHP_EOL);

if ($failures !== []) {
    fwrite(STDERR, '[SongChart audit] FAILURES ('.count($failures).'):' . PHP_EOL);
    foreach ($failures as $failure) {
        fwrite(STDERR, "- {$failure}".PHP_EOL);
    }
    exit(1);
}

fwrite(STDOUT, "[SongChart audit] PASSED.\n");
