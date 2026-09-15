<?php

declare(strict_types=1);

it('keeps shared edge caching bounded to anonymous safe reads', function (): void {
    $authority = json_decode(
        (string) file_get_contents(base_path('docs/project/domain/route-authority.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $edge = $authority['edge_delivery'];

    expect($edge['provider_mode'])->toBe('provider_neutral')
        ->and($edge['fallback'])->toBe('direct_dns_to_caddy')
        ->and($edge['automatic_infrastructure_mutation'])->toBeFalse()
        ->and($edge['shared_cache_methods'])->toBe(['GET', 'HEAD'])
        ->and($edge['classes']['private_or_mutating']['shared_cache'])->toBe('bypass')
        ->and($edge['classes']['dynamic_search']['shared_cache'])->toBe('bypass')
        ->and($edge['mandatory_bypass']['request_headers_present'])->toContain('Authorization')
        ->and($edge['mandatory_bypass']['cookies_present'])->toContain('songchart_session')
        ->and($edge['mandatory_bypass']['response_headers'])->toContain('Set-Cookie');

    $cacheablePatterns = [];
    foreach ($edge['classes'] as $class) {
        if (($class['shared_cache'] ?? 'bypass') !== 'bypass') {
            array_push($cacheablePatterns, ...($class['patterns'] ?? []));
        }
    }

    foreach (['/account', '/admin', '/development', '/search'] as $protectedPrefix) {
        expect(array_filter(
            $cacheablePatterns,
            static fn (string $pattern): bool => $pattern === $protectedPrefix
                || str_starts_with($pattern, $protectedPrefix.'/')
                || str_starts_with($pattern, $protectedPrefix.'*'),
        ))->toBeEmpty();
    }
});

it('requires Stage 24 evidence and measurable improvement before edge adoption', function (): void {
    $authority = json_decode(
        (string) file_get_contents(base_path('docs/project/domain/route-authority.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    $threshold = $authority['edge_delivery']['decision_thresholds']['investigate_edge_delivery'];
    $signals = array_column($threshold['stage_24_signals_any'], 'metric');

    expect($threshold['required_scale_scorecard_status'])->toBe(['investigate'])
        ->and($signals)->toContain('pulse_slow_events_15m', 'cache_hit_ratio')
        ->and($threshold['required_before_adoption'])->toContain(
            'public_request_volume_baseline',
            'public_p95_latency_baseline',
            'origin_request_rate_baseline',
        )
        ->and($threshold['expected_improvement']['public_p95_latency_percent_min'])->toBeGreaterThan(0)
        ->and($threshold['expected_improvement']['origin_request_rate_percent_min'])->toBeGreaterThan(0);
});
