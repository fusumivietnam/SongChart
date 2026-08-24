# Stage 16.1 Task Contract — Local Development Bootstrap & Demo Readiness

Status: retrospective task contract reconciled at Stage 16.4.4.

## Goal

Provide a safe, repeatable local bootstrap flow that makes the application inspectable on Laragon without default privileged credentials.

## Non-goals

- No production bootstrap automation.
- No default administrator password.
- No live provider integration.
- No production deployment workflow.

## Acceptance criteria

- `php artisan songchart:setup-local` validates and prepares local development state.
- Demo catalog seeding is optional and idempotent.
- Administrator creation remains interactive.
- `/development/status` is available only in local/testing environments.
- Windows storage junctions are treated as valid storage links.

## Affected modules and boundaries

- Local console bootstrap command and readiness reporting.
- Local-only development status route/view.
- Demo seeding and local documentation.
- No production schema capability or privileged credential changes.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/docs/SECURITY.md`
- `docs/project/stack/LARAVEL_CONVENTIONS.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.3` | `composer.json` |
| Laravel | `^13.0` | `composer.json` |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel | `https://laravel.com/docs/13.x/configuration` | Environment/configuration bootstrap primitives | 2026-08-07 |
| Laravel | `https://laravel.com/docs/13.x/filesystem` | Public storage link behavior | 2026-08-07 |

### Native capability assessment

- Capability owner: Laravel Artisan/config/filesystem primitives.
- Native/first-party capability available: partial.
- Selected official API or primitive: Artisan commands, environment configuration, migrations, storage linking.
- Why it satisfies the requirement: framework primitives perform the operations; SongChart only coordinates local readiness policy.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: SongChart-specific readiness matrix, demo bootstrap choices, local-only safety policy.
- Narrow custom boundary: `songchart:setup-local`, readiness reporter, local status surface.
- Framework primitives reused: Artisan, migrations, filesystem/storage, environment APIs.
- Non-goals: replacing Laravel deployment/setup behavior.

## Domain contract and use-case data surface

- Actor and preconditions: developer in `local` or `testing`.
- Input types and identifier formats: CLI flags only.
- Exact entity fields read: readiness checks plus optional demo seeder-owned catalog fields.
- Exact entity fields written: only existing migrations/demo seed paths; no new schema.
- Null/unknown semantics: missing prerequisites are reported explicitly.
- Output DTO/presentation contract: CLI readiness table and local status page.
- Route/API contract: `/development/status` only in local/testing.
- Relationship invariants: no new domain relationships.
- Contract changes required: no.
- `domain-contracts.json` entries affected: none.

## Security, authorization, and data impact

Bootstrap refuses production use, creates no default privileged credentials, and does not expose secrets on the readiness page.

## Tests and verification

- Local-bootstrap feature and architecture coverage.
- Storage-link/Junction compatibility verification on Windows target environment.
- Pint/static analysis and database lanes through the release gate.
- Historical full-release success is not inferred by this retrospective record.

## Documentation impact

- `docs/foundation/STAGE_16_1_LOCAL_BOOTSTRAP.md` remains the operational guide.
- This task contract and its validation report close the missing governance pair.

## Rollback

Remove the Stage 16.1 bootstrap/status implementation and documentation; no schema rollback is required.
