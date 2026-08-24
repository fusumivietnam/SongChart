# Stage 16.4 Task Contract — Domain Contract Registry & Schema Authority

Status: task contract.

## Goal

Create one machine-readable authority for the implemented canonical catalog entity fields, identifiers, relationship vocabulary and route formats, then make cross-entity runtime code and the quality gate consume it.

## Non-goals

- No new catalog columns or migrations.
- No provider HTTP integration.
- No catalog mutation UI.
- No future SEO route migration from `/entity/{type}/{slug}`.
- No code generation; that belongs to Stage 16.5.

## Acceptance criteria

- Six canonical entity contracts exactly describe current canonical migration columns.
- Writable contract fields match model `$fillable` declarations.
- Declared model casts and soft-delete behavior are verified.
- `EntityType` and `RelationshipType` vocabularies match the registry.
- Search display/date/description/slug semantics come from `DomainContractRegistry`.
- Admin ULID route pattern comes from the registry.
- `composer quality:verify` executes domain-contract verification.
- Task template requires exact read/write/null/identifier/route data surface for later use cases.

## Affected modules and boundaries

- Catalog domain documentation and machine-readable contracts.
- `DomainContractRegistry` runtime lookup boundary.
- Cross-entity search field selection.
- Admin catalog ULID route constraint ownership.
- Composer quality verification and architecture tests.
- No database schema, authentication, provider-network, or catalog mutation boundary changes.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/START_HERE.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/docs/ENGINEERING_WORKFLOW.md`
- `docs/project/docs/ARCHITECTURE.md`
- `docs/project/stack/FRAMEWORK_BASELINE.md`
- `docs/project/stack/DATABASE_CONVENTIONS.md`
- `docs/project/stack/LARAVEL_CONVENTIONS.md`
- `docs/project/domain/domain-contracts.json`
- Existing canonical catalog migrations, models, enums, routes, and tests are implementation evidence for the current schema surface.

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.3` | `composer.json` |
| Laravel | `^13.0`; target runtime observed on Laragon is Laravel 13.x | `composer.json`; target-machine runtime evidence |
| PostgreSQL | PostgreSQL 17+ project authority | `docs/project/docs/TECH_STACK.md`, `docs/project/stack/DATABASE_CONVENTIONS.md` |
| Composer quality scripts | Repository-defined | `composer.json` |

The packaged repository does not contain a `composer.lock`; therefore this contract does not claim an exact resolved Laravel patch version from package metadata. Exact runtime-version evidence belongs to the target-machine release report.

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel | `https://laravel.com/docs/13.x/eloquent` | Eloquent model table/attribute/cast conventions used by contract-to-model verification | 2026-08-07 |
| Laravel | `https://laravel.com/docs/13.x/migrations` | Migration column definitions and schema ownership used as persistence evidence | 2026-08-07 |
| Laravel | `https://laravel.com/docs/13.x/routing` | Route parameters and regular-expression constraints used by the ULID route boundary | 2026-08-07 |

No provider API or external music metadata source is introduced by this stage.

### Native capability assessment

- Capability owner: SongChartWeb owns the music-domain vocabulary; Laravel owns ORM, migrations, routing, and application-container primitives.
- Native/first-party capability available: partial.
- Selected official API or primitive: Laravel Eloquent model metadata, migrations/schema definitions, route constraints, PHP enums, container-injected application services, Composer scripts, and Pest architecture/feature tests.
- Why it satisfies the requirement: Laravel already owns persistence/model/route mechanics, while SongChart-specific entity semantics such as `display_field`, provider-independent entity vocabulary, and cross-entity field meaning require a repository domain authority layered on top of those primitives.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: Laravel does not define SongChart's canonical artist/work/recording/version/release/collection field vocabulary, cross-entity display/date/description semantics, or a repository-specific machine-readable drift contract.
- Narrow custom boundary: one JSON domain registry, one runtime registry reader, one static verifier, domain authority documentation, and architecture/regression coverage. Existing Laravel models, migrations, routes, enums, and Composer orchestration remain authoritative implementation primitives.
- Framework primitives reused: Eloquent models/casts/fillable declarations, migrations, route constraints, dependency injection, PHP enums, Composer scripts, and Pest tests.
- Non-goals: no custom ORM, schema engine, migration runner, route engine, serializer framework, provider schema, or code generator.

## Domain contract and use-case data surface

- Actor and preconditions: developer/maintainer implementing or reviewing catalog behavior after reading repository authorities and the current use case.
- Input types and identifier formats: `EntityType` vocabulary from the registry; canonical entity IDs are ULID strings serialized lowercase while admin route input is case-insensitive; public entity lookup remains slug-based under the implemented `/entity/{type}/{slug}` route family.
- Exact entity fields read: registry metadata for all columns currently declared by the six canonical catalog migrations plus model table/fillable/cast/soft-delete declarations and entity/relationship enum values.
- Exact entity fields written: none in the database. Repository files written are the domain registry/docs, runtime registry, verifier, tests, and cross-entity consumers that replace literal schema guesses.
- Null/unknown semantics: nullable is declared per field in `domain-contracts.json`; absent semantic pointers such as `date_field` or `description_field` mean the entity does not expose that concept and runtime code must not probe an undeclared attribute.
- Output DTO/presentation contract: deterministic runtime lookup for display, slug, optional date, optional description, identifier and route semantics; no new public DTO or API response schema is introduced.
- Route/API contract: admin catalog detail remains `/admin/catalog/{type}/{id}` with a contract-owned case-insensitive ULID constraint; existing public canonical entity route behavior is unchanged.
- Relationship invariants: `RelationshipType` vocabulary must match the registry exactly; Stage 16.4 does not create or mutate relationship rows.
- Contract changes required: yes.
- `domain-contracts.json` entries affected: new authoritative definitions for `artist`, `work`, `recording`, `version`, `release`, `collection`, identifier formats, relationship vocabulary, and current route semantics.

## Security, authorization, and data impact

No sensitive data or authorization behavior changes. Existing admin middleware remains unchanged. Moving the ULID regex authority from a route literal to the registry does not widen authorization; it only normalizes accepted ULID casing before the existing controller/middleware boundary. No database rows or schema are changed.

## Tests and verification

Performed in the packaging environment:

- PHP syntax for new/changed PHP files.
- `php scripts/verify-domain-contracts.php`.
- `php scripts/verify-documentation.php` after the Stage 16.4.1 delivery correction.
- `php scripts/verify-official-sources.php` after this contract correction.
- JSON parsing for `domain-contracts.json` and `composer.json`.
- ZIP integrity checks during packaging.

Required on the target Laragon/runtime environment before release closure:

- Pint.
- Larastan.
- `DomainContractRegistryTest` architecture coverage.
- Existing search and catalog administration regression tests.
- SQLite compatibility lane.
- PostgreSQL authoritative lane.
- Full `composer release:verify`.

A lane is not considered passed unless command output from that lane is available.

## Documentation impact

Add the domain authority documents and registry, update README/current-stage references, task-contract template, documentation indexes/workflow references, and Stage 12 cumulative change manifest as applicable. This task contract itself is governed by the official-source verifier.

## Rollback

Restore modified search/routes/composer/workflow/docs files and remove the Stage 16.4 registry/runtime/verifier/test files. No database rollback is required.
