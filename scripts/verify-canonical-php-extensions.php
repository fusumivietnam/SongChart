<?php

declare(strict_types=1);

$required = [
    'bcmath',
    'curl',
    'dom',
    'intl',
    'mbstring',
    'pcntl',
    'pdo_pgsql',
    'redis',
    'xml',
    'zip',
];

$missing = array_values(array_filter(
    $required,
    static fn (string $extension): bool => ! extension_loaded($extension),
));

if ($missing !== []) {
    fwrite(STDERR, "Canonical PHP extension verification failed:\n- ".implode("\n- ", $missing).PHP_EOL);

    exit(1);
}

fwrite(STDOUT, 'Canonical PHP extension verification passed: '.implode(', ', $required).PHP_EOL);
