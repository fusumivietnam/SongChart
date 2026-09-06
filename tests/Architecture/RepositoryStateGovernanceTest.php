<?php

declare(strict_types=1);

it('keeps repository state reconciliation executable', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(base_path('scripts/verify-repository-state.php'))->toBeFile()
        ->and(base_path('scripts/project-state.php'))->toBeFile()
        ->and(base_path('docs/project/engineering/stage-plan.json'))->toBeFile()
        ->and(base_path('docs/project/generated/development-state.json'))->toBeFile()
        ->and(base_path('docs/project/generated/DEVELOPMENT_STATE.md'))->toBeFile()
        ->and(base_path('docs/project/DEVELOPMENT_STATE.md'))->toBeFile()
        ->and(base_path('docs/project/DEVELOPMENT_HISTORY.md'))->toBeFile()
        ->and($composer['scripts']['repository-state:verify'] ?? null)->toBe('@php scripts/verify-repository-state.php')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@repository-state:verify');
});

it('keeps generated development state as the current stage projection', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));
    $startHere = (string) file_get_contents(base_path('docs/START_HERE.md'));
    $compatibilityPointer = (string) file_get_contents(base_path('docs/project/DEVELOPMENT_STATE.md'));
    $generatedState = json_decode((string) file_get_contents(base_path('docs/project/generated/development-state.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($readme)->not->toContain('Current stage:')
        ->and($readme)->not->toContain('## Current development stage')
        ->and($startHere)->not->toContain('Current stage:')
        ->and($compatibilityPointer)->toContain('compatibility pointer')
        ->and($generatedState['generated_from_repository'] ?? false)->toBeTrue();

    expect($generatedState['current_stage']['id'] ?? null)
        ->toBeString()
        ->not->toBeEmpty()
        ->and($generatedState['current_stage']['task_contract'] ?? null)
        ->toBeString()
        ->not->toBeEmpty();
});
