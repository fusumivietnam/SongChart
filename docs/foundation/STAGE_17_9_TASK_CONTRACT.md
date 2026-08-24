# Stage 17.9 Task Contract

## Scope

Turn the Stage 17.8 Data Passport into a provider-neutral Identity Bridge and a deterministic read-only Enrichment Planner. Provider-specific identifiers and approved destinations must attach to one SongChart canonical entity, while missing-data planning must be expressed as recipes rather than provider-specific admin workflows.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/domain/URL_CONTRACTS.md`
- `config/data-fusion.php`
- `config/catalog-enrichment.php`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- PostgreSQL 18 is the canonical database authority.

### Official external sources

No new provider API or external provider policy is introduced in Stage 17.9. Existing MusicBrainz and YouTube provider contracts remain unchanged. This stage operates only on SongChart's persisted provider evidence, identifiers, destination reviews and provider registry state.

### Native capability assessment

- Reuse `external_identifiers` and `provider_destinations` as the cross-provider identity bridge; do not add a second identity table.
- Reuse `metadata_assertions` plus the Stage 17.8 field resolver to determine whether required evidence already exists.
- Use Laravel config as the recipe authority and container contracts for the planner and identity bridge.
- Read provider enablement from the provider registry; do not bypass admission or feature flags.

### Custom implementation justification

A multi-provider catalog needs a provider-independent answer to two questions: which external identities are attached to this SongChart entity, and what evidence is still missing? The Identity Bridge provides the first answer without changing canonical ULIDs. The Enrichment Planner provides the second as an explainable plan containing provider, priority, cost class and provider availability. Planning is intentionally side-effect free so future orchestration can execute reviewed plans through existing provider boundaries.

## Required behavior

- One SongChart canonical entity may expose many provider identifiers and reviewed destinations through one Identity Bridge snapshot.
- MusicBrainz MBIDs/ISRCs and YouTube destinations remain provider evidence; none becomes the SongChart primary key.
- Recipes may require fields, identifiers and destinations by entity type.
- Planner output includes missing kind/key, preferred provider, priority, cost class, reason and whether the provider is enabled.
- Planner uses existing evidence and provider registry state only.
- Planner never calls provider APIs, queues jobs or mutates canonical fields.
- Entity Data Passport exposes Identity Bridge and Enrichment Plan sections.

## Non-goals

- No automatic provider execution.
- No Wikidata provider yet.
- No canonical overwrite from planner output.
- No new migration.
- No YouTube iframe player.

## Tests and verification

- Feature test proving an Artist with MusicBrainz identity produces one connected identity source and only the genuinely missing enrichment need.
- Test must prove planner execution leaves canonical data unchanged.
- Run architecture/domain/use-case/type/taxonomy/documentation/repository-state/PHPStan/PostgreSQL/Pest/canonical verification.

## Acceptance criteria

- Provider identities are visible as one canonical bridge.
- Missing data is represented as a deterministic provider-neutral plan.
- Provider disabled/enabled state is visible to planning without being bypassed.
- Existing canonical identity and public URL policy remain unchanged.
