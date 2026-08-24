# Stage 16.4.1 — Engineering Toolchain & Package Governance Standardization

## Authority and official sources

### Repository authorities

Stage 16.4 Pulse observability, Stage 16.3 queue infrastructure, existing stack/package/testing/static-analysis authorities, PostgreSQL release authority, code-generation guardrails and repository-state governance remain authoritative.

### Installed versions

PHP 8.5+ / Laravel 13 / PostgreSQL 17+ / `laravel/pulse:^1.7.4`.

### Official external sources

- Laravel 13 testing documentation: Unit tests do not boot the Laravel application and cannot access framework services.
- PHPStan baseline guidance: baseline is for legacy adoption; new errors should not be casually added and the baseline's lifecycle goal is removal.
- Laravel/Pulse upstream migration/config remain package source references; committed derivatives must also satisfy SongChart quality gates.

### Native capability assessment

Composer scripts, Artisan commands, Pest suites, Pint, Larastan/PHPStan and existing repository verifiers already provide the necessary primitives. The missing capability is one executable authority that records package admission, test taxonomy and candidate verification truthfully.

### Custom implementation justification

Custom code is limited to SongChart governance metadata/verifiers and a read-only `songchart:doctor` command. It does not replace Laravel testing, Composer, Pint, PHPStan, Pest, Redis, PostgreSQL or Pulse.

## Objective

Prevent installer-time discovery of predictable Composer/Pint/PHPStan/Pest/package integration errors by making environment/package/test/candidate rules executable before packaging.

## Scope

- fix Pulse migration exhaustive database-driver handling
- `PROJECT_AUTHORITY.md`
- machine-readable package registry
- package admission verifier
- Unit/Feature taxonomy verifier
- candidate verification contract/manifest
- read-only `songchart:doctor`
- integrate governance checks into `composer quality:verify`
- architecture/feature tests and impact mapping
- preserve historical 16.4.1 chronology while documenting numeric reuse

## Non-goals

- weakening PHPStan
- creating a second package manager
- replacing CI
- Dockerizing the main Laragon workflow
- adding Activitylog/Permission/Data packages
- modifying business domain semantics

## Expected files

Pulse migration, project authority, stack package/testing/static docs, machine-readable registries, doctor command, governance scripts/tests, Composer scripts, impact map, docs/history.

## Allowed incidental files

README current-stage pointer, documentation index, candidate verification metadata and changeset installer.

## Scope deviations

The numeric identifier 16.4.1 was used historically for a documentation delivery hotfix. That history remains intact; this Discovery-infrastructure roadmap reuses the identifier explicitly.

## Tests and verification

Packaging environment must pass PHP syntax and all static governance verifiers available without vendor. Target/canonical environment must run Composer validation, Pint, PHPStan/Larastan, Pest, PostgreSQL tests, frontend build and release verification before closure is claimed.
