# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `18.5 — Public Product / Frontend Release Pass` plus bounded corrective `18.5.1 — Workflow Hardening` merged to `main` via PR #14 as accepted merge commit `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Stage `18.6 — Provider Destination & Media Quality` closed on exact canonical-verified head `0ba79a89994b0a2722f5f6c18a84af0045e88cc6`.
- PR #15 passed GitHub Actions workflow run #161 on that exact head and merged to `main` as accepted merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Development authority remains Linux/WSL2 + Docker through `./songchart`; GitHub is the development handoff source of truth.

## Current stage

- Stage `19.0 — Production Readiness & First Release`.
- Branch: `stage-19.0-production-readiness-first-release`.
- Base: accepted Stage 18.6 merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Task contract: `docs/foundation/STAGE_19_0_TASK_CONTRACT.md`.
- Strategy: turn the accepted product tree into a reproducible first production release without weakening existing canonical, provider, auth, audit, migration or verification authorities.

## Stage map

```text
19.0 PRODUCTION READINESS & FIRST RELEASE
        |
        +--> [IMPLEMENTED / VERIFY] 19.0.1 production topology + environment inventory
        |
        +--> [NEXT] 19.0.2 secrets/environment hardening
        |
        +--> [NEXT] 19.0.3 queue/scheduler production runtime
        |
        +--> [NEXT] 19.0.4 observability + alerting
        |
        +--> [NEXT] 19.0.5 backup/recovery
        |
        +--> [NEXT] 19.0.6 security review + production smoke
        |
        `--> [FINAL] release package/tag from accepted exact main tree
```

## Done

- Stage 18.6 deterministic destination eligibility/preference, freshness handling, YouTube verification quality, public destination projection/explainability and Admin remediation all closed under exact-tree candidate/canonical verification.
- Stage 18.6 final exact head `0ba79a89994b0a2722f5f6c18a84af0045e88cc6` passed candidate and canonical verification.
- GitHub Actions run #161 passed on that exact head.
- PR #15 merged Stage 18.6 to `main` as accepted merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch was created directly from that accepted merge commit.
- 19.0.1 inventory is now implemented in `docs/operations/PRODUCTION_TOPOLOGY.md`.
- Current runtime inventory confirms development already has PostgreSQL 18.4, Redis 7.4, independent queue worker, Laravel scheduler definitions, Caddy TLS and `/up` health evidence.
- The supported first-release topology is one immutable SongChart application artifact/image reused by independent `web`, `queue` and `scheduler` processes behind Caddy, with PostgreSQL 18 and Redis as explicit stateful/runtime dependencies.
- Development PHP built-in server, bind-mounted repository/vendor/node_modules, local mkcert certificates, local DB credentials, disabled Admin 2FA and debug/design-lab defaults are explicitly rejected as production primitives.
- 19.0.1 intentionally does not introduce a production Dockerfile, Compose manifest or hosting-specific framework before environment/secrets ownership is hardened.

## In progress

### 19.0.1 — Production topology + environment inventory

Implementation is complete and pending deterministic repository verification:

- `docs/operations/PRODUCTION_TOPOLOGY.md` inventories web, queue, scheduler, PostgreSQL, Redis, TLS/edge, observability, release/CI and environment ownership;
- Caddy remains the selected first-release TLS/reverse-proxy primitive unless later evidence proves a blocker;
- PostgreSQL major 18 remains mandatory and external to the application image;
- Redis remains an independent queue/cache/runtime dependency, not a substitute for PostgreSQL durability;
- web/queue/scheduler must be independently restartable even if the first release places them on one host;
- the production application artifact must be immutable, exact-commit traceable, contain built dependencies/assets and contain no real secrets;
- explicit gaps are mapped to 19.0.2 through 19.0.6 rather than solved speculatively in the inventory slice.

## Current blockers / risks

- Production environment values remain ambiguous until 19.0.2 defines a hardened environment/secrets contract; current `.env.example` contains development-safe defaults that must not become production defaults.
- The existing `docker/verify/Dockerfile` is a PHP CLI verification image, not a production web image.
- The existing `compose.dev.yml` is a development topology with bind mounts and PHP built-in server; it cannot be promoted directly to production.
- Scheduler definitions exist, but no independent scheduler lifecycle currently exists in the development Compose stack.
- Queue/Horizon production runtime/restart policy is not yet sealed.
- Backup/recovery must be PostgreSQL-aware and verified by restore behavior in 19.0.5.
- Production smoke verification must be deterministic where possible; live provider/network checks remain release-confidence evidence, not canonical verification owners.
- Any workflow mechanism change must update its Markdown authority, machine contract/routing and permanent regression in the same logical change.

## Latest acceptance evidence

- Stage 18.6 exact sealed head: `0ba79a89994b0a2722f5f6c18a84af0045e88cc6`.
- Stage 18.6 candidate verification contract: PASS.
- Stage 18.6 canonical verification: PASS.
- GitHub Actions run #161 on exact sealed head: PASS.
- Accepted `main` merge commit: `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch starts exactly from that accepted merge commit.
- 19.0.1 topology inventory is implemented; no 19.0.1 PASS is recorded until current-tree verification is observed.

## Next required action

1. Sync local workspace to the current Stage 19 branch head.
2. Run `./songchart impact docs/operations/PRODUCTION_TOPOLOGY.md docs/project/DEVELOPMENT_STATE.md docs/foundation/STAGE_19_0_VALIDATION_REPORT.md`.
3. Run `./songchart impact --diff`, `./songchart reconcile`, then `./songchart impact --verify` for the current 19.0.1 tree.
4. If 19.0.1 verification passes, record exact slice closure before opening 19.0.2.
5. 19.0.2 should define production environment/secrets ownership and fail-closed critical configuration before creating production Docker/Compose runtime files.

## Documentation checkpoint discipline

For every logical implementation slice:

- update the owning task contract only when scope/acceptance changes;
- update this file when blocker, implemented slice, evidence or next action changes;
- keep `README.md` as durable onboarding/overview, not current-stage state storage;
- keep this file as the operational current-state owner;
- keep `docs/project/docs/ROADMAP.md` current/future-only;
- keep completed chronology in `docs/project/DEVELOPMENT_HISTORY.md` after governed acceptance;
- promote reusable workflow rules into their owning authority and permanent guard rather than duplicating them here;
- before AI/device handoff, require exact pushed commit state and no unresolved upstream divergence.
