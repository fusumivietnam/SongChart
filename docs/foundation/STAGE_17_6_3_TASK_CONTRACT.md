# Stage 17.6.3 — PHPStan Exhaustive Map Corrective

Status: implementation candidate.

## Goal

Close the final PHPStan error left after Stage 17.6.2 by removing an impossible null-coalescing fallback from the exhaustive public-catalog title-column map.

## Non-goals

- no runtime behavior changes;
- no schema migration;
- no route, authorization, provider or API changes;
- no PHPStan ignores or configuration weakening.

## Acceptance criteria

- `PublicCatalogReadModel` indexes the exhaustive entity-type title-column map directly;
- the reported `nullCoalesce.offset` error is removed;
- Stage 17.6 MusicBrainz relationship behavior remains unchanged;
- canonical Docker verification is able to proceed beyond PHPStan.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `phpstan.neon`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

### Installed versions

No package/runtime version changes. Target verification remains PHP 8.5 / Laravel 13 under the repository Docker authority.

### Official external sources

No external API behavior is changed by this corrective.

### Native capability assessment

The existing exhaustive PHP array map over the validated `EntityType` union is sufficient; no fallback is required.

### Custom implementation justification

No custom abstraction is added. The corrective removes a statically impossible fallback only.

## Tests and verification

- PHP syntax for the changed PHP file;
- PHPStan full analysis on the canonical Docker target;
- repository/static governance verification;
- `verify-songchart.bat` for canonical closure.

## Rollback

Restore the Stage 17.6.2 version of `PublicCatalogReadModel.php`. No migration rollback is required.
