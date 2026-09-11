<?php

declare(strict_types=1);

use App\Application\Chart\BuildChartSnapshot;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Support\Chart\DatabaseChartSnapshotStore;
use DateTimeImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('persists chart snapshots idempotently by calculation version and input fingerprint', function (): void {
    $snapshotAt = new DateTimeImmutable('2026-09-09T00:00:00+00:00');
    $snapshot = (new BuildChartSnapshot)->handle('global-streams', 'streams', $snapshotAt, [
        new ChartMetricObservation(
            'observation-1',
            'recording-1',
            'provider-a',
            'provider-item-1',
            'streams',
            100,
            new DateTimeImmutable('2026-09-08T23:00:00+00:00'),
        ),
    ]);

    $store = app(DatabaseChartSnapshotStore::class);
    $first = $store->append($snapshot);
    $second = $store->append($snapshot);
    $latest = $store->latest('global-streams');

    expect($second)->toBe($first)
        ->and(DB::table('chart_snapshots')->count())->toBe(1)
        ->and($latest)->not->toBeNull()
        ->and($latest?->inputFingerprint)->toBe($snapshot->inputFingerprint)
        ->and($latest?->rows[0]['observations'][0]['observation_id'])->toBe('observation-1');
});
