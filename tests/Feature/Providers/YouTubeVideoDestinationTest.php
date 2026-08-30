<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Providers\Destinations\YouTubeDestinationWorkbench;
use App\Support\Providers\Destinations\YouTubeVideoDestinationDiscovery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('discovers YouTube candidates then verifies video metadata', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $recording = Recording::factory()->create(['title' => 'Get Lucky', 'duration_ms' => 369000]);

    Http::fake([
        'www.googleapis.com/youtube/v3/search*' => Http::response([
            'items' => [[
                'id' => ['videoId' => 'abcdefghijk'],
                'snippet' => ['title' => 'Get Lucky'],
            ]],
        ]),
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'abcdefghijk',
                'snippet' => ['title' => 'Get Lucky (Official Audio)', 'channelId' => 'channel-1', 'channelTitle' => 'Daft Punk'],
                'contentDetails' => ['duration' => 'PT6M9S'],
                'status' => ['privacyStatus' => 'public', 'embeddable' => true],
            ]],
        ]),
    ]);

    $candidates = app(YouTubeVideoDestinationDiscovery::class)->candidates($recording);

    expect($candidates)->toHaveCount(1)
        ->and($candidates[0]->resourceId)->toBe('abcdefghijk')
        ->and($candidates[0]->durationMs)->toBe(369000)
        ->and($candidates[0]->embeddable)->toBeTrue()
        ->and($candidates[0]->evidence['availability'])->toBe('observed');

    Http::assertSentCount(2);
});

it('revalidates and persists an approved YouTube destination for a canonical Recording', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $provider = Provider::query()->create([
        'slug' => 'youtube', 'name' => 'YouTube', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create(['title' => 'Pink Venom', 'duration_ms' => 187000]);

    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'abcdefghijk',
                'snippet' => ['title' => 'BLACKPINK - Pink Venom (Official Video)', 'channelId' => 'channel-1', 'channelTitle' => 'BLACKPINK'],
                'contentDetails' => ['duration' => 'PT3M7S'],
                'status' => ['privacyStatus' => 'public', 'embeddable' => true],
            ]],
        ]),
    ]);

    app(YouTubeDestinationWorkbench::class)->approve((string) $provider->id, (string) $recording->id, 'abcdefghijk');

    $this->assertDatabaseHas('provider_destinations', [
        'provider_id' => $provider->id,
        'entity_type' => 'recording',
        'entity_id' => $recording->id,
        'provider_resource_id' => 'abcdefghijk',
        'review_state' => 'approved',
        'privacy_status' => 'public',
        'is_embeddable' => true,
    ]);
});

it('approves a public non-embeddable YouTube destination as outbound-only evidence', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $provider = Provider::query()->create([
        'slug' => 'youtube', 'name' => 'YouTube', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create(['title' => 'Outbound Only', 'duration_ms' => 180000]);

    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'outbound123',
                'snippet' => ['title' => 'Outbound Only (Official Audio)', 'channelId' => 'channel-1', 'channelTitle' => 'Official'],
                'contentDetails' => ['duration' => 'PT3M'],
                'status' => ['privacyStatus' => 'public', 'embeddable' => false],
            ]],
        ]),
    ]);

    $destination = app(YouTubeDestinationWorkbench::class)->approve(
        (string) $provider->id,
        (string) $recording->id,
        'outbound123',
    );

    expect($destination->privacy_status)->toBe('public')
        ->and($destination->is_embeddable)->toBeFalse()
        ->and($destination->review_state)->toBe('approved');
});

it('reverification preserves canonical Recording linkage while private evidence fails closed', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $provider = Provider::query()->create([
        'slug' => 'youtube', 'name' => 'YouTube', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create(['title' => 'Private Later', 'duration_ms' => 180000]);
    $destination = ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'private123',
        'url' => 'https://www.youtube.com/watch?v=private123',
        'title' => 'Private Later',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 95,
        'review_state' => 'approved',
        'verified_at' => now()->subDays(5),
        'last_checked_at' => now()->subDays(5),
    ]);
    $verifiedAt = $destination->verified_at;

    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'private123',
                'snippet' => ['title' => 'Private Later', 'channelId' => 'channel-1', 'channelTitle' => 'Official'],
                'contentDetails' => ['duration' => 'PT3M'],
                'status' => ['privacyStatus' => 'private', 'embeddable' => false],
            ]],
        ]),
    ]);

    $reverified = app(YouTubeDestinationWorkbench::class)->reverify((string) $destination->id);

    expect($reverified->entity_id)->toBe($recording->id)
        ->and($reverified->entity_type)->toBe(EntityType::Recording)
        ->and($reverified->review_state)->toBe('approved')
        ->and($reverified->privacy_status)->toBe('private')
        ->and($reverified->is_embeddable)->toBeFalse()
        ->and($reverified->verified_at?->equalTo($verifiedAt))->toBeTrue()
        ->and($reverified->evidence['availability'])->toBe('observed');
});

it('reverification marks a missing YouTube resource unavailable without changing canonical identity', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $provider = Provider::query()->create([
        'slug' => 'youtube', 'name' => 'YouTube', 'category' => 'music',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create(['title' => 'Deleted Later']);
    $destination = ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'missing123',
        'url' => 'https://www.youtube.com/watch?v=missing123',
        'title' => 'Deleted Later',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 90,
        'review_state' => 'approved',
        'verified_at' => now()->subDays(10),
        'last_checked_at' => now()->subDays(10),
    ]);

    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response(['items' => []]),
    ]);

    $reverified = app(YouTubeDestinationWorkbench::class)->reverify((string) $destination->id);

    expect($reverified->entity_id)->toBe($recording->id)
        ->and($reverified->review_state)->toBe('approved')
        ->and($reverified->privacy_status)->toBeNull()
        ->and($reverified->is_embeddable)->toBeFalse()
        ->and($reverified->last_checked_at)->not->toBeNull()
        ->and($reverified->evidence['availability'])->toBe('unavailable');
});
