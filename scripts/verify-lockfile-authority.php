<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$release = in_array('--release', $argv, true);
$errors = [];

foreach ([
    'composer.json' => 'composer.lock',
    'package.json' => 'package-lock.json',
] as $manifest => $lock) {
    if (! is_file($root.'/'.$manifest)) {
        $errors[] = "Dependency manifest missing [{$manifest}].";

        continue;
    }

    if (! is_file($root.'/'.$lock)) {
        $message = "Dependency lockfile missing [{$lock}] for [{$manifest}].";
        if ($release) {
            $errors[] = $message;
        } else {
            fwrite(STDOUT, "Lockfile authority note: {$message}\n");
        }
    }
}

$composer = (string) file_get_contents($root.'/composer.json');
if (str_contains($composer, '"minimum-stability": "dev"')) {
    $errors[] = 'Release dependency authority must not use minimum-stability=dev.';
}

if ($errors !== []) {
    fwrite(STDERR, "Lockfile authority verification failed:\n- ".implode("\n- ", array_unique($errors)).PHP_EOL);
    exit(1);
}

fwrite(STDOUT, $release
    ? "Release lockfile authority verification passed.\n"
    : "Lockfile authority structure verification passed; release mode enforces file presence.\n");
