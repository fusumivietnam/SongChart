# Stage 17.10.3 — Linux-first CLI + Stable Docker Identity

Status: implementation candidate.

## Objective
Make Linux/WSL the primary host workflow without changing product/domain semantics.

## Scope
- primary Bash `./songchart` CLI;
- stable Compose identities `songchart-dev` and `songchart-verify`;
- Linux Docker bootstrap;
- Windows wrappers remain compatibility-only;
- preserve database safety/canonical gates and local-only secrets/runtime data.

## Acceptance
1. `./songchart --help` works.
2. Dev commands use `songchart-dev`.
3. `./songchart artisan admin:create` forwards args exactly.
4. Test/verify use isolated `songchart-verify`.
5. Renaming/moving the repo does not create a new dev DB identity.
6. Canonical verification passes.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/START_HERE.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/docs/ENGINEERING_WORKFLOW.md`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `compose.dev.yml`
- `compose.verify.yml`

These repository authorities remain controlling for runtime, verification, command surface, and delivery semantics.

### Installed versions

Runtime versions observed through the Linux-first development container:

- PHP `8.5.9`
- Laravel `13.25.0`
- Composer `2.10.2`
- PostgreSQL `18.x` remains release-authoritative
- Node.js is supplied by the repository Docker image / lockfile authority

No dependency upgrade is part of Stage 17.10.3.

### Official external sources

This corrective stage introduces no new third-party package or external service integration.

External runtime capabilities used are existing platform tools:

- Docker Engine and Docker Compose v2
- WSL2 / Ubuntu
- mkcert for local development TLS

No external source changes SongChart domain semantics or canonical data behavior.

### Native capability assessment

Existing native capabilities were assessed before adding custom behavior:

- Docker Compose already provides stable project naming through the project name / Compose `name` capability.
- Linux UID/GID propagation is supported by Compose service `user`.
- Docker named volumes remain the native mechanism for Composer, npm, vendor, node_modules, PostgreSQL, and Redis persistence boundaries.
- Bash is the native Linux/WSL host scripting boundary.
- Existing PowerShell/BAT wrappers are retained only for Windows compatibility.
- Laravel Artisan, Composer, npm, and the existing verification scripts remain the execution owners inside containers.

No parallel container orchestration or package-management mechanism is introduced.

### Custom implementation justification

A small `./songchart` Bash wrapper is required because the existing public host CLI is Windows/PowerShell-oriented and does not provide a reliable Linux-first command surface.

Custom implementation is intentionally limited to:

- mapping public SongChart commands to existing Docker Compose services;
- enforcing stable Compose project identities;
- forwarding the WSL host UID/GID to development services;
- preparing writable development dependency/cache paths;
- preserving Windows wrappers as compatibility shims.

The wrapper does not reimplement Docker Compose, Composer, npm, Artisan, Laravel verification, provider behavior, or database lifecycle logic.

## Tests and verification

Stage 17.10.3 must preserve all existing verification gates.

Required evidence includes:

- `bash -n songchart`
- `bash -n scripts/setup-docker-dev.sh`
- `git diff --check`
- `./songchart artisan about`
- development Compose project identity resolves to `songchart-dev`
- verification Compose project identity resolves to `songchart-verify`
- development `app` and `queue` run with the WSL host UID/GID
- Composer and npm cache paths are writable for the non-root development user
- `php scripts/verify-candidate-contract.php` passes inside Docker
- `php scripts/verify-repository-state.php` passes inside Docker
- `composer stage:verify` / `./songchart test` passes
- `composer canonical:verify` / `./songchart verify` passes before stage closure

No PHPStan, Pint, Pest, PostgreSQL, repository-contract, or canonical verification gate may be weakened to close this stage.
