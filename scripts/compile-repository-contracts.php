<?php

declare(strict_types=1);

use App\Support\Engineering\RepositoryContractResolver;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/RepositoryContractResolver.php';

$resolver = new RepositoryContractResolver($root);
$manifest = $resolver->compileManifest();
$target = $root.'/docs/project/generated/repository-contract-manifest.json';
$encoded = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR).PHP_EOL;

$check = static function () use ($root, $target): int {
    $resolver = new RepositoryContractResolver($root);
    $expected = json_encode(
        $resolver->compileManifest(),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    ).PHP_EOL;

    if (! is_file($target)) {
        fwrite(STDERR, "Compiled repository contract manifest is missing. Run: php scripts/compile-repository-contracts.php --refresh-check\n");

        return 1;
    }

    $current = (string) file_get_contents($target);
    if (! hash_equals(hash('sha256', $expected), hash('sha256', $current))) {
        fwrite(STDERR, "Compiled repository contract manifest is stale for the current source tree.\n");

        return 1;
    }

    fwrite(STDOUT, "Compiled repository contract manifest is current for the exact source tree.\n");

    return 0;
};

if (in_array('--check', $argv, true)) {
    exit($check());
}

file_put_contents($target, $encoded);
fwrite(STDOUT, "Compiled repository contract manifest written from the current source tree.\n");

if (in_array('--refresh-check', $argv, true)) {
    exit($check());
}
