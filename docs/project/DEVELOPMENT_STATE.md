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
        +--> [IN PROGRESS] production topology + environment inventory
        |
        +--> [NEXT] secrets/environment hardening
        |
        +--> [NEXT] queue/scheduler production runtime
        |
        +--> [NEXT] observability + alerting
        |
        +--> [NEXT] backup/recovery
        |
        +--> [NEXT] security review + production smoke
        |
        `--> [FINAL] release package/tag from accepted exact main tree
```

## Done

- Stage 18.6 deterministic destination eligibility/preference, freshness handling, YouTube verification quality, public destination projection/explainability and Admin remediation all closed under exact-tree candidate/canonical verification.
- Stage 18.6 final exact head `0ba79a89994b0a2722f5f6c18a84af0045e88cc6` passed candidate and canonical verification.
- GitHub Actions run #161 passed on that exact head.
- PR #15 merged Stage 18.6 to `main` as accepted merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch was created directly from that accepted merge commit.

## In progress

### 19.0.1 — Production topology + environment inventory

The first Stage 19 slice is inventory-first and must not guess infrastructure:

- inventory current Docker/Caddy/PHP/queue/PostgreSQL/Redis/runtime assumptions and every production-relevant environment variable already owned by repository authorities;
- identify what can move unchanged from development/canonical verification into production and what requires explicit production-only authority;
- define the smallest supported first-release deployment topology before adding scripts, manifests or hosting-specific behavior;
- keep PostgreSQL 18 release authority and historical migration immutability intact;
- preserve provider credentials, rate limits, authorization, privileged audit and canonical identity boundaries;
- no provider/product feature expansion in this slice;
- no secret values committed to the repository;
- no scheduler/queue/observability implementation until topology ownership is explicit.

## Current blockers / risks

- Production topology must be derived from existing runtime authority and actual release needs, not from a speculative platform preference.
- Secrets must remain external to tracked source; examples/templates may name keys but never contain real values.
- Queue/scheduler topology cannot assume a single-process web runtime if current jobs require independent workers.
- Backup/recovery must be PostgreSQL-aware and tested against restore behavior, not documented as an unverified checklist.
- Production smoke verification must be deterministic where possible; live provider/network checks remain release-confidence evidence, not canonical verification owners.
- Any workflow mechanism change must update its Markdown authority, machine contract/routing and permanent regression in the same logical change.

## Latest acceptance evidence

- Stage 18.6 exact sealed head: `0ba79a89994b0a2722f5f6c18a84af0045e88cc6`.
- Stage 18.6 candidate verification contract: PASS.
- Stage 18.6 canonical verification: PASS.
- GitHub Actions run #161 on exact sealed head: PASS.
- Accepted `main` merge commit: `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch starts exactly from that accepted merge commit.

## Next required action

1. Sync local `main` to accepted merge `40eba85e36bed1d3a5604975e45ad3234aca6e25` and switch to `stage-19.0-production-readiness-first-release`.
2. Inventory production-relevant runtime files, Docker/Caddy configuration, queue/scheduler entrypoints, environment templates, deployment/release scripts, observability and backup surfaces.
3. Run `./songchart impact <planned-paths...>` on the narrowed inventory before implementation.
4. Record the supported first-release topology and explicit non-goals in the Stage 19 task contract/validation checkpoint before adding production runtime behavior.
5. Implement Stage 19 in bounded slices with the same Pint → focused tests/PHPStan → reconcile → impact verify → candidate → canonical discipline.

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
