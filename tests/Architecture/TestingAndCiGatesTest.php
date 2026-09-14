<?php

declare(strict_types=1);

/**
 * Return the source block owned by a top-level GitHub Actions job.
 *
 * This intentionally avoids adding a YAML parser dependency just for repository
 * architecture tests while still asserting semantic ownership instead of merely
 * checking that a token appears somewhere in the workflow file.
 */
function workflowJobBlock(string $workflow, string $job): string
{
    $pattern = '/^  '.preg_quote($job, '/').":\n(?<body>(?:(?!^  [A-Za-z0-9_-]+:\n).*(?:\n|$))*)/m";

    if (preg_match($pattern, $workflow, $matches) !== 1) {
        return '';
    }

    return "  {$job}:\n".($matches['body'] ?? '');
}

it('registers every test layer in phpunit', function (): void {
    $configuration = file_get_contents(base_path('phpunit.xml'));

    expect($configuration)
        ->toContain('tests/Unit')
        ->toContain('tests/Architecture')
        ->toContain('tests/Feature');
});

it('keeps required reusable CI jobs and commands under their owning jobs', function (): void {
    $workflow = (string) file_get_contents(base_path('.github/workflows/tests.yml'));
    $quality = workflowJobBlock($workflow, 'quality');
    $postgres = workflowJobBlock($workflow, 'tests-postgres');
    $browser = workflowJobBlock($workflow, 'browser-smoke');
    $frontend = workflowJobBlock($workflow, 'frontend-build');
    $classify = workflowJobBlock($workflow, 'classify');

    expect($workflow)
        ->toContain('workflow_call:')
        ->toContain('target_sha:')
        ->toContain('SONGCHART_CI_SHA: ${{ inputs.target_sha || github.sha }}')
        ->and($quality)
        ->not->toBe('')
        ->toContain('ref: ${{ env.SONGCHART_CI_SHA }}')
        ->toContain('composer quality:verify')
        ->and($postgres)
        ->not->toBe('')
        ->toContain('ref: ${{ env.SONGCHART_CI_SHA }}')
        ->toContain('SONGCHART_DEVELOPMENT_DATABASE: songchart_ci_development')
        ->toContain('TEST_PGSQL_DATABASE: songchart_test')
        ->toContain('composer test:postgres')
        ->toContain('postgres-test-result.json')
        ->toContain('postgres_phase')
        ->toContain('postgres_failure_class')
        ->and($browser)
        ->not->toBe('')
        ->toContain('ref: ${{ env.SONGCHART_CI_SHA }}')
        ->toContain('Run desktop/mobile browser smoke')
        ->and($frontend)
        ->not->toBe('')
        ->toContain('ref: ${{ env.SONGCHART_CI_SHA }}')
        ->toContain('npm run build')
        ->and($classify)
        ->not->toBe('')
        ->toContain('needs.tests-postgres.outputs.postgres_phase')
        ->toContain('needs.tests-postgres.outputs.postgres_failure_class')
        ->toContain('songchart-verification-result.json');

    expect($postgres)
        ->not->toContain('DB_DATABASE: songchart_ci_development')
        ->not->toContain('DB_USERNAME: songchart_test')
        ->not->toContain('DB_PASSWORD: songchart_test_only');

    expect(preg_match('/uses:\s*actions\/upload-artifact@[0-9a-f]{40}/m', $workflow))->toBe(1)
        ->and(str_contains($workflow, "pull_request:\n    branches:\n      - main"))->toBeFalse()
        ->and(str_contains($workflow, 'tests-sqlite:'))->toBeFalse()
        ->and(str_contains($workflow, 'composer test:sqlite'))->toBeFalse();
});
