# Stage 17.8.1 Task Contract

## Scope

Close the Stage 17.8 public-detail runtime regressions and introduce subtype-aware public Artist taxonomy without changing canonical MusicBrainz Artist identity semantics.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/domain/URL_CONTRACTS.md`
- `docs/project/docs/URL_SEO.md`
- `routes/web.php`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- PostgreSQL 18 is the canonical database authority.

### Official external sources

No external provider contract changes are introduced. Existing MusicBrainz Artist semantics remain authoritative: SongChart keeps person/group/orchestra/choir records under the canonical Artist domain entity. This corrective changes only SongChart public presentation taxonomy and local/testing fixture resolution.

### Native capability assessment

- Reuse the existing `artists.artist_type` canonical field; do not create a second Group model or migrate identities.
- Use named Laravel routes and `PublicEntityUrl` as the single URL policy boundary.
- Use the canonical Eloquent catalog first for entity detail lookup.
- Allow `DemoSearchCatalog` fallback only in `local` and `testing` so deterministic UI fixtures remain renderable without leaking demo data into production.
- Make Blade entity rows derive their URL from type/slug/subtype when a fixture omits an explicit URL.

### Custom implementation justification

MusicBrainz correctly models both people and groups as Artist entities, while SongChart's public information architecture benefits from a user-facing distinction between solo artists and groups. The custom URL policy therefore maps canonical Artist subtypes to public taxonomies without rewriting provider identities, relationships, or external identifiers. This also allows previously imported group records to become `/groups/{slug}` immediately from their existing `artist_type` value.

## Required behavior

- `artist_type=person` uses `/artists` and `/artists/{slug}`.
- `artist_type in (group, orchestra, choir)` uses `/groups` and `/groups/{slug}`.
- Canonical Artist ULIDs, MusicBrainz identifiers and Artist relationships remain unchanged.
- `/artists/{slug}` must 404 for a canonical group; `/groups/{slug}` must 404 for a canonical person.
- Canonical DB data wins over local/testing demo fixtures.
- Demo detail fallback is unavailable outside `local` and `testing`.
- Result rows remain renderable when fixture arrays omit `url`.
- Legacy `/entity/*` and singular routes remain absent; no redirect is introduced.

## Non-goals

- No schema migration.
- No rewrite of canonical Artist records into a new Group entity.
- No SEO redirect migration while the catalog remains local/unindexed.
- No new provider or enrichment orchestrator.

## Tests and verification

- Feature test separating `/artists` person records from `/groups` group records.
- Detail-route test proving subtype mismatch returns 404.
- Architecture test enforcing both public taxonomies while preserving canonical Artist identity.
- Existing entity-detail, Provider Chooser, Search Flow and UI Preview regressions must return to green.
- Run domain/use-case/documentation/repository-state/type/PHPStan/PostgreSQL/Pest/canonical verification.

## Acceptance criteria

- Existing imported BLACKPINK-like group records automatically generate `/groups/{slug}` from `artist_type=group` with no migration.
- Solo/person records generate `/artists/{slug}`.
- Stage 17.8 runtime regressions are closed without enabling demo data in production.
