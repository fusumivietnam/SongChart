# Stage 16.5 — Privileged Operations & Audit

## Authority and official sources

### Repository authorities

Stage 16.4.3 canonical verification, PostgreSQL 18 release authority, Laravel native authorization gates, provider operation idempotency/audit ledger, identity review contracts, extension lifecycle actions, authentication hardening and package governance remain authoritative.

### Installed versions

- PHP 8.5
- Laravel 13
- PostgreSQL 18
- `spatie/laravel-activitylog:^5.0`

### Official external sources

- Spatie Activitylog v5 requirements: PHP 8.4+ and Laravel 12+.
- Spatie Activitylog v5 installation: Composer package auto-discovery, published migration/config, and guidance to adapt morph IDs for non-integer identifiers.
- Spatie Activitylog v5 logging API: explicit subject, causer, properties, event and named logs.
- Spatie Activitylog v5 supports querying by subject, causer, event and log name.

### Native capability assessment

Laravel Gates already own authorization and Laravel commands own privileged CLI entry points. Existing SongChart services own provider/identity/extension mutations. Spatie is admitted only to provide the cross-cutting activity storage/query infrastructure that Laravel core does not provide as a business audit ledger.

### Custom implementation justification

Custom code defines SongChart-specific audited event semantics, privacy rules, before/after/context payloads, role safety invariants, admin audit projection and command workflows. It does not recreate a generic activity-log package.

## Objective

Create an explicit cross-cutting business audit trail for privileged operations and replace routine Tinker-based user privilege mutations with audited commands.

## Scope

- `spatie/laravel-activitylog:^5.0`
- ULID-aware `activity_log` migration
- explicit privileged audit contract/adapter
- provider/import mutation audit
- identity conflict decision audit
- extension lifecycle audit
- user role/activation commands
- last-super-admin/self-deactivation guards
- paginated admin audit viewer
- `view-audit` Gate
- sensitive-data exclusions
- schema/package/impact governance
- architecture and feature tests

## Non-goals

- broad automatic model logging
- technical request/SQL/queue logging
- replacing Pulse/application logs/provider ledgers
- full role/permission migration (Stage 16.6)
- arbitrary user editing UI
- logging secrets/raw provider payloads

## Expected files

Package registry/composer update, activity config/migration, audit contract/adapter, privileged services/commands, admin route/view, mutation integrations, tests/verifier/docs and schema ownership.

## Allowed incidental files

README, project authority, development history, environment examples, candidate manifest, sidebar and impact map.

## Scope deviations

The target Composer lockfile is authoritative and is not present in the packaging source artifact. The Stage installer must update the target lockfile with the admitted package before Pint/PHPStan/Pest/canonical verification.

## Tests and verification

Packaging performs PHP syntax and static governance. Target installation must run Composer dependency resolution, migration, focused tests and canonical verification before closure.
