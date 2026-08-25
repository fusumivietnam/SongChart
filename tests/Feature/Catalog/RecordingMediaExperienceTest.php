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

it('fails closed to outbound-only presentation when an approved destination is stale', function (): void {
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

    $media = app(RecordingMediaExperience::class)->forSlug('stale-video-recording');

    expect($media)->not->toBeNull()
        ->and($media['can_embed'])->toBeFalse()
        ->and($media['embed_url'])->toBeNull()
        ->and($media['availability_label'])->toBe('Cần kiểm tra lại');

    $html = Blade::render('<x-provider.media-player :media="$media" />', ['media' => $media]);
    expect($html)->not->toContain('<iframe')
        ->and($html)->toContain('Mở trên YouTube')
        ->and($html)->toContain('Cần kiểm tra lại');
});
