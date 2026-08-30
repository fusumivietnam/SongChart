<?php

declare(strict_types=1);

use App\Application\Catalog\Queries\RecordingMediaExperience;
use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

function stage18YoutubeProvider(): Provider
{
    return Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => 'media',
        'status' => 'approved',
        'is_enabled' => true,
    ]);
}

it('renders a privacy-enhanced iframe for an approved fresh embeddable YouTube destination', function (): void {
    $recording = Recording::factory()->create(['slug' => 'fresh-video-recording']);
    $provider = stage18YoutubeProvider();

    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'video123',
        'url' => 'https://www.youtube.com/watch?v=video123',
        'title' => 'Official video',
        'channel_title' => 'Official channel',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 95,
        'review_state' => 'approved',
        'verified_at' => now()->subDay(),
        'last_checked_at' => now()->subDay(),
    ]);

    $media = app(RecordingMediaExperience::class)->forSlug('fresh-video-recording');

    expect($media)->not->toBeNull()
        ->and($media['can_embed'])->toBeTrue()
        ->and($media['embed_url'])->toBe('https://www.youtube-nocookie.com/embed/video123');

    $html = Blade::render('<x-provider.media-player :media="$media" />', ['media' => $media]);
    expect($html)->toContain('youtube-nocookie.com/embed/video123')
        ->and($html)->toContain('Mở trên YouTube');
});

it('does not expose an approved destination after its freshness window expires', function (): void {
    $recording = Recording::factory()->create(['slug' => 'stale-video-recording']);
    $provider = stage18YoutubeProvider();

    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'stale123',
        'url' => 'https://www.youtube.com/watch?v=stale123',
        'title' => 'Older video',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 90,
        'review_state' => 'approved',
        'verified_at' => now()->subDays(45),
        'last_checked_at' => now()->subDays(45),
    ]);

    expect(app(RecordingMediaExperience::class)->forSlug('stale-video-recording'))->toBeNull();
});

it('does not let a newer private destination shadow an older eligible public destination', function (): void {
    $recording = Recording::factory()->create(['slug' => 'deterministic-video-recording']);
    $provider = stage18YoutubeProvider();

    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'public123',
        'url' => 'https://www.youtube.com/watch?v=public123',
        'title' => 'Eligible public video',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 91,
        'review_state' => 'approved',
        'verified_at' => now()->subDays(2),
        'last_checked_at' => now()->subDays(2),
    ]);

    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'private999',
        'url' => 'https://www.youtube.com/watch?v=private999',
        'title' => 'Newer private video',
        'is_embeddable' => true,
        'privacy_status' => 'private',
        'match_score' => 99,
        'review_state' => 'approved',
        'verified_at' => now()->subHour(),
        'last_checked_at' => now()->subHour(),
    ]);

    $media = app(RecordingMediaExperience::class)->forSlug('deterministic-video-recording');

    expect($media)->not->toBeNull()
        ->and($media['title'])->toBe('Eligible public video')
        ->and($media['embed_url'])->toBe('https://www.youtube-nocookie.com/embed/public123');
});
