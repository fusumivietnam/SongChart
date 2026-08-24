<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$composerLock = $root.'/composer.lock';
$packageLock = $root.'/package-lock.json';

if (! is_file($composerLock)) {
    $errors[] = 'composer.lock is missing.';
} else {
    $composer = json_decode((string) file_get_contents($composerLock), true);

    if (! is_array($composer)) {
        $errors[] = 'composer.lock is not valid JSON.';
    } elseif (! isset($composer['content-hash']) || ! is_string($composer['content-hash']) || $composer['content-hash'] === '') {
        $errors[] = 'composer.lock does not contain a valid content-hash.';
    }
}

if (! is_file($packageLock)) {
    $errors[] = 'package-lock.json is missing.';
} else {
    $package = json_decode((string) file_get_contents($packageLock), true);

    if (! is_array($package)) {
        $errors[] = 'package-lock.json is not valid JSON.';
    } else {
        $lockfileVersion = $package['lockfileVersion'] ?? null;
        $packages = $package['packages'] ?? null;

        if (! is_int($lockfileVersion) || $lockfileVersion < 3) {
            $errors[] = 'package-lock.json must use lockfileVersion 3 or newer.';
        }

        if (! is_array($packages)) {
            $errors[] = 'package-lock.json does not contain a packages map.';
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, "Release lock verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, 'Release lock verification passed.'.PHP_EOL);
