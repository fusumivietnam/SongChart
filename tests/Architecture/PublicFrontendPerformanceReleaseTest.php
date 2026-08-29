<?php

declare(strict_types=1);

it('guards the public frontend performance release contract', function (): void {
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/performance/performance-contracts.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );
    $verifier = (string) file_get_contents(base_path('scripts/verify-performance-baseline.php'));

    expect($contract['public_frontend_release'] ?? null)
        ->toBe([
            'third_party_blocking_assets' => false,
            'images_require_dimensions' => true,
            'reduced_motion_required' => true,
            'mobile_safe_area_required' => true,
        ]);

    expect($verifier)
        ->toContain('third-party blocking script or stylesheet assets')
        ->toContain('image without explicit width and height attributes')
        ->toContain('@media (prefers-reduced-motion: reduce)')
        ->toContain('env(safe-area-inset-bottom)');
});
