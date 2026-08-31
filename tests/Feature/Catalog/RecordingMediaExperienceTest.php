<?php

declare(strict_types=1);

use App\Application\Catalog\Queries\RecordingMediaExperience;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Destinations\ProviderDestinationPreference;
use App\Domain\Providers\Enums\ProviderCategory;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Providers\Destinations\PublicProviderDestinationProjection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

function stage18YoutubeProvider(): Provider
{
    return Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => ProviderCategory::Music->value,
        'status' => 'approved',
        'is_enabled' => true,
    ]);
}

it('projects playable state for an approved fresh embeddable YouTube destination', function (): void {
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
        ->and($media['state'])->toBe(PublicProviderDestinationProjection::STATE_PLAYABLE)
        ->and($media['reason_codes'])->toBe([PublicProviderDestinationProjection::REASON_SELECTED_EMBEDDABLE])
        ->and($media['can_embed'])->toBeTrue()
        ->and($media['embed_url'])->toBe('https://www.youtube-nocookie.com/embed/video123');

    $html = Blade::render('<x-provider.media-player :media="$media" />', ['media' => $media]);
    expect($html)->toContain('data-media-state="playable"')
        ->and($html)->toContain('youtube-nocookie.com/embed/video123')
        ->and($html)->toContain('Mở trên YouTube');
});

it('projects outbound-only state for an eligible public non-embeddable destination', function (): void {
    $recording = Recording::factory()->create(['slug' => 'outbound-video-recording']);
    $provider = stage18YoutubeProvider();

    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'outbound123',
        'url' => 'https://www.youtube.com/watch?v=outbound123',
        'title' => 'Official outbound video',
        'is_embeddable' => false,
        'privacy_status' => 'public',
        'match_score' => 96,
        'review_state' => 'approved',
        'verified_at' => now()->subDay(),
        'last_checked_at' => now()->subDay(),
    ]);

    $media = app(RecordingMediaExperience::class)->forSlug('outbound-video-recording');

    expect($media)->not->toBeNull()
        ->and($media['state'])->toBe(PublicProviderDestinationProjection::STATE_OUTBOUND_ONLY)
        ->and($media['reason_codes'])->toBe([PublicProviderDestinationProjection::REASON_SELECTED_OUTBOUND_ONLY])
        ->and($media['can_embed'])->toBeFalse()
        ->and($media['embed_url'])->toBeNull()
        ->and($media['url'])->toBe('https://www.youtube.com/watch?v=outbound123');

    $html = Blade::render('<x-provider.media-player :media="$media" />', ['media' => $media]);
    expect($html)->toContain('data-media-state="outbound_only"')
        ->and($html)->toContain('Chỉ mở trên provider')
        ->and($html)->not->toContain('youtube-nocookie.com/embed/outbound123');
});

it('projects no-selection with freshness explainability after the freshness window expires', function (): void {
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
        ->and($media['state'])->toBe(PublicProviderDestinationProjection::STATE_NO_SELECTION)
        ->and($media['reason_codes'])->toContain(ProviderDestinationPreference::ISSUE_FRESHNESS_STALE)
        ->and($media['can_embed'])->toBeFalse()
        ->and($media['availability_reason'])->toContain('Evidence media chưa đủ mới');

    $html = Blade::render('<x-provider.media-player :media="$media" />', ['media' => $media]);
    expect($html)->toContain('data-media-state="no_selection"')
        ->and($html)->toContain('Chưa có media khả dụng')
        ->and($html)->not->toContain('stale123');
});

it('projects privacy failure without exposing the private destination', function (): void {
    $recording = Recording::factory()->create(['slug' => 'private-video-recording']);
    $provider = stage18YoutubeProvider();

    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'private123',
        'url' => 'https://www.youtube.com/watch?v=private123',
        'title' => 'Private video',
        'is_embeddable' => true,
        'privacy_status' => 'private',
        'match_score' => 99,
        'review_state' => 'approved',
        'verified_at' => now()->subHour(),
        'last_checked_at' => now()->subHour(),
    ]);

    $media = app(RecordingMediaExperience::class)->forSlug('private-video-recording');

    expect($media)->not->toBeNull()
        ->and($media['state'])->toBe(PublicProviderDestinationProjection::STATE_NO_SELECTION)
        ->and($media['reason_codes'])->toContain(ProviderDestinationPreference::ISSUE_PRIVACY_NOT_PUBLIC)
        ->and($media['availability_reason'])->toContain('Media chưa được xác nhận là public.')
        ->and($media['url'])->toBeNull();
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
        ->and($media['state'])->toBe(PublicProviderDestinationProjection::STATE_PLAYABLE)
        ->and($media['title'])->toBe('Eligible public video')
        ->and($media['embed_url'])->toBe('https://www.youtube-nocookie.com/embed/public123')
        ->and($media['selection_reason'])->toContain('thứ hạng deterministic cao nhất');
});
