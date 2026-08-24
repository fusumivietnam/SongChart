# Stage 17.3.1 — Canonical Artist Admin Editing & Development Status Corrective

Status: implementation candidate.

## Goal

Close two gaps found during live MusicBrainz Artist testing: let authorized administrators correct canonical Artist metadata without mutating provider evidence, and make the local pipeline snapshot distinguish real database failures from application/presentation errors.

## Non-goals

- no Release/Recording/Work editing;
- no bulk catalog editor;
- no provider payload mutation;
- no schema migration;
- no YouTube integration;
- no replacement of Laravel validation, Gates, transactions, or audit primitives.

## Acceptance criteria

- authorized catalog editors can update Artist name, sort name, slug, type, country code, and verification state from Admin;
- slug validation preserves lowercase URL-safe uniqueness;
- canonical edit requires recent password confirmation and a rationale;
- mutation is guarded by a dedicated `manage-catalog` Gate and recorded in privileged audit with before/after values;
- reviewer role remains read-only for canonical catalog data;
- external identifiers, relationships, metadata conflicts, and raw provider evidence remain untouched;
- `/development/status` renders enum-backed latest import status via enum value;
- only actual database query failures are labeled `database unavailable`; non-database control-center errors are distinguishable;
- no migration or dependency change is required.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/security/authorization-contract.json`
- `docs/project/domain/use-case-contracts.json`
- `app/Support/Admin/CatalogAdministration.php`
- `app/Support/Local/DevelopmentControlCenter.php`

### Installed versions

No dependency changes. Runtime remains PHP 8.5, Laravel 13, PostgreSQL 18.4, Redis 7.4, and the existing Docker development/canonical verification profile.

### Official external sources

- Laravel 13 validation: https://laravel.com/docs/13.x/validation
- Laravel 13 authorization / Gates: https://laravel.com/docs/13.x/authorization
- Laravel 13 database transactions: https://laravel.com/docs/13.x/database#database-transactions
- Laravel 13 Eloquent mutability/fillable models: https://laravel.com/docs/13.x/eloquent

### Native capability assessment

Laravel Gates, request validation, `Rule::unique`, Eloquent fillable models, database transactions, password-confirm middleware, and the existing SongChart privileged-audit contract are sufficient. No package or custom RBAC framework is needed.

### Custom implementation justification

SongChart needs a narrow application service because canonical Artist mutation must stay outside Blade/controller persistence while preserving provider evidence and privileged audit. The custom code only composes Laravel primitives around SongChart's existing canonical domain boundary.

## Security, authorization, and data impact

Adds `manage-catalog` to editor, provider-manager, and super-admin roles. The mutation route remains inside the authenticated/active/verified/admin/2FA group and additionally requires `can:manage-catalog` plus `password.confirm`. No external identifier or provider raw payload is modified.

## Tests and verification

- `tests/Feature/CatalogAdministrationTest.php` covers successful governed edit and reviewer denial;
- `tests/Feature/Development/LocalBootstrapTest.php` covers enum-backed pipeline rendering without false database-unavailable state;
- authorization, type-guardrail, documentation, official-source, repository-state, taxonomy, repository-contract, and candidate-contract verification;
- final target closure via `verify-songchart.bat`.

## Rollback

Restore Stage 17.3 application/governance files. No database rollback is required because Stage 17.3.1 adds no migration.
