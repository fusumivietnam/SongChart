<?php

declare(strict_types=1);

use App\Support\Operations\OperationalIntelligenceSnapshot;

it('keeps unavailable operational evidence distinct from zero', function (): void {
    config()->set('songchart.operational_intelligence.metrics', [
        'queue_depth' => [
            'dimension' => 'queue',
            'unit' => 'jobs',
            'owner' => 'laravel-horizon',
            'direction' => 'higher_is_worse',
            'warning' => 100,
            'critical' => 500,
        ],
        'cache_hit_ratio' => [
            'dimension' => 'cache',
            'unit' => 'ratio',
            'owner' => 'laravel-pulse',
            'direction' => 'lower_is_worse',
            'warning' => 0.8,
            'critical' => 0.5,
        ],
    ]);
    config()->set('songchart.operational_intelligence.required_scale_dimensions', ['queue', 'cache']);

    $result = app(OperationalIntelligenceSnapshot::class)->build([
        'queue_depth' => 0,
        'cache_hit_ratio' => null,
    ]);

    expect($result['metrics']['queue_depth']['status'])->toBe('healthy')
        ->and($result['metrics']['queue_depth']['value'])->toBe(0)
        ->and($result['metrics']['cache_hit_ratio']['status'])->toBe('unavailable')
        ->and($result['metrics']['cache_hit_ratio']['value'])->toBeNull()
        ->and($result['scale_scorecard']['status'])->toBe('insufficient_evidence')
        ->and($result['scale_scorecard']['missing_dimensions'])->toBe(['cache']);
});

it('fails closed when operational thresholds are breached', function (): void {
    config()->set('songchart.operational_intelligence.metrics', [
        'database_probe_ms' => [
            'dimension' => 'database',
            'unit' => 'ms',
            'owner' => 'postgresql-runtime',
            'direction' => 'higher_is_worse',
            'warning' => 50,
            'critical' => 200,
        ],
        'cache_hit_ratio' => [
            'dimension' => 'cache',
            'unit' => 'ratio',
            'owner' => 'laravel-pulse',
            'direction' => 'lower_is_worse',
            'warning' => 0.8,
            'critical' => 0.5,
        ],
    ]);
    config()->set('songchart.operational_intelligence.required_scale_dimensions', ['database', 'cache']);

    $result = app(OperationalIntelligenceSnapshot::class)->build([
        'database_probe_ms' => 250,
        'cache_hit_ratio' => 0.9,
    ]);

    expect($result['status'])->toBe('critical')
        ->and($result['metrics']['database_probe_ms']['status'])->toBe('critical')
        ->and($result['metrics']['cache_hit_ratio']['status'])->toBe('healthy')
        ->and($result['scale_scorecard']['status'])->toBe('stabilize_before_scaling')
        ->and($result['scale_scorecard']['automatic_infrastructure_mutation'])->toBeFalse();
});

it('does not recommend external observability without a demonstrated gap', function (): void {
    config()->set('songchart.operational_intelligence.metrics', []);
    config()->set('songchart.operational_intelligence.required_scale_dimensions', []);
    config()->set('songchart.operational_intelligence.external_observability', [
        'decision' => 'deferred',
        'reason' => 'Internal evidence remains sufficient.',
    ]);

    $result = app(OperationalIntelligenceSnapshot::class)->build([]);

    expect($result['external_observability'])->toBe([
        'decision' => 'deferred',
        'reason' => 'Internal evidence remains sufficient.',
    ]);
});
