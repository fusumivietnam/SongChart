<?php

declare(strict_types=1);

it('keeps Stage 25 external observability evidence gated and optional', function (): void {
    $evaluation = (string) file_get_contents(base_path('docs/operations/external-observability-evaluation.md'));
    $decision = config('songchart.operational_intelligence.external_observability');

    expect($evaluation)
        ->toContain('deferred_no_demonstrated_gap')
        ->toContain('Privacy and retention')
        ->toContain('Cost and cardinality')
        ->toContain('Exit and fallback')
        ->toContain('must not fail application requests')
        ->and($decision['decision'] ?? null)->toBe('deferred')
        ->and($decision['reason'] ?? '')->toContain('adopt external APM only after a measured gap is recorded');
});

it('does not authorize automatic infrastructure mutation through the Stage 25 observability evaluation', function (): void {
    $evaluation = (string) file_get_contents(base_path('docs/operations/external-observability-evaluation.md'));

    expect($evaluation)
        ->toContain('No external APM package, agent, sidecar, proxy, credential, network dependency, or automatic infrastructure mutation is authorized')
        ->toContain('External APM is always an optimization/visibility layer, never the runtime or domain authority');
});
