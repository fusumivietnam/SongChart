<?php

declare(strict_types=1);

it('keeps official-first engineering in the executable quality boundary', function (): void {
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, flags: JSON_THROW_ON_ERROR);

    expect(base_path('docs/project/docs/OFFICIAL_SOURCE_POLICY.md'))->toBeFile()
        ->and(base_path('docs/templates/TASK_CONTRACT_TEMPLATE.md'))->toBeFile()
        ->and(base_path('scripts/verify-official-sources.php'))->toBeFile()
        ->and($composer['scripts']['official-sources:verify'] ?? null)
        ->toBe('@php scripts/verify-official-sources.php')
        ->and($composer['scripts']['quality:verify'] ?? [])
        ->toContain('@official-sources:verify');
});

it('keeps the secondary agent file pointer-only', function (): void {
    $contents = (string) file_get_contents(base_path('docs/project/AGENTS.md'));

    expect($contents)
        ->toContain('Status: non-authoritative pointer.')
        ->toContain('../../AGENTS.md')
        ->not->toContain('## Nguyên tắc bắt buộc');
});
