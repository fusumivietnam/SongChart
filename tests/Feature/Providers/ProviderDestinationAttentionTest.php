<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Admin\ProviderDestinationAttention;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('classifies unknown stale unavailable and ready provider destinations for operators', function (): void {
    CarbonImmutable::setTestNow('2026-08-31T00:00:00+00:00');

    $provider = Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => 'media',
        'status' => 'approved',
        'is_enabled' => true,
    ]);

    $base = [
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => '01JRECORDING000000000000001',
        'url' => 'https://www.youtube.com/watch?v=video',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 90,
        'review_state' => 'approved',
        'verified_at' => now()->subDay(),
    ];

    ProviderDestination::query()->create([...$base, 'provider_resource_id' => 'unknown', 'title' => 'Unknown', 'last_checked_at' => null]);
    ProviderDestination::query()->create([...$base, 'provider_resource_id' => 'stale', 'title' => 'Stale', 'last_checked_at' => now()->subDays(45)]);
    ProviderDestination::query()->create([...$base, 'provider_resource_id' => 'private', 'title' => 'Private', 'privacy_status' => 'private', 'last_checked_at' => now()->subDay()]);
    ProviderDestination::query()->create([...$base, 'provider_resource_id' => 'ready', 'title' => 'Ready', 'last_checked_at' => now()->subDay()]);

    $result = app(ProviderDestinationAttention::class)->forProvider($provider);
    $states = collect($result['items'])->pluck('status_key', 'resource_id');

    expect($result['summary'])
        ->toMatchArray(['total' => 4, 'attention' => 3, 'unknown' => 1, 'stale' => 1, 'unavailable' => 1])
        ->and($states['unknown'])->toBe('unknown')
        ->and($states['stale'])->toBe('stale')
        ->and($states['private'])->toBe('unavailable')
        ->and($states['ready'])->toBe('ready');

    CarbonImmutable::setTestNow();
});
