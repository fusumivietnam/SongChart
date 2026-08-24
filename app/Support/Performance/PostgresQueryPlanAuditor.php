<?php

declare(strict_types=1);

namespace App\Support\Performance;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class PostgresQueryPlanAuditor
{
    /** @return array<string, mixed> */
    public function baseline(): array
    {
        $path = base_path('docs/project/performance/postgres-query-plan-baseline.json');
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException('PostgreSQL query-plan baseline must decode to an object.');
        }

        return $decoded;
    }

    /**
     * @param  list<mixed>  $bindings
     * @return array<int, array<string, mixed>>
     */
    public function explain(string $sql, array $bindings = [], bool $analyze = false): array
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            throw new RuntimeException('Query-plan audit requires PostgreSQL.');
        }

        if ($analyze && app()->isProduction()) {
            throw new RuntimeException('EXPLAIN ANALYZE is forbidden in production.');
        }

        $options = $analyze
            ? 'ANALYZE, BUFFERS, FORMAT JSON'
            : 'BUFFERS, FORMAT JSON';

        /** @var list<object> $rows */
        $rows = DB::select("EXPLAIN ({$options}) {$sql}", $bindings);
        $plan = isset($rows[0]) ? ($rows[0]->{'QUERY PLAN'} ?? null) : null;
        if (! is_string($plan)) {
            throw new RuntimeException('PostgreSQL did not return a JSON query plan.');
        }

        $decoded = json_decode($plan, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    /** @return array<string, int> */
    public function rowCounts(): array
    {
        $baseline = $this->baseline();
        $queries = is_array($baseline['queries'] ?? null) ? $baseline['queries'] : [];
        $tables = [];
        foreach ($queries as $entry) {
            if (is_array($entry) && is_string($entry['table'] ?? null)) {
                $tables[$entry['table']] = true;
            }
        }

        $counts = [];
        foreach (array_keys($tables) as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        ksort($counts);

        return $counts;
    }
}
