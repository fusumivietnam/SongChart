<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'PROJECT_AUTHORITY.md',
    'docs/project/stack/package-registry.json',
    'docs/project/stack/candidate-verification-contract.json',
    'candidate-verification.json',
    'app/Console/Commands/SongChartDoctorCommand.php',
    'scripts/verify-package-governance.php',
    'scripts/verify-test-taxonomy.php',
    'scripts/verify-candidate-contract.php',
];

foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        fwrite(STDERR, "Missing engineering governance file: {$file}\n");

        exit(1);
    }
}

$migration = (string) file_get_contents($root.'/database/migrations/2026_08_11_000100_create_pulse_tables.php');
$failClosedBranches = substr_count($migration, 'default => throw new LogicException(');

if ($failClosedBranches !== 3) {
    fwrite(STDERR, "Pulse migration must fail closed for unsupported database drivers in all three table definitions.\n");

    exit(1);
}

$authority = (string) file_get_contents($root.'/PROJECT_AUTHORITY.md');

foreach (['composer stage:verify', 'composer canonical:verify', 'tests/Unit', 'package-registry.json', 'candidate-verification.json'] as $signal) {
    if (! str_contains($authority, $signal)) {
        fwrite(STDERR, "Project authority missing signal: {$signal}\n");

        exit(1);
    }
}

echo "Engineering toolchain governance verification passed.\n";
