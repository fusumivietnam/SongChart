<?php

declare(strict_types=1);

it('keeps repository state reconciliation executable', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(base_path('scripts/verify-repository-state.php'))->toBeFile()
        ->and(base_path('docs/project/DEVELOPMENT_HISTORY.md'))->toBeFile()
        ->and($composer['scripts']['repository-state:verify'] ?? null)->toBe('@php scripts/verify-repository-state.php')
        ->and($composer['scripts']['quality:verify'] ?? [])->toContain('@repository-state:verify');
});

it('keeps README as the only current stage pointer', function (): void {
    $readme = (string) file_get_contents(base_path('README.md'));
    $startHere = (string) file_get_contents(base_path('docs/START_HERE.md'));
    $history = (string) file_get_contents(base_path('docs/project/DEVELOPMENT_HISTORY.md'));

    $matched = preg_match(
        '/^Current stage:\s*\*\*([0-9]+(?:\.[0-9]+)+)\s+—\s+([^*]+)\*\*$/m',
        $readme,
        $current,
    );

    expect(preg_match_all('/^Current stage:/m', $readme))->toBe(1)
        ->and($matched)->toBe(1)
        ->and($startHere)->not->toContain('Current stage:');

    $stage = $current[1];
    $title = trim($current[2]);
    $stageToken = str_replace('.', '_', $stage);

    expect($history)->toContain("| {$stage} | {$title} |")
        ->and(base_path("docs/foundation/STAGE_{$stageToken}_TASK_CONTRACT.md"))->toBeFile()
        ->and(base_path("docs/foundation/STAGE_{$stageToken}_VALIDATION_REPORT.md"))->toBeFile();
});
