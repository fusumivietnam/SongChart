<?php

declare(strict_types=1);

it('registers every test layer in phpunit', function (): void {
    $configuration = file_get_contents(base_path('phpunit.xml'));

    expect($configuration)
        ->toContain('tests/Unit')
        ->toContain('tests/Architecture')
        ->toContain('tests/Feature');
});

it('keeps required CI jobs and commands', function (): void {
    $workflow = file_get_contents(base_path('.github/workflows/tests.yml'));

    expect($workflow)
        ->toContain('SONGCHART_CI_SHA: ${{ github.event.pull_request.head.sha || github.sha }}')
        ->toContain('ref: ${{ env.SONGCHART_CI_SHA }}')
        ->toContain('quality:')
        ->toContain('tests-postgres:')
        ->toContain('frontend-build:')
        ->toContain('composer quality:verify')
        ->toContain('composer test:postgres')
        ->toContain('npm run build')
        ->toContain('touch .env')
        ->toContain('Preserve PostgreSQL failure evidence')
        ->toContain('actions/upload-artifact@v4')
        ->toContain('storage/logs/postgres-test-last-failure.log')
        ->toContain('postgres-failure-${{ env.SONGCHART_CI_SHA }}-${{ github.run_attempt }}');

    expect(str_contains((string) $workflow, 'tests-sqlite:'))->toBeFalse()
        ->and(str_contains((string) $workflow, 'composer test:sqlite'))->toBeFalse();
});
