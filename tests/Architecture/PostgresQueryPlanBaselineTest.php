<?php

declare(strict_types=1);

it('keeps PostgreSQL index changes evidence first', function (): void {
    $baseline = json_decode((string) file_get_contents(base_path('docs/project/performance/postgres-query-plan-baseline.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($baseline['policy']['require_explain_before_new_index'] ?? false)->toBeTrue()
        ->and($baseline['policy']['production_analyze_forbidden'] ?? false)->toBeTrue();

    foreach ($baseline['queries'] as $entry) {
        foreach ($entry['candidate_indexes'] ?? [] as $candidate) {
            expect($candidate['evidence_required'] ?? false)->toBeTrue();
        }
    }
});

it('keeps the auditor PostgreSQL-only and production safe', function (): void {
    $source = (string) file_get_contents(base_path('app/Support/Performance/PostgresQueryPlanAuditor.php'));

    expect($source)->toContain("getDriverName() !== 'pgsql'")
        ->and($source)->toContain('app()->isProduction()')
        ->and($source)->toContain('EXPLAIN (');
});
