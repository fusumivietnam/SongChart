<?php

declare(strict_types=1);

use App\Application\Chart\RefreshYouTubeViewChart;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Chart\DatabaseChartSnapshotStore;
use App\Support\Chart\YouTubeViewCountObservationSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

it('fetches approved YouTube viewCount evidence and publishes a persisted canonical chart', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $provider = Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create([
        'title' => 'Golden YouTube Song',
        'slug' => 'golden-youtube-song',
    ]);
    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'abcdefghijk',
        'url' => 'https://www.youtube.com/watch?v=abcdefghijk',
        'title' => 'Golden YouTube Song',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 100,
        'review_state' => 'approved',
        'verified_at' => now(),
        'last_checked_at' => now(),
    ]);

    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'abcdefghijk',
                'status' => ['privacyStatus' => 'public'],
                'statistics' => ['viewCount' => '123456'],
            ]],
        ]),
    ]);

    $snapshotId = app(RefreshYouTubeViewChart::class)->handle();
    $snapshot = app(DatabaseChartSnapshotStore::class)->latest(RefreshYouTubeViewChart::CHART_ID);

    expect($snapshotId)->not->toBeNull()
        ->and($snapshot)->not->toBeNull()
        ->and($snapshot?->metric)->toBe(YouTubeViewCountObservationSource::METRIC)
        ->and($snapshot?->rows)->toHaveCount(1)
        ->and($snapshot?->rows[0]['canonical_recording_id'])->toBe((string) $recording->getKey())
        ->and($snapshot?->rows[0]['score'])->toEqual(123456.0)
        ->and($snapshot?->rows[0]['observations'][0]['metric_semantics_version'])->toBe(YouTubeViewCountObservationSource::SEMANTICS_VERSION)
        ->and($snapshot?->rows[0]['observations'][0]['source_reference'])->toBe('youtube:videos.list:abcdefghijk:statistics');

    Http::assertSent(fn (Request $request): bool => str_contains($request->url(), '/youtube/v3/videos')
        && $request['part'] === 'statistics,status'
        && $request['id'] === 'abcdefghijk'
        && $request['key'] === 'test-key'
    );

    $this->get('/charts/'.RefreshYouTubeViewChart::CHART_ID)
        ->assertOk()
        ->assertSee('Golden YouTube Song')
        ->assertSee('123456');
});

it('does not create a chart snapshot when no approved public YouTube destination exists', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    expect(app(RefreshYouTubeViewChart::class)->handle())->toBeNull();
    Http::assertNothingSent();
});
