<?php

declare(strict_types=1);

it('keeps PostgreSQL verification evidence phase-aware and machine-readable', function (): void {
    $runner = (string) file_get_contents(base_path('scripts/run-database-tests.php'));

    expect($runner)
        ->toContain('postgres-test-result.json')
        ->toContain('[SongChart verification result]')
        ->toContain("'phase' => $phase")
        ->toContain("'failure_class' => $failureClass")
        ->toContain("'failed_test' => $failedTest")
        ->toContain("'safety-precheck'")
        ->toContain("'migration'")
        ->toContain("'safety-postcheck'")
        ->toContain("'test'")
        ->toContain("'database-safety'")
        ->toContain("'database-error'")
        ->toContain("'assertion'")
        ->toContain("'test-process'");
});

it('keeps the PostgreSQL failure artifact as the bounded human and automation entrypoint', function (): void {
    $workflow = (string) file_get_contents(base_path('.github/workflows/tests.yml'));

    $start = strpos($workflow, "  tests-postgres:\n");
    $end = $start === false ? false : strpos($workflow, "\n  browser-smoke:\n", $start);
    $postgresJob = ($start !== false && $end !== false)
        ? substr($workflow, $start, $end - $start)
        : '';

    expect($postgresJob)
        ->not->toBe('')
        ->toContain('composer test:postgres')
        ->toContain('Summarize PostgreSQL failure')
        ->toContain('storage/logs/postgres-test-last-failure.log')
        ->toContain('Preserve PostgreSQL failure evidence');
});
