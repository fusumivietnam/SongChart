<?php

declare(strict_types=1);

it('keeps repository state reconciliation executable', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(base_path('scripts/verify-repository-state.php'))->toBeFile()
        ->and(base_path('docs/project/DEVELOPMENT_STATE.md'))->toBeFile()
        ->and(base_path('docs/project/DEVELOPMENT_HISTORY.md'))->toBeFile()
        ->and($composer['scripts']['repository-state:verify'] ?? null)->toBe('@php scripts/verify-repository-state.php')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@repository-state:verify');
});

it('keeps DEVELOPMENT_STATE as the current stage authority', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));
    $startHere = (string) file_get_contents(base_path('docs/START_HERE.md'));
    $developmentState = (string) file_get_contents(base_path('docs/project/DEVELOPMENT_STATE.md'));

    $matched = preg_match(
        '/^- Stage\s+`([0-9]+(?:\.[0-9]+)+)\s+—\s+([^`]+)`$/m',
        $developmentState,
        $current,
    );

    expect($readme)->not->toContain('Current stage:')
        ->and($readme)->not->toContain('## Current development stage')
        ->and($startHere)->not->toContain('Current stage:')
        ->and($matched)->toBe(1);

    $stage = $current[1];
    $stageToken = str_replace('.', '_', $stage);

    expect(base_path("docs/foundation/STAGE_{$stageToken}_TASK_CONTRACT.md"))->toBeFile()
        ->and(base_path("docs/foundation/STAGE_{$stageToken}_VALIDATION_REPORT.md"))->toBeFile();
});
