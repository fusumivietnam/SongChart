# Stage 16.3 Task Contract — Catalog Administration

Status: retrospective task contract reconciled at Stage 16.4.4.

## Goal

Provide read-only administrator list/filter/detail inspection for all canonical catalog entity types while preserving existing authorization and catalog invariants.

## Non-goals

- No catalog mutation, archive/restore, merge, or delete actions.
- No new catalog schema.
- No provider ingestion changes.

## Acceptance criteria

- Admin readers can list artist, work, recording, version, release, and collection entities.
- Lists support query, verification-state filtering, sort, and pagination.
- Detail pages expose canonical attributes, identifiers, relationships, and metadata conflicts.
- Unsupported entity types return 404.
- Catalog detail ULIDs accept canonical generated lower-case values; Stage 16.3.1 additionally accepts uppercase input.

## Affected modules and boundaries

- Admin catalog controller/support service/routes/views.
- Catalog administration feature tests.
- Existing admin authentication/2FA middleware only.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/docs/SECURITY.md`
- `docs/project/domain/domain-contracts.json`
- `docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.3` | `composer.json` |
| Laravel | `^13.0` | `composer.json` |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel | `https://laravel.com/docs/13.x/eloquent` | Eloquent querying/model behavior | 2026-08-07 |
| Laravel | `https://laravel.com/docs/13.x/pagination` | Length-aware pagination | 2026-08-07 |
| Laravel | `https://laravel.com/docs/13.x/routing` | Route parameters/constraints | 2026-08-07 |

### Native capability assessment

- Capability owner: Laravel routing, Eloquent, pagination, authorization middleware.
- Native/first-party capability available: yes for primitives, no for SongChart catalog presentation semantics.
- Selected official API or primitive: Eloquent builders, paginator, route constraints, middleware.
- Why it satisfies the requirement: framework owns transport/persistence primitives while SongChart maps canonical entity contracts to admin presentation.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: SongChart-specific multi-entity catalog inspection and provenance presentation.
- Narrow custom boundary: admin catalog query/presentation service and views.
- Framework primitives reused: routing, Eloquent, paginator, authorization.
- Non-goals: custom ORM or permission framework.

## Domain contract and use-case data surface

- Actor and preconditions: active, verified administrator with confirmed 2FA.
- Input types and identifier formats: entity type enum value, ULID entity ID, query/filter/sort/page values.
- Exact entity fields read: contract-declared canonical fields plus identifiers, relationships, and metadata conflicts.
- Exact entity fields written: none.
- Null/unknown semantics: absent optional metadata remains explicit and read-only.
- Output DTO/presentation contract: paginated admin list and entity detail view models.
- Route/API contract: `/admin/catalog/{type}` and `/admin/catalog/{type}/{id}`.
- Relationship invariants: no relationship mutations.
- Contract changes required: no.
- `domain-contracts.json` entries affected: none at implementation time; Stage 16.4 later centralized these semantics.

## Security, authorization, and data impact

Read-only routes inherit the existing admin/active/verified/2FA boundary. No secrets or write operations are added.

## Tests and verification

- `tests/Feature/CatalogAdministrationTest.php`.
- Unsupported type and authorization behavior.
- ULID route regression corrected in Stage 16.3.1.
- Pint/Larastan/database lanes through the repository release gate.

## Documentation impact

- README/history/index references must identify Stage 16.3 as Catalog Administration, not Admin Information Architecture.

## Rollback

Remove admin catalog route/controller/support/views/tests; no schema rollback is required.
