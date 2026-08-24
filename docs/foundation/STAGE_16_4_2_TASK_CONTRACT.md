# Stage 16.4.2 — Reproducible Verification Environment & PostgreSQL 18 Authority

## Authority and official sources

### Repository authorities

Stage 16.4.1 engineering governance, `PROJECT_AUTHORITY.md`, release lockfiles, PostgreSQL-only release testing, `composer release:verify`, package registry, impact map and Laragon local-development support remain authoritative.

### Installed versions

- PHP 8.5
- Laravel 13
- PostgreSQL major 18 exactly for release verification
- canonical PostgreSQL container reference: 18.4
- Node 22
- Composer 2
- Redis queue/cache runtime

### Official external sources

- Laravel 13 Sail documentation: Docker-based Laravel workflows are supported on Windows via WSL2 and PHP 8.5 is supported.
- PostgreSQL 18 release documentation: PostgreSQL 18 is the current major baseline; 18.4 is the current maintenance release referenced by this stage.
- Docker Compose documentation: named volumes persist reusable dependency data, and `depends_on` with `service_healthy` waits for dependency health checks before starting dependent services.

### Native capability assessment

Docker Compose, Composer lockfiles, npm lockfiles, Pint, Larastan/PHPStan, Pest and the existing release gate already provide the primitives. SongChart needs only a project-owned composition and entry-point scripts that wire them together reproducibly.

### Custom implementation justification

Custom code is limited to environment composition, PostgreSQL-major verification, candidate evidence recording and static governance. It does not replace Composer, Docker, PostgreSQL, Redis, Pint, PHPStan, Pest or Laravel testing.

## Objective

Ensure release candidates are formatted, analyzed, tested and migrated in a reproducible environment with real `vendor/`, real `node_modules`, PostgreSQL 18 and Redis before packaging, while keeping Laragon as the normal Windows development runtime.

## Scope

- `compose.verify.yml`
- PHP 8.5 verification image with Composer 2 and Node 22
- PostgreSQL 18.4 canonical container
- Redis verification service
- isolated named volumes for `vendor/`, `node_modules` and Composer cache
- Windows/WSL/Linux verification entry points
- exact PostgreSQL major-18 runtime gate
- `songchart:doctor` PostgreSQL-major awareness
- CI PostgreSQL major 18
- candidate evidence recording
- package/stack/current-stage authority updates
- architecture/governance tests and static verifier

## Non-goals

- replacing Laragon
- committing `vendor/` or `node_modules`
- running Horizon on native Windows
- moving production deployment to Docker
- adopting PostgreSQL 19 before explicit compatibility review
- weakening existing release gates

## Expected files

Compose/Docker verification files, host launchers, PostgreSQL/candidate verifier scripts, runtime/doctor/CI updates, active stack docs, current-stage docs, tests and impact map.

## Allowed incidental files

README, documentation index/history, Composer scripts, candidate verification metadata.

## Scope deviations

The numeric identifier 16.4.2 was used historically for an official-source task-contract hotfix. That history remains preserved; this Discovery infrastructure stage explicitly reuses the identifier.

## Tests and verification

Packaging environment must pass syntax and static governance verifiers. Because the packaging runtime lacks Docker and Composer, it must not claim container/Pint/Larastan/Pest/PostgreSQL closure. On a Docker-capable target, run `verify-songchart.bat`; closure is recorded only after `composer release:verify` succeeds in the canonical environment.
