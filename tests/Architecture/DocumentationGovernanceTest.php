<?php

declare(strict_types=1);

it('keeps the documentation governance authorities available', function (): void {
    expect(base_path('docs/DOCUMENTATION_GOVERNANCE.md'))->toBeFile()
        ->and(base_path('docs/DOCUMENTATION_INDEX.md'))->toBeFile()
        ->and(base_path('docs/foundation/STAGE_11_2_TASK_CONTRACT.md'))->toBeFile()
        ->and(base_path('docs/project/docs/adr/ADR-004-documentation-governance.md'))->toBeFile();
});

it('keeps documentation verification in the release command', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    $topology = json_decode((string) file_get_contents(base_path('docs/project/engineering/verification-topology.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($composer['scripts']['docs:verify'] ?? null)
        ->toBe('@php scripts/verify-documentation.php')
        ->and($composer['scripts']['quality:verify'] ?? [])
        ->toContain('@docs:verify')
        ->and($composer['scripts'])->not->toHaveKey('verify')
        ->and($composer['scripts']['stage:verify'] ?? null)
        ->toBe($topology['lanes']['stage']['ordered_steps']);
});

it('does not create duplicate root authority documents', function (string $filename): void {
    expect(base_path($filename))->not->toBeFile();
})->with([
    'ARCHITECTURE.md',
    'SECURITY.md',
    'WORKFLOW.md',
    'TESTING.md',
    'DECISIONS.md',
]);
