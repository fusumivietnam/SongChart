<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Models\Catalog\Recording;
use App\Support\Chart\DatabaseChartSnapshotStore;
use App\Support\Chart\YouTubeViewCountObservationSource;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders a bounded unavailable state for a known chart without a persisted snapshot', function (): void {
    $this->get('/charts/youtube-video-views')
        ->assertOk()
        ->assertSee('Chưa có đủ bằng chứng')
        ->assertSee('Chưa thể công bố thứ hạng');
});

it('keeps unknown chart identifiers as 404', function (): void {
    $this->get('/charts/not-a-real-chart')->assertNotFound();
});

it('renders the latest persisted chart using canonical recording identity and provenance metadata', function (): void {
    $recording = Recording::factory()->create([
        'title' => 'Golden Song',
        'slug' => 'golden-song',
    ]);

    $snapshot = (new BuildChartSnapshot)->handle(
        'youtube-video-views',
        YouTubeViewCountObservationSource::METRIC,
        new DateTimeImmutable,
        [
            new ChartMetricObservation(
                'provider-observation-1',
                (string) $recording->getKey(),
                'youtube',
                'provider-item-1',
                YouTubeViewCountObservationSource::METRIC,
                123456,
                new DateTimeImmutable('-5 minutes'),
                YouTubeViewCountObservationSource::METRIC_UNIT,
                YouTubeViewCountObservationSource::SEMANTICS_VERSION,
                new DateTimeImmutable('-4 minutes'),
                'youtube:videos.list:provider-item-1:statistics',
            ),
        ],
    );

    app(DatabaseChartSnapshotStore::class)->append($snapshot);

    $this->get('/charts/youtube-video-views')
        ->assertOk()
        ->assertSee('Golden Song')
        ->assertSee('123.456')
        ->assertSee('Nguồn và provenance')
        ->assertSee('youtube:videos.list:provider-item-1:statistics')
        ->assertSee('golden-song')
        ->assertDontSee((string) $recording->getKey());
});

it('renders an observed zero as evidence instead of an unavailable state', function (): void {
    $recording = Recording::factory()->create([
        'title' => 'Observed Zero Song',
        'slug' => 'observed-zero-song',
    ]);

    $snapshot = (new BuildChartSnapshot)->handle(
        'youtube-video-views',
        YouTubeViewCountObservationSource::METRIC,
        new DateTimeImmutable,
        [
            new ChartMetricObservation(
                'provider-observation-zero',
                (string) $recording->getKey(),
                'youtube',
                'provider-item-zero',
                YouTubeViewCountObservationSource::METRIC,
                0,
                new DateTimeImmutable('-5 minutes'),
                YouTubeViewCountObservationSource::METRIC_UNIT,
                YouTubeViewCountObservationSource::SEMANTICS_VERSION,
            ),
        ],
    );

    app(DatabaseChartSnapshotStore::class)->append($snapshot);

    $this->get('/charts/youtube-video-views')
        ->assertOk()
        ->assertSee('Observed Zero Song')
        ->assertSee('Giá trị 0 đã được quan sát')
        ->assertDontSee('Chưa có đủ bằng chứng');
});
