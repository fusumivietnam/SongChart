<?php

declare(strict_types=1);

test('it keeps development progress out of README and in DEVELOPMENT_STATE', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));
    $developmentState = (string) file_get_contents(base_path('docs/project/DEVELOPMENT_STATE.md'));

    expect($readme)
        ->not->toContain('Current stage:')
        ->not->toContain('## Current development stage')
        ->and($developmentState)->toContain('## Current stage');
});

test('it preserves historical Stage 11 closure evidence', function (): void {
    expect(base_path('docs/foundation/STAGE_11_8_TASK_CONTRACT.md'))->toBeFile();
});
