# Stage 17.9.1 Task Contract

## Purpose
Correct PHPStan iterable-value contracts in `ConfigEnrichmentPlanner` without changing planner behavior, provider recipes, persistence, or runtime orchestration.

## Changes
- Declare `array<string, mixed>` for the enrichment recipe parameter consumed by `rows()`.
- Declare provider-state array shape `array<string, array{id: string, enabled: bool}>` for `need()`.
- Keep enrichment planning read-only and provider-neutral.

## Non-goals
- No migration.
- No provider API changes.
- No suppression or PHPStan level reduction.
- No canonical mutation changes.
