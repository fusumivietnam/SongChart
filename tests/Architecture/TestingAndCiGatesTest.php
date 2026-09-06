<?php

declare(strict_types=1);

it('registers every test layer in phpunit', function (): void {
    $configuration = file_get_contents(base_path('phpunit.xml'));

    expect($configuration)
        ->toContain('tests/Unit')
        ->toContain('tests/Architecture')
        ->toContain('tests/Feature');
});

it('keeps required reusable CI jobs and commands', function (): void {
    $workflow = file_get_contents(base_path('.github/workflows/tests.yml'));

    expect($workflow)
        ->toContain('workflow_call:')
        ->toContain('target_sha:')
        ->toContain('SONGCHART_CI_SHA: ${{ inputs.target_sha || github.sha }}')
        ->toContain('ref: ${{ env.SONGCHART_CI_SHA }}')
        ->toContain('quality:')
        ->toContain('tests-postgres:')
        ->toContain('frontend-build:')
        ->toContain('composer quality:verify')
        ->toContain('composer test:postgres')
        ->toContain('npm run build')
        ->toContain('touch .env')
        ->toContain('Preserve PostgreSQL failure evidence')
        ->toContain('storage/logs/postgres-test-last-failure.log')
        ->toContain('postgres-failure-${{ env.SONGCHART_CI_SHA }}-${{ github.run_attempt }}');

    expect(preg_match('/uses:\s*actions\/upload-artifact@[0-9a-f]{40}/m', (string) $workflow))->toBe(1)
        ->and(str_contains((string) $workflow, "pull_request:\n    branches:\n      - main"))->toBeFalse()
        ->and(str_contains((string) $workflow, 'tests-sqlite:'))->toBeFalse()
        ->and(str_contains((string) $workflow, 'composer test:sqlite'))->toBeFalse();
});
