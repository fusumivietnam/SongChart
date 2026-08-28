<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$runtimePrefixes = [
    'storage/framework/',
    'storage/logs/',
    '.songchart-backups/',
];
$allowedTracked = [
    'storage/framework/cache/data/.gitignore',
    'storage/framework/sessions/.gitignore',
    'storage/framework/views/.gitignore',
    'storage/logs/.gitignore',
];

$command = 'git -C '.escapeshellarg($root).' ls-files -z';
exec($command, $lines, $status);
if ($status !== 0) {
    fwrite(STDERR, "Runtime artifact ownership verification failed: unable to enumerate tracked files.\n");
    exit(1);
}

$tracked = array_values(array_filter(explode("\0", implode("\n", $lines)), static fn (string $path): bool => $path !== ''));
$violations = [];

foreach ($tracked as $path) {
    if (in_array($path, $allowedTracked, true)) {
        continue;
    }

    foreach ($runtimePrefixes as $prefix) {
        if (str_starts_with($path, $prefix)) {
            $violations[] = "tracked runtime artifact [{$path}] must remain runtime-only";
            break;
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Runtime artifact ownership verification failed:\n- ".implode("\n- ", $violations)."\n");
    exit(1);
}

fwrite(STDOUT, "Runtime artifact ownership verification passed.\n");
