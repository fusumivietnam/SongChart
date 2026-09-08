<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Domain\Chart\DTO\ChartMetricObservation;
use DateTimeImmutable;
use InvalidArgumentException;

it('builds deterministic ranked rows with canonical and input provenance', function (): void {
    $snapshotAt = new DateTimeImmutable('2026-09-09T00:00:00+00:00');
    $observedAt = new DateTimeImmutable('2026-09-08T23:00:00+00:00');
    $builder = new BuildChartSnapshot;

    $snapshot = $builder->handle('global-streams', 'streams', $snapshotAt, [
        new ChartMetricObservation('obs-b1', 'recording-b', 'provider-b', 'item-b', 'streams', 150, $observedAt),
        new ChartMetricObservation('obs-a2', 'recording-a', 'provider-b', 'item-a-b', 'streams', 60, $observedAt),
        new ChartMetricObservation('obs-a1', 'recording-a', 'provider-a', 'item-a-a', 'streams', 100, $observedAt),
    ]);

    expect($snapshot->calculationVersion)->toBe(BuildChartSnapshot::CALCULATION_VERSION)
        ->and($snapshot->metric)->toBe('streams')
        ->and($snapshot->rows)->toHaveCount(2)
        ->and($snapshot->rows[0])->toMatchArray([
            'rank' => 1,
            'canonical_recording_id' => 'recording-a',
            'score' => 160.0,
            'observation_ids' => ['obs-a1', 'obs-a2'],
        ])
        ->and($snapshot->rows[1])->toMatchArray([
            'rank' => 2,
            'canonical_recording_id' => 'recording-b',
            'score' => 150.0,
            'observation_ids' => ['obs-b1'],
        ]);
});

it('rejects metric mixing and observations without complete provenance', function (): void {
    $snapshotAt = new DateTimeImmutable('2026-09-09T00:00:00+00:00');
    $observedAt = new DateTimeImmutable('2026-09-08T23:00:00+00:00');
    $builder = new BuildChartSnapshot;

    expect(fn () => $builder->handle('global-streams', 'streams', $snapshotAt, [
        new ChartMetricObservation('obs-1', 'recording-a', 'provider-a', 'item-a', 'views', 100, $observedAt),
    ]))->toThrow(InvalidArgumentException::class, 'All chart observations must use the requested metric.');

    expect(fn () => $builder->handle('global-streams', 'streams', $snapshotAt, [
        new ChartMetricObservation('', 'recording-a', 'provider-a', 'item-a', 'streams', 100, $observedAt),
    ]))->toThrow(InvalidArgumentException::class, 'Chart observations require canonical identity and provider provenance.');
});
