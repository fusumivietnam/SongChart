# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `18.5 — Public Product / Frontend Release Pass` plus bounded corrective `18.5.1 — Workflow Hardening` merged to `main` via PR #14 as accepted merge commit `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Stage `18.6 — Provider Destination & Media Quality` closed on exact canonical-verified head `0ba79a89994b0a2722f5f6c18a84af0045e88cc6`.
- PR #15 passed GitHub Actions workflow run #161 on that exact head and merged to `main` as accepted merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Development authority remains Linux/WSL2 + Docker through `./songchart`; GitHub is the development handoff source of truth.

## Current stage

- Stage `19.0 — Production Readiness & First Release`
- Branch: `stage-19.0-production-readiness-first-release`.
- Base: accepted Stage 18.6 merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Task contract: `docs/foundation/STAGE_19_0_TASK_CONTRACT.md`.
- Strategy: turn the accepted product tree into a reproducible first production release without weakening existing canonical, provider, auth, audit, migration or verification authorities.

## Stage map

```text
19.0 PRODUCTION READINESS & FIRST RELEASE
        |
        +--> [IMPLEMENTED] 19.0.1 production topology + environment inventory
        |
        +--> [VERIFIED / DOC RECLOSE] 19.0.1.1 data contract + provider taxonomy convergence
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

- Stage 18.6 final exact head `0ba79a89994b0a2722f5f6c18a84af0045e88cc6` passed candidate/canonical verification and PR #15 CI before merge to accepted `main` commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch was created directly from that accepted merge commit.
- 19.0.1 topology inventory is implemented in `docs/operations/PRODUCTION_TOPOLOGY.md`.
- The supported first-release topology is one immutable SongChart application artifact/image reused by independent `web`, `queue` and `scheduler` processes behind Caddy, with PostgreSQL 18 and Redis as explicit dependencies.
- Development PHP built-in server, bind-mounted repository/vendor/node_modules, local mkcert certificates, local DB credentials, disabled Admin 2FA and debug/design-lab defaults are rejected as production primitives.
- 19.0.1.1 data/provider convergence implementation is complete: recurring cross-boundary representations are owned by `DATA_CONTRACT.md`, provider category/role/capability are distinct typed concepts, and exposed legacy fixture/document vocabulary was migrated toward current authorities instead of retained as compatibility aliases.
- GitHub-native mobile evidence transport preserves PostgreSQL failure logs as exact-head/run-scoped artifacts and CI checks out the exact PR head SHA rather than relying on the synthetic pull-request merge ref.
- Legacy Stage 18.5 task-contract documentation exposed by the current quality gate was converged to the current official-source structure rather than exempted from the newer governance standard.

## In progress

### 19.0.1.1 — Data Contract + Provider Taxonomy Convergence

Implementation and exact implementation-tree verification are complete. The remaining action is documentation-tree reclosure because recording that evidence changes tracked authority.

Verified implementation includes:

- `docs/project/domain/DATA_CONTRACT.md` owns recurring cross-boundary representation rules for IDs, external IDs, timestamps, partial dates, null/collection semantics, URLs, machine reason codes, provider evidence and JSON configuration boundaries;
- `docs/providers/PROVIDER_TAXONOMY.md` separates provider slug, broad category, operational role and concrete capability;
- typed `ProviderCategory`, `ProviderRole` and `ProviderCapabilityCode` contracts remove free-form category/capability naming from new governed code;
- `ProviderTaxonomy` is the single runtime definition owner for the known provider registry and classifies providers as `data`, `destination` or `service` without changing canonical identity semantics;
- `Provider` and `ProviderCapability` fail fast when unknown category/capability values are saved;
- `ProviderRegistrySeeder` consumes the taxonomy registry, preserves existing provider status/enabled state and seeds known capability rows;
- `ProviderOperationalAssessor` normalizes operational states to `disabled`, `unapproved`, `misconfigured`, `degraded`, `ready` with stable runtime issue codes;
- unknown runtime health is degraded/fail-closed rather than implicitly healthy;
- no migration, new provider, canonical mutation path or generic integration framework was introduced.

## Current blockers / risks

- The implementation checkpoint passed canonical verification, but this state/validation documentation refresh is a tracked authority change; final documented-tree reconcile and exact-tree reclosure are therefore required before recording a final 19.0.1.1 closed HEAD.
- Production environment values remain ambiguous until 19.0.2 defines a hardened environment/secrets contract.
- The existing `docker/verify/Dockerfile` and `compose.dev.yml` remain verification/development infrastructure, not production runtime.
- Scheduler lifecycle, queue/Horizon restart policy, observability retention and PostgreSQL backup/restore remain later Stage 19 slices.
- Any workflow mechanism change still requires owning Markdown authority, machine contract/routing and permanent regression in the same logical change.

## Latest focused evidence

Exact implementation checkpoint: `e5bf2d4630c2fbbbe8a4a7c480d5cc7acfc7a6a9`.

Observed on that exact tree:

- generated `project-context.json` candidate stage reconciled to `19.0`;
- `./songchart impact --verify`: PASS pre-closure verification;
- PostgreSQL/application suite: 433 tests PASS / 4191 assertions;
- frontend production build: PASS;
- `./songchart audit`: PASS;
- `./songchart candidate`: PASS;
- Docker candidate stage: PASS;
- `./songchart verify`: canonical verification PASS;
- migration upgrade/runtime contract, project-context runtime drift and foundation closure audit: PASS;
- no lockfile release blocker detected;
- tracked tree after canonical verification: clean;
- GitHub Actions run #172 on the same exact SHA: PASS.

The current documentation commits intentionally invalidate reuse of that SHA as final closure evidence. No final documented-tree canonical PASS is claimed yet.

## Latest acceptance evidence

- Stage 18.6 exact sealed head: `0ba79a89994b0a2722f5f6c18a84af0045e88cc6`.
- Stage 18.6 candidate verification contract: PASS.
- Stage 18.6 canonical verification: PASS.
- GitHub Actions run #161 on exact sealed head: PASS.
- Accepted `main` merge commit: `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch starts exactly from that accepted merge commit.
- Stage 19 remains open; 19.0.1.1 has verified implementation checkpoint evidence but awaits final reclosure of the documented authority tree before its final exact closed HEAD is recorded.

## Next required action

1. Synchronize the current documentation-updated Stage 19 branch.
2. Run `./songchart reconcile` and commit only deterministic generated outputs if they change.
3. Run `./songchart impact --verify`, `./songchart audit`, `./songchart candidate`, then `./songchart verify` on one unchanged exact tree.
4. Require clean tracked state after canonical PASS and GitHub Actions PASS on the same pushed SHA.
5. Record that exact SHA as the final 19.0.1.1 closed checkpoint, then proceed to 19.0.2 secrets/environment hardening.
6. A bounded AI/UI design-harness integration such as Impeccable may be evaluated only after this reclosure; SongChart UI/domain authorities remain higher priority and no parallel verification authority may be introduced.

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
