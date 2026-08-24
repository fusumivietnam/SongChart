<?php

declare(strict_types=1);

it('pins first party Pulse and exposes bounded operational defaults', function (): void {
    $root = dirname(__DIR__, 2);
    $composer = json_decode((string) file_get_contents($root.'/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $pulse = (string) file_get_contents($root.'/config/pulse.php');

    expect($composer['require']['laravel/pulse'] ?? null)->toBe('^1.7.4')
        ->and(str_contains($pulse, "'threshold' => env('PULSE_SLOW_QUERIES_THRESHOLD', 500)"))->toBeTrue()
        ->and(str_contains($pulse, 'Authorize::class'))->toBeTrue()
        ->and(str_contains($pulse, "'driver' => env('PULSE_INGEST_DRIVER', 'storage')"))->toBeTrue();
});

it('authorizes Pulse through the operations capability', function (): void {
    $root = dirname(__DIR__, 2);
    $provider = (string) file_get_contents($root.'/app/Providers/AuthorizationServiceProvider.php');
    $capabilities = (string) file_get_contents($root.'/app/Enums/Capability.php');

    expect(str_contains($provider, 'foreach (Capability::cases() as $capability)'))->toBeTrue()
        ->and(str_contains($capabilities, "case ViewPulse = 'viewPulse';"))->toBeTrue();
});
