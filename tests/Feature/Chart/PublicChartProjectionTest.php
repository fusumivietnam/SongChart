<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Application\Chart\Queries\PublicChartProjection;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Models\Catalog\Recording;
use App\Support\Chart\YouTubeViewCountObservationSource;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

afterEach(function (): void {
    Carbon::setTestNow();
});

it('projects persisted chart provenance and freshness onto canonical public recording identity', function (): void {
    Carbon::setTestNow('2026-09-09T01:00:00+00:00');

    $recording = Recording::factory()->create([
        'title' => 'Golden Song',
        'slug' => 'golden-song',
    ]);

    $snapshot = (new BuildChartSnapshot)->handle(
        'youtube-video-views',
        YouTubeViewCountObservationSource::METRIC,
        new DateTimeImmutable('2026-09-09T00:30:00+00:00'),
        [
            new ChartMetricObservation(
                'provider-observation-1',
                (string) $recording->getKey(),
                'youtube',
                'provider-item-1',
                YouTubeViewCountObservationSource::METRIC,
                100,
                new DateTimeImmutable('2026-09-09T00:00:00+00:00'),
                YouTubeViewCountObservationSource::METRIC_UNIT,
                YouTubeViewCountObservationSource::SEMANTICS_VERSION,
                new DateTimeImmutable('2026-09-09T00:05:00+00:00'),
                'youtube:videos.list:provider-item-1:statistics',
            ),
        ],
    );

    $public = app(PublicChartProjection::class)->fromSnapshot($snapshot);

    expect($public['state'])->toBe('observed')
        ->and($public['metric_unit'])->toBe('views')
        ->and($public['metric_semantics_version'])->toBe(YouTubeViewCountObservationSource::SEMANTICS_VERSION)
        ->and($public['provider'])->toBe('youtube')
        ->and($public['freshness'])->toBe('fresh')
        ->and($public['rows'])->toHaveCount(1)
        ->and($public['rows'][0]['canonical_recording_id'])->toBe((string) $recording->getKey())
        ->and($public['rows'][0]['title'])->toBe('Golden Song')
        ->and($public['rows'][0]['url'])->toContain('golden-song')
        ->and($public['rows'][0]['value_state'])->toBe('observed')
        ->and($public['rows'][0]['evidence_count'])->toBe(1)
        ->and($public['rows'][0]['observation_freshness'])->toBe('fresh')
        ->and($public['rows'][0]['observations'][0]['provider'])->toBe('youtube')
        ->and($public['rows'][0]['observations'][0]['metric_unit'])->toBe('views')
        ->and($public['rows'][0]['observations'][0]['source_reference'])->toBe('youtube:videos.list:provider-item-1:statistics');
});

it('keeps an observed zero distinct from unavailable chart evidence', function (): void {
    Carbon::setTestNow('2026-09-09T01:00:00+00:00');

    $recording = Recording::factory()->create([
        'title' => 'Observed Zero Song',
        'slug' => 'observed-zero-song',
    ]);

    $snapshot = (new BuildChartSnapshot)->handle(
        'youtube-video-views',
        YouTubeViewCountObservationSource::METRIC,
        new DateTimeImmutable('2026-09-09T00:30:00+00:00'),
        [
            new ChartMetricObservation(
                'provider-observation-zero',
                (string) $recording->getKey(),
                'youtube',
                'provider-item-zero',
                YouTubeViewCountObservationSource::METRIC,
                0,
                new DateTimeImmutable('2026-09-09T00:00:00+00:00'),
                YouTubeViewCountObservationSource::METRIC_UNIT,
                YouTubeViewCountObservationSource::SEMANTICS_VERSION,
            ),
        ],
    );

    $public = app(PublicChartProjection::class)->fromSnapshot($snapshot);

    expect($public['state'])->toBe('observed')
        ->and($public['rows'][0]['score'])->toEqual(0.0)
        ->and($public['rows'][0]['value_state'])->toBe('observed_zero')
        ->and($public['rows'][0]['evidence_count'])->toBe(1);
});
