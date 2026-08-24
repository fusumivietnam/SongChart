# Stage 17.6.4 Task Contract

## Scope

Close the six PostgreSQL feature-test regressions exposed after Stage 17.6.3 without weakening test, static-analysis, routing, or provider boundaries.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/domain/domain-contracts.json`
- `routes/web.php`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- PostgreSQL 18 is the canonical database verification authority.

### Official external sources

- No new external API behavior is introduced by this corrective.
- Existing Laravel routing, Eloquent cast, and MusicBrainz contracts remain authoritative.

### Native capability assessment

- Use Laravel dependency injection and existing Eloquent models/read paths.
- Use `getRawOriginal()` when comparing enum-cast persisted discriminator values.

### Custom implementation justification

- Canonical short aliases must bypass the optional demo search implementation because they address persisted canonical entities, not demo search fixtures.
- No new framework, package, or parallel persistence path is introduced.

## Tests and verification

- Re-run the six previously failing PostgreSQL feature tests.
- Run architecture, taxonomy, repository-state, official-source, repository-compiler, PHPStan, and canonical verification.

## Acceptance criteria

- `/artist/{slug}`, `/release/{slug}`, and `/recording/{slug}` resolve persisted canonical entities when demo search is enabled.
- Recording Artist Credit and Work relationship mutation does not cast enum objects to strings.
- Development MusicBrainz workbench assertion is stable.
- No suppression or weaker verification configuration is added.
