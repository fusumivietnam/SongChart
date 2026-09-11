<?php

declare(strict_types=1);

use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Models\Catalog\Recording;
use App\Support\Chart\DatabaseChartMetricObservationStore;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

uses(RefreshDatabase::class);

it('persists identical metric evidence idempotently and rehydrates its semantics', function (): void {
    $recording = Recording::factory()->create();
    $observedAt = new DateTimeImmutable('2026-09-09T00:00:00+00:00');
    $observation = new ChartMetricObservation(
        observationId: hash('sha256', 'stable-observation'),
        canonicalRecordingId: (string) $recording->getKey(),
        provider: 'youtube',
        providerItemId: 'abcdefghijk',
        metric: 'youtube_video_view_count',
        value: 123456,
        observedAt: $observedAt,
        metricUnit: 'views',
        metricSemanticsVersion: 'youtube-view-count-2026-08-24',
        fetchedAt: $observedAt,
        sourceReference: 'youtube:videos.list:abcdefghijk:statistics',
    );

    $store = app(DatabaseChartMetricObservationStore::class);
    $first = $store->appendMany([$observation]);
    $second = $store->appendMany([$observation]);

    expect(DB::table('chart_metric_observations')->count())->toBe(1)
        ->and($first)->toHaveCount(1)
        ->and($second)->toHaveCount(1)
        ->and($second[0]->observationId)->toBe($observation->observationId)
        ->and($second[0]->metricUnit)->toBe('views')
        ->and($second[0]->metricSemanticsVersion)->toBe('youtube-view-count-2026-08-24')
        ->and($second[0]->value)->toBe(123456);
});

it('fails closed when an observation id is reused for different evidence', function (): void {
    $recording = Recording::factory()->create();
    $observedAt = new DateTimeImmutable('2026-09-09T00:00:00+00:00');
    $id = hash('sha256', 'collision');
    $store = app(DatabaseChartMetricObservationStore::class);

    $store->appendMany([new ChartMetricObservation(
        observationId: $id,
        canonicalRecordingId: (string) $recording->getKey(),
        provider: 'youtube',
        providerItemId: 'abcdefghijk',
        metric: 'youtube_video_view_count',
        value: 100,
        observedAt: $observedAt,
        metricUnit: 'views',
        metricSemanticsVersion: 'youtube-view-count-2026-08-24',
    )]);

    expect(fn () => $store->appendMany([new ChartMetricObservation(
        observationId: $id,
        canonicalRecordingId: (string) $recording->getKey(),
        provider: 'youtube',
        providerItemId: 'abcdefghijk',
        metric: 'youtube_video_view_count',
        value: 101,
        observedAt: $observedAt,
        metricUnit: 'views',
        metricSemanticsVersion: 'youtube-view-count-2026-08-24',
    )]))->toThrow(RuntimeException::class, 'Metric observation id was reused for different evidence.');
});
