<?php

declare(strict_types=1);

use App\Support\Engineering\PhpSemanticFingerprint;

$root = dirname(__DIR__);
require_once $root.'/app/Support/Engineering/PhpSemanticFingerprint.php';

$contract = json_decode(
    (string) file_get_contents($root.'/docs/project/stack/migration-lifecycle-contract.json'),
    true,
    512,
    JSON_THROW_ON_ERROR,
);
$target = $root.'/'.($contract['baseline']['path'] ?? 'docs/project/generated/migration-history-baseline.json');

if (is_file($target)) {
    fwrite(STDOUT, "Migration history baseline already sealed; refusing to overwrite it.\n");
    exit(0);
}

$historical = $contract['historical_migrations'] ?? [];
if (! is_array($historical) || $historical === []) {
    fwrite(STDERR, "Migration lifecycle seal refused: no historical migrations are declared.\n");
    exit(1);
}

$fingerprints = [];
foreach ($historical as $relative) {
    if (! is_string($relative) || ! is_file($root.'/'.$relative)) {
        fwrite(STDERR, "Migration lifecycle seal refused: historical migration is missing [{$relative}].\n");
        exit(1);
    }

    $fingerprints[$relative] = PhpSemanticFingerprint::fromFile($root.'/'.$relative);
}

$baseline = [
    'schema_version' => 1,
    'algorithm' => PhpSemanticFingerprint::ALGORITHM,
    'sealed_from' => 'exact-canonical-target-after-locked-pint-normalization',
    'historical_migrations' => $fingerprints,
];

if (! is_dir(dirname($target))) {
    mkdir(dirname($target), 0777, true);
}

file_put_contents(
    $target,
    json_encode($baseline, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR).PHP_EOL,
);

fwrite(STDOUT, "Migration history baseline sealed from the exact canonical target.\n");

$compile = proc_open(
    [PHP_BINARY, 'scripts/compile-repository-contracts.php', '--refresh-check'],
    [STDIN, STDOUT, STDERR],
    $pipes,
    $root,
);
if (! is_resource($compile) || proc_close($compile) !== 0) {
    fwrite(STDERR, "Migration lifecycle seal failed while refreshing the exact-tree repository manifest.\n");
    exit(1);
}
