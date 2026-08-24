# Stage 17.9.3 Task Contract

## Purpose
Close the official-source governance gap introduced by the Stage 17.9.2 metadata-only corrective and restore canonical verification without changing application runtime behavior.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `scripts/verify-official-sources.php`
- `scripts/verify-repository-state.php`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- PostgreSQL 18 is the canonical database authority.
- Docker Compose remains governed by the existing isolated canonical verification workflow.

### Official external sources

No new external source is required. Stage 17.9.3 changes repository governance documentation only and does not introduce or modify provider/API behavior.

### Native capability assessment

- Use the existing `TASK_CONTRACT_TEMPLATE.md` section contract rather than inventing a parallel corrective format.
- Use `verify-official-sources.php` as the executable authority for required current-stage evidence sections.
- Use `verify-repository-state.php` as the executable authority for README/history/current-stage consistency.

### Custom implementation justification

No custom runtime implementation is required. The defect is a missing governance-document shape, so the correct fix is to repair the authoritative task contracts and current-stage metadata while leaving application code untouched.

## Changes

- Expand the Stage 17.9.2 task contract to contain all required official-source evidence sections.
- Add the Stage 17.9.3 task contract and validation report using the authoritative template structure.
- Record Stage 17.9.3 in development history.
- Advance the README current-stage pointer to Stage 17.9.3.

## Non-goals

- No application code changes.
- No migration.
- No provider/API changes.
- No enrichment planner behavior changes.
- No PHPStan suppression or verification weakening.

## Tests and verification

- `@php scripts/verify-official-sources.php`
- `@php scripts/verify-repository-state.php`
- Documentation/current-stage contract checks.
- Canonical Docker PHPStan/Pest/PostgreSQL verification remains the final authority.
