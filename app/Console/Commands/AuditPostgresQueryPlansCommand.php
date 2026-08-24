<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Performance\PostgresQueryPlanAuditor;
use Illuminate\Console\Command;

final class AuditPostgresQueryPlansCommand extends Command
{
    protected $signature = 'songchart:audit-postgres-query-plans {--analyze : Execute EXPLAIN ANALYZE on the target PostgreSQL database}';

    protected $description = 'Inspect PostgreSQL query-plan baseline and dataset readiness without adding speculative indexes';

    public function handle(PostgresQueryPlanAuditor $auditor): int
    {
        $baseline = $auditor->baseline();
        $this->info('PostgreSQL query-plan baseline v'.(string) ($baseline['version'] ?? '?'));

        if (config('database.default') !== 'pgsql') {
            $this->error('Current database connection is not PostgreSQL.');

            return self::FAILURE;
        }

        $counts = $auditor->rowCounts();
        $this->table(['Table', 'Rows'], array_map(
            static fn (string $table, int $count): array => [$table, $count],
            array_keys($counts),
            array_values($counts),
        ));

        $this->newLine();
        $this->line('Candidate indexes remain evidence_required until EXPLAIN output is reviewed.');
        if (! (bool) $this->option('analyze')) {
            $this->comment('Re-run with --analyze only on a non-production PostgreSQL dataset representative of expected scale.');
        } else {
            $this->warn('ANALYZE mode enabled. Stage 16.7.11 does not auto-create indexes; capture and review plans before any migration.');
        }

        return self::SUCCESS;
    }
}
