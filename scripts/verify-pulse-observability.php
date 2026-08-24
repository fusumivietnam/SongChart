<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$required = [
    'config/pulse.php',
    'database/migrations/2026_08_11_000100_create_pulse_tables.php',
    'docs/operations/pulse-observability.md',
    'tests/Unit/PulseObservabilityContractTest.php',
    'tests/Architecture/PulseObservabilityBoundaryTest.php',
];

foreach ($required as $file) {
    if (! is_file($root.'/'.$file)) {
        fwrite(STDERR, "Missing Pulse observability file: {$file}\n");

        exit(1);
    }
}

$composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
if (($composer['require']['laravel/pulse'] ?? null) !== '^1.7.4') {
    fwrite(STDERR, "laravel/pulse ^1.7.4 must be required.\n");

    exit(1);
}

$pulse = (string) file_get_contents($root.'/config/pulse.php');
foreach (['Authorize::class', "PULSE_SLOW_QUERIES_THRESHOLD', 500", "PULSE_INGEST_DRIVER', 'storage'"] as $signal) {
    if (! str_contains($pulse, $signal)) {
        fwrite(STDERR, "Missing Pulse contract signal: {$signal}\n");

        exit(1);
    }
}

$provider = (string) file_get_contents($root.'/app/Providers/AuthorizationServiceProvider.php');
$capabilities = (string) file_get_contents($root.'/app/Enums/Capability.php');
if (! str_contains($provider, 'foreach (Capability::cases() as $capability)')
    || ! str_contains($capabilities, "case ViewPulse = 'viewPulse';")
) {
    fwrite(STDERR, "Pulse must use the shared Capability/Gate authorization authority.\n");

    exit(1);
}

echo "Pulse observability verification passed.\n";
