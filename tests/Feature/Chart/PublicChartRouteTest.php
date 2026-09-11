<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Models\Catalog\Recording;
use App\Support\Chart\DatabaseChartSnapshotStore;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns 404 when a public chart has no persisted snapshot', function (): void {
    $this->get('/charts/global-streams')->assertNotFound();
});

it('renders the latest persisted chart using canonical recording identity and provenance metadata', function (): void {
    $recording = Recording::factory()->create([
        'title' => 'Golden Song',
        'slug' => 'golden-song',
    ]);

    $snapshot = (new BuildChartSnapshot)->handle(
        'global-streams',
        'streams',
        new DateTimeImmutable('2026-09-09T00:00:00+00:00'),
        [
            new ChartMetricObservation(
                'provider-observation-1',
                (string) $recording->getKey(),
                'provider-a',
                'provider-item-1',
                'streams',
                100,
                new DateTimeImmutable('2026-09-08T23:00:00+00:00'),
            ),
        ],
    );

    app(DatabaseChartSnapshotStore::class)->append($snapshot);

    $this->get('/charts/global-streams')
        ->assertOk()
        ->assertSee('Golden Song')
        ->assertSee('songchart-chart-v1')
        ->assertSee('golden-song');
});
