<?php

declare(strict_types=1);

use App\Enums\QueueName;

it('defines the stage 16.3 queue taxonomy', function (): void {
    expect(QueueName::values())->toBe([
        'critical',
        'discovery-projections',
        'provider-health',
        'provider-imports',
        'provider-normalization',
        'notifications',
        'default',
    ]);
});

it('requires Horizon as the canonical Redis queue supervisor', function (): void {
    $composer = json_decode((string) file_get_contents(dirname(__DIR__, 2).'/composer.json'), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['require']['laravel/horizon'] ?? null)->toBe('^5.48')
        ->and(array_key_exists('laravel/horizon', $composer['require-dev']))->toBeFalse()
        ->and(array_key_exists('laravel/horizon', $composer['suggest'] ?? []))->toBeFalse();
});
