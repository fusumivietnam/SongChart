<?php

declare(strict_types=1);

use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use Illuminate\Support\Facades\Cache;

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    Cache::clear();
    config()->set('songchart.providers.musicbrainz.rate', [
        'strategy' => 'minimum_interval',
        'minimum_interval_ms' => 1,
        'default_cooldown_seconds' => 2,
        'maximum_cooldown_seconds' => 30,
        'lock_wait_seconds' => 2,
    ]);
});

it('resolves provider-scoped rate policy without hard-coding the gate to MusicBrainz', function (): void {
    $policy = app(ProviderRatePolicyRegistry::class)->for('musicbrainz', 'artist.search');

    expect($policy->providerSlug)->toBe('musicbrainz')
        ->and($policy->operation)->toBe('artist.search')
        ->and($policy->strategy->value)->toBe('minimum_interval')
        ->and($policy->minimumIntervalMilliseconds)->toBe(1);
});

it('shares provider cooldown state through the configured cache store', function (): void {
    $policies = app(ProviderRatePolicyRegistry::class);
    $gate = app(ProviderRequestGate::class);
    $policy = $policies->for('musicbrainz', 'artist.search');

    $gate->recordCooldown($policy, 5, 'rate-limited');
    $state = $gate->state($policy);

    expect($state->status())->toBe('cooldown')
        ->and($state->cooldownRemainingSeconds)->toBeGreaterThanOrEqual(1)
        ->and($state->cooldownReason)->toBe('rate-limited');

    expect(fn () => $gate->await($policy))
        ->toThrow(ProviderRequestException::class, 'cooling down');
});
