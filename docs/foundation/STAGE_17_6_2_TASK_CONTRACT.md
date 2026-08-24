# Stage 17.6.2 — PHPStan Type Contract Corrective

Status: implementation candidate.

## Goal

Restore PHPStan closure after Stage 17.6 relationship expansion exposed ambiguous enum-backed Eloquent attributes, an exhaustively narrowed public-catalog branch, a provider-rate branch that became statically constant, and malformed iterable PHPDoc.

## Non-goals

- no MusicBrainz behavior or API scope changes;
- no schema migration;
- no route or authorization changes;
- no PHPStan ignores, baseline expansion or weaker `treatPhpDocTypesAsCertain` configuration.

## Acceptance criteria

- all 15 reported PHPStan errors are removed by code/type corrections;
- enum-backed Eloquent values are normalized from raw storage where static type inference is ambiguous;
- public catalog branching no longer relies on an exhaustively narrowed enum `match`;
- provider request gate handles the post-`None` minimum-interval path without an always-true comparison;
- MusicBrainz Artist Credit relationship PHPDoc declares iterable value types correctly;
- existing Stage 17.6 behavior is preserved.

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

PHP enum values, Eloquent raw attributes, explicit array maps and existing Laravel model APIs are sufficient to make the types unambiguous.

### Custom implementation justification

No new abstraction is introduced. The corrective only makes existing runtime semantics explicit to PHPStan.

## Tests and verification

- PHP syntax sweep;
- PHPStan full analysis on canonical Docker target;
- architecture/repository/static governance verification;
- `verify-songchart.bat` for canonical closure.

## Rollback

Restore Stage 17.6.1 files if necessary. No migration rollback is required.
