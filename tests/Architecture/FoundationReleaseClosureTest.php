<?php

declare(strict_types=1);

it('keeps release lock and closure commands in Composer', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);
    $scripts = $composer['scripts'] ?? [];

    $topology = json_decode((string) file_get_contents(base_path('docs/project/engineering/verification-topology.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($scripts['locks:verify'] ?? null)
        ->toBe('@php scripts/verify-release-locks.php')
        ->and($scripts)->not->toHaveKey('release:verify')
        ->and($scripts['canonical:verify'] ?? null)
        ->toBe($topology['lanes']['canonical']['ordered_steps'])
        ->toContain('@locks:verify', '@foundation:release');
});

it('keeps CI on strict lockfile installation', function (): void {
    $workflow = (string) file_get_contents(base_path('.github/workflows/tests.yml'));

    expect($workflow)
        ->toContain('composer validate --strict')
        ->toContain('npm ci --no-audit --no-fund')
        ->not->toContain('npm install --no-audit --no-fund');
});

it('keeps the Stage 11 release closure authorities available', function (): void {
    expect(base_path('docs/foundation/STAGE_11_10_TASK_CONTRACT.md'))->toBeFile()
        ->and(base_path('docs/foundation/STAGE_11_10_FOUNDATION_RELEASE_CLOSURE.md'))->toBeFile();
});
