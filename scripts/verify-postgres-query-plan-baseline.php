<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];
$path = $root.'/docs/project/performance/postgres-query-plan-baseline.json';
$composerPath = $root.'/composer.json';
$auditorPath = $root.'/app/Support/Performance/PostgresQueryPlanAuditor.php';
$commandPath = $root.'/app/Console/Commands/AuditPostgresQueryPlansCommand.php';

foreach ([$path, $composerPath, $auditorPath, $commandPath] as $required) {
    if (! is_file($required)) {
        $errors[] = 'Missing required Stage 16.7.11 file: '.substr($required, strlen($root) + 1);
    }
}

if ($errors === []) {
    $baseline = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    $queries = is_array($baseline['queries'] ?? null) ? $baseline['queries'] : [];
    if ($queries === []) {
        $errors[] = 'Query-plan baseline must declare at least one query shape.';
    }
    foreach ($queries as $key => $entry) {
        if (! is_array($entry) || ! is_string($entry['table'] ?? null) || ! is_string($entry['shape'] ?? null)) {
            $errors[] = "Query-plan entry {$key} must declare table and shape.";

            continue;
        }
        foreach (($entry['candidate_indexes'] ?? []) as $candidate) {
            if (! is_array($candidate) || ($candidate['evidence_required'] ?? null) !== true) {
                $errors[] = "Candidate index in {$key} must remain evidence_required until PostgreSQL evidence exists.";
            }
        }
    }

    $auditor = (string) file_get_contents($auditorPath);
    if (! str_contains($auditor, "getDriverName() !== 'pgsql'")) {
        $errors[] = 'PostgresQueryPlanAuditor must reject non-PostgreSQL connections.';
    }
    if (! str_contains($auditor, 'app()->isProduction()')) {
        $errors[] = 'PostgresQueryPlanAuditor must forbid ANALYZE in production.';
    }

    $composer = json_decode((string) file_get_contents($composerPath), true, 512, JSON_THROW_ON_ERROR);
    $scripts = is_array($composer['scripts'] ?? null) ? $composer['scripts'] : [];
    if (($scripts['postgres-query-plans:verify'] ?? null) !== '@php scripts/verify-postgres-query-plan-baseline.php') {
        $errors[] = 'composer postgres-query-plans:verify is missing.';
    }
    $quality = $scripts['quality:verify'] ?? [];
    if (! is_array($quality) || ! in_array('@postgres-query-plans:verify', $quality, true)) {
        $errors[] = 'quality:verify must include @postgres-query-plans:verify.';
    }
}

if ($errors !== []) {
    fwrite(STDERR, "PostgreSQL query-plan baseline verification failed:\n- ".implode("\n- ", $errors)."\n");
    exit(1);
}

fwrite(STDOUT, "PostgreSQL query-plan baseline verification passed.\n");
