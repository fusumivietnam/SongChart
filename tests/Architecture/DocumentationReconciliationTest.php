<?php

declare(strict_types=1);

test('it keeps one current development stage in the repository readme', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));

    expect(preg_match_all('/^## Current development stage$/m', $readme))->toBe(1);
});

test('it publishes the reconciled Stage 11 baseline', function (): void {
    expect(base_path('docs/foundation/STAGE_11_FOUNDATION_BASELINE.md'))->toBeFile()
        ->and(base_path('docs/foundation/STAGE_11_8_TASK_CONTRACT.md'))->toBeFile();
});
