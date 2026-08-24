# Stage 17.8 Task Contract

## Scope

Deliver the first provider-neutral Canonical Data Fusion foundation on the existing provenance schema and canonicalize public entity URLs to plural type-specific routes. Remove the public `/entity/{type}/{slug}` abstraction and singular detail aliases without redirects because the catalog is still local and unindexed.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/domain/URL_CONTRACTS.md`
- `docs/project/docs/URL_SEO.md`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- PostgreSQL 18 is the canonical database authority.

### Official external sources

No new external provider API is introduced in Stage 17.8. Existing provider contracts remain authoritative for MusicBrainz and YouTube. Data fusion in this stage is internal deterministic evidence selection over SongChart's existing provenance records.

### Native capability assessment

- Reuse existing `metadata_sources`, `metadata_assertions`, `metadata_conflicts`, `external_identifiers`, and `provider_destinations` instead of adding a second evidence store.
- Use Laravel configuration and container binding for field/source authority policy.
- Use Eloquent read models for entity passports and evidence resolution.
- Use named Laravel routes and one public URL mapper instead of hard-coded entity URL assembly in views.

### Custom implementation justification

Provider APIs return partial and sometimes conflicting evidence. SongChart needs a provider-neutral field resolver that ranks evidence by declared source authority, assertion confidence, and freshness while preserving all assertions and conflicts. This resolver is intentionally read-only: it does not silently overwrite canonical values. Entity Passport exposes coverage/confidence/conflict state so future enrichment orchestration can operate on data quality rather than provider-specific workflows.

## Required behavior

- MusicBrainz, YouTube, editorial and future providers are evidence sources, not alternate canonical stores.
- Field resolution is deterministic and explainable; ties are stable.
- Existing provenance rows remain append-safe and source-attributed.
- Public canonical routes are plural/type-specific for all current `EntityType` values.
- `/entity/*` and singular detail aliases return 404; no redirect is introduced in this local/unindexed stage.
- Search/home/catalog links use the same URL mapper.
- Entity detail exposes a Data Passport with coverage, confidence, evidence count and open conflicts.

## Non-goals

- No provider-wide enrichment planner yet.
- No Wikidata provider yet.
- No automatic canonical overwrite from fusion scores.
- No YouTube iframe player yet.
- No redirect migration for legacy local URLs.

## Tests and verification

- Deterministic field resolution test with conflicting MusicBrainz/YouTube assertions.
- Architecture guard forbidding restoration of `/entity/{type}/{slug}` and singular detail routes.
- Feature coverage for plural public paths and legacy 404 behavior.
- Run domain/use-case/documentation/repository-state/type/PHPStan/PostgreSQL/Pest/canonical verification.

## Acceptance criteria

- One evidence model serves all providers.
- Canonical data remains distinct from evidence selection.
- Entity Passport is available from canonical detail data.
- Public route structure is symmetric and no internal `Entity` abstraction leaks into URLs.
