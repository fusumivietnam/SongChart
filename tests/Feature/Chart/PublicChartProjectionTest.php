<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Application\Chart\Queries\PublicChartProjection;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Models\Catalog\Recording;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('projects ranked chart rows onto the same canonical public recording identity', function (): void {
    $recording = Recording::factory()->create([
        'title' => 'Golden Song',
        'slug' => 'golden-song',
    ]);

    $snapshotAt = new DateTimeImmutable('2026-09-09T00:00:00+00:00');
    $snapshot = (new BuildChartSnapshot)->handle('global-streams', 'streams', $snapshotAt, [
        new ChartMetricObservation(
            'provider-observation-1',
            (string) $recording->getKey(),
            'provider-a',
            'provider-item-1',
            'streams',
            100,
            new DateTimeImmutable('2026-09-08T23:00:00+00:00'),
        ),
    ]);

    $public = app(PublicChartProjection::class)->fromSnapshot($snapshot);

    expect($public['rows'])->toHaveCount(1)
        ->and($public['rows'][0]['canonical_recording_id'])->toBe((string) $recording->getKey())
        ->and($public['rows'][0]['title'])->toBe('Golden Song')
        ->and($public['rows'][0]['slug'])->toBe('golden-song')
        ->and($public['rows'][0]['url'])->toContain('golden-song')
        ->and($public['rows'][0]['observation_ids'])->toBe(['provider-observation-1']);
});
