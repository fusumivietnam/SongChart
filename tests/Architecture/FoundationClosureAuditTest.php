<?php

declare(strict_types=1);

it('keeps the foundation closure audit in the quality chain', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($composer['scripts']['foundation:audit'] ?? null)
        ->toBe('@php scripts/verify-foundation-closure.php')
        ->and($composer['scripts']['foundation:release'] ?? null)
        ->toBe('@php scripts/verify-foundation-closure.php --release')
        ->and($composer['scripts']['quality:verify'] ?? [])
        ->toContain('@foundation:audit');
});

it('publishes the closure audit and task contract', function (): void {
    expect(base_path('docs/foundation/STAGE_11_9_TASK_CONTRACT.md'))->toBeFile()
        ->and(base_path('docs/foundation/STAGE_11_9_FOUNDATION_CLOSURE_AUDIT.md'))->toBeFile();
});
