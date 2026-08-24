<?php

declare(strict_types=1);

it('keeps changed authorities explicit and dependency-closed', function (): void {
    $registry = json_decode((string) file_get_contents(base_path('docs/project/governance/authority-dependencies.json')), true, 512, JSON_THROW_ON_ERROR);
    $composer = json_decode((string) file_get_contents(base_path('composer.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($registry['authorities']['database-test-authority']['state'])->toBe('postgresql-only-release-authority')
        ->and($registry['authorities']['admin-dashboard-mode']['state'])->toBe('attention-first')
        ->and($composer['scripts']['quality:verify'])->toContain('@authority-dependencies:verify');
});
