<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Application\Chart\PlanChartRecompute;
use App\Application\Chart\Queries\RecordingDataTrace;
use App\Application\Chart\RefreshYouTubeViewChart;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Chart\ChartDefinitionRegistry;
use App\Support\Chart\DatabaseChartMetricObservationStore;
use App\Support\Chart\DatabaseChartSnapshotStore;
use App\Support\Chart\YouTubeViewCountObservationSource;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('selects chart recomputation from declared canonical dependencies', function (): void {
    $registry = app(ChartDefinitionRegistry::class);
    $definition = $registry->get(RefreshYouTubeViewChart::CHART_ID);
    $planner = app(PlanChartRecompute::class);

    expect($definition['metric'])->toBe(YouTubeViewCountObservationSource::METRIC)
        ->and($definition['metric_semantics_version'])->toBe(YouTubeViewCountObservationSource::SEMANTICS_VERSION)
        ->and($planner->forCanonicalChange(EntityType::Recording))->toContain(RefreshYouTubeViewChart::CHART_ID)
        ->and($planner->forCanonicalChange(EntityType::Artist))->toBe([]);
});

it('traces one recording through provider evidence metric history chart snapshot and freshness', function (): void {
    $provider = Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create([
        'title' => 'Traceable Song',
        'slug' => 'traceable-song',
    ]);
    ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'abcdefghijk',
        'url' => 'https://www.youtube.com/watch?v=abcdefghijk',
        'title' => 'Traceable Song',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 100,
        'review_state' => 'approved',
        'verified_at' => now(),
        'last_checked_at' => now(),
    ]);

    $observedAt = new DateTimeImmutable('2026-09-09T00:00:00+00:00');
    $snapshotAt = $observedAt->modify('+1 second');
    $observation = new ChartMetricObservation(
        observationId: hash('sha256', 'trace-observation'),
        canonicalRecordingId: (string) $recording->getKey(),
        provider: 'youtube',
        providerItemId: 'abcdefghijk',
        metric: YouTubeViewCountObservationSource::METRIC,
        value: 123456,
        observedAt: $observedAt,
        metricUnit: YouTubeViewCountObservationSource::METRIC_UNIT,
        metricSemanticsVersion: YouTubeViewCountObservationSource::SEMANTICS_VERSION,
        fetchedAt: $observedAt,
        sourceReference: 'youtube:videos.list:abcdefghijk:statistics',
    );
    $persisted = app(DatabaseChartMetricObservationStore::class)->appendMany([$observation]);
    $snapshot = (new BuildChartSnapshot)->handle(
        RefreshYouTubeViewChart::CHART_ID,
        YouTubeViewCountObservationSource::METRIC,
        $snapshotAt,
        $persisted,
    );
    app(DatabaseChartSnapshotStore::class)->append($snapshot);

    $trace = app(RecordingDataTrace::class)->handle((string) $recording->getKey());

    expect($trace['entity']['id'])->toBe((string) $recording->getKey())
        ->and($trace['entity']['public_url'])->toContain('/recordings/traceable-song')
        ->and($trace['provider_destinations'])->toHaveCount(1)
        ->and($trace['charts'])->toHaveCount(1)
        ->and($trace['charts'][0]['latest_observation']['observation_id'])->toBe($observation->observationId)
        ->and($trace['charts'][0]['observation_freshness']['state'])->toBe('fresh')
        ->and($trace['charts'][0]['latest_snapshot']['row']['canonical_recording_id'])->toBe((string) $recording->getKey())
        ->and($trace['charts'][0]['snapshot_freshness']['state'])->toBe('fresh');

    $this->artisan('songchart:data:trace', [
        'recording' => (string) $recording->getKey(),
        '--json' => true,
    ])->assertSuccessful();
});
