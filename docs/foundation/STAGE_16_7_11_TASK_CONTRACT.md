# Stage 16.7.11 — PostgreSQL Query Plan & Index Baseline

## Authority and official sources
### Repository authorities
- `docs/project/performance/performance-contracts.json`
- `docs/project/performance/postgres-query-plan-baseline.json`
- current migrations under `database/migrations/`
- admin read models under `app/Support/Admin/`

### Installed versions
Use committed Composer constraints and target-machine lockfiles as version authority.

### Official external sources
PostgreSQL EXPLAIN/ANALYZE semantics and Laravel database/query-builder behavior are external implementation references; no speculative index is accepted without target PostgreSQL evidence.

### Native capability assessment
PostgreSQL already exposes `EXPLAIN (ANALYZE, BUFFERS, FORMAT JSON)` and Laravel exposes the active connection/query builder. No external profiler dependency is required for the baseline.

### Custom implementation justification
SongChart needs a machine-readable mapping between actual admin query shapes and current/candidate indexes so future migrations are evidence-driven and reviewable.

## Use case
Establish repeatable PostgreSQL query-plan and index evidence before provider mutation/live-provider stages.

## Exact reads
- existing migration index declarations
- performance/use-case contracts
- PostgreSQL table row counts during manual audit
- PostgreSQL query plans only when explicitly requested

## Exact writes
None to application data or schema. Stage 16.7.11 does not add indexes automatically.

## Null and identifier semantics
No identifier semantics change.

## Routes
None.

## Expected files
- `docs/project/performance/postgres-query-plan-baseline.json`
- `app/Support/Performance/PostgresQueryPlanAuditor.php`
- `app/Console/Commands/AuditPostgresQueryPlansCommand.php`
- `scripts/verify-postgres-query-plan-baseline.php`
- `tests/Architecture/PostgresQueryPlanBaselineTest.php`

## Allowed incidental files
- `composer.json`
- repository stage/history/documentation metadata

## Scope deviations
None. Candidate indexes remain `evidence_required`; index migrations are deferred until target PostgreSQL plans are captured and reviewed.

## Tests and verification
- `composer postgres-query-plans:verify`
- `composer quality:verify`
- `php artisan test tests/Architecture/PostgresQueryPlanBaselineTest.php`
- manual target audit: `php artisan songchart:audit-postgres-query-plans`
- optional non-production analysis: `php artisan songchart:audit-postgres-query-plans --analyze`
