<?php

declare(strict_types=1);

test('it keeps development progress out of README and in generated repository state', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));
    $compatibilityPointer = (string) file_get_contents(base_path('docs/project/DEVELOPMENT_STATE.md'));
    $generatedState = json_decode((string) file_get_contents(base_path('docs/project/generated/development-state.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($readme)
        ->not->toContain('Current stage:')
        ->not->toContain('## Current development stage')
        ->and($compatibilityPointer)->toContain('compatibility pointer')
        ->and($compatibilityPointer)->toContain('docs/project/generated/development-state.json')
        ->and($generatedState['generated_from_repository'] ?? false)->toBeTrue()
        ->and($generatedState['current_stage']['id'] ?? null)->toBeString();
});

test('it routes historical stage evidence through structured owners instead of requiring legacy stage files', function (): void {
    $history = (string) file_get_contents(base_path('docs/project/DEVELOPMENT_HISTORY.md'));
    $contract = json_decode((string) file_get_contents(base_path('docs/project/engineering/documentation-consolidation-contract.json')), true, flags: JSON_THROW_ON_ERROR);

    expect($history)->toContain('Development History')
        ->and($contract['target_owners']['accepted_chronology'] ?? null)->toBe('docs/project/DEVELOPMENT_HISTORY.md')
        ->and($contract['target_owners']['exact_historical_evidence'] ?? null)->toBe('Git commit, pull-request and release history');
});
