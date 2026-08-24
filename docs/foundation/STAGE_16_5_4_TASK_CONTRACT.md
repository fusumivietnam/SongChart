# Stage 16.5.4 — Docker-First Development Environment Consolidation

Status: implementation candidate v22.

## Goal

Make Docker Desktop + WSL2 the primary SongChart development/test runtime while preserving the existing Docker data volumes and retaining Laragon only as a compatibility lane.

## Non-goals

- deleting Laragon compatibility in this stage;
- changing product/domain behavior;
- resetting the existing Docker development database;
- changing PostgreSQL/Redis/PHP/Node package versions;
- changing canonical verification semantics from Stage 16.5.3.

## Acceptance criteria

- `docker-development` is the primary development profile.
- Laragon is explicitly compatibility-only and cannot make release claims.
- host PHP/Composer/Node/PostgreSQL/Redis are not required for the primary workflow.
- one root `songchart.bat` + `scripts/songchart.ps1` owns routine Docker commands.
- old docker BAT/PowerShell entrypoints are compatibility shims, not independent orchestration.
- `songchart test` runs the isolated Docker stage closure; `songchart verify` runs canonical closure.
- dev and verification databases remain isolated.
- existing `songchart_docker` development DB/volume is preserved.

## Changed authorities

- `docker-development`
- `ai-development-protocol`

## Expected files

- `docs/project/stack/docker-development-contract.json`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/candidate-verification-contract.json`
- `candidate-verification.json`
- `compose.dev.yml`
- `compose.verify.yml`
- `PROJECT_AUTHORITY.md`
- `README.md`
- `docs/project/docs/LOCAL_SETUP.md`
- `docs/project/stack/STACK_OVERVIEW.md`
- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `AGENTS.md`
- `CLAUDE.md`
- `GEMINI.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `scripts/verify-ai-development-protocol.php`
- `tests/Architecture/AiDevelopmentProtocolTest.php`
- `scripts/songchart.ps1`
- `songchart.bat`
- `scripts/docker-stage-verify.sh`
- `scripts/verify-reproducible-environment.php`
- `scripts/canonical-verify.sh`
- Docker compatibility wrappers
- `scripts/verify-docker-first-development.php`
- `scripts/verify-docker-local-development.php`
- `scripts/verify-runtime-contracts.php`
- `tests/Architecture/DockerFirstDevelopmentEnvironmentTest.php`
- repository authority/impact/regression registries
- PROJECT_AUTHORITY/README/AI protocol/history/index


## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/candidate-verification-contract.json`
- `candidate-verification.json`
- `docs/project/stack/docker-development-contract.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/stack/STACK_OVERVIEW.md`

### Installed versions

| Capability | Version/authority |
|---|---|
| PHP | 8.5 / canonical Dockerfile and Composer constraint |
| Laravel | ^13.0 / `composer.json` + target `composer.lock` |
| PostgreSQL | 18.4 image / runtime contract |
| Redis | 7.4 image / Docker compose |
| Node | 22 / canonical Dockerfile |
| Caddy | 2.11.3 / development compose |

No dependency version is changed by this stage.

### Official external sources

No new external API or package capability is adopted. Existing Docker Compose, Docker Desktop/WSL2, PHP, PostgreSQL, Redis, Node and Caddy choices are retained from repository authorities; this stage changes ownership/orchestration only.

### Native capability assessment

- Capability owner: Docker Compose + existing repository PowerShell/BAT wrappers.
- Native/first-party capability available: yes.
- Selected primitive: Docker Compose v2 service orchestration and PowerShell command delegation.
- Why it satisfies the requirement: existing dev/verify containers already provide the required runtime isolation; consolidation does not require another orchestration package.

### Custom implementation justification

- Custom code required: yes, narrow repository CLI only.
- Missing official behavior: project-specific commands, safety boundaries and compatibility shims.
- Narrow custom boundary: `scripts/songchart.ps1`, `songchart.bat`, contract/verifier.
- Framework primitives reused: Docker Compose v2, existing canonical scripts.
- Non-goals: custom container scheduler or replacement for Docker Compose.

## Verification plan

- Packaging: syntax, JSON parse, docker-first/runtime/repository/authority/static governance.
- Target routine closure: `songchart test`.
- Canonical closure: `songchart verify`.
- Packaging remains post-canonical only.

## Tests and verification

- PHP syntax for new/changed PHP tests/verifiers.
- JSON parse for Docker/runtime/repository authorities.
- `composer docker-first:verify`.
- `composer docker-local:verify`.
- `composer runtime-contracts:verify`.
- repository compiler/regression/authority dependency/impact/documentation/source static gates.
- target focused Architecture test.
- target `songchart test`.
- target `songchart verify` canonical closure.
- Any target-only gate not actually executed in packaging is reported as not run.

## Rollback

File-only rollback. Existing Docker volumes and the `songchart_docker` development database are not removed by this stage.
