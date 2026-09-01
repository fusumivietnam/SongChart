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
        +--> [IMPLEMENTED / VERIFY] 19.0.1.1 data contract + provider taxonomy convergence
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
- 19.0.1.1 provider taxonomy convergence has been exercised by the canonical PostgreSQL CI lane after legacy fixture vocabulary was migrated to the current typed category/capability owners.
- GitHub-native mobile evidence transport now preserves PostgreSQL failure logs as exact-head/run-scoped artifacts and CI checks out the exact PR head SHA rather than relying on the synthetic pull-request merge ref.

## In progress

### 19.0.1.1 — Data Contract + Provider Taxonomy Convergence

Implementation is complete and pending exact-tree closure verification:

- `docs/project/domain/DATA_CONTRACT.md` now owns recurring cross-boundary representation rules for IDs, external IDs, timestamps, partial dates, null/collection semantics, URLs, machine reason codes, provider evidence and JSON configuration boundaries;
- `docs/providers/PROVIDER_TAXONOMY.md` separates provider slug, broad category, operational role and concrete capability;
- typed `ProviderCategory`, `ProviderRole` and `ProviderCapabilityCode` contracts remove free-form category/capability naming from new governed code;
- `ProviderTaxonomy` is the single runtime definition owner for the known provider registry and classifies providers as `data`, `destination` or `service` without changing canonical identity semantics;
- `Provider` and `ProviderCapability` fail fast when unknown category/capability values are saved;
- `ProviderRegistrySeeder` consumes the taxonomy registry, preserves existing provider status/enabled state and seeds known capability rows on the existing schema;
- `ProviderOperationalAssessor` normalizes operational states to `disabled`, `unapproved`, `misconfigured`, `degraded`, `ready` with stable runtime issue codes;
- unknown runtime health is degraded/fail-closed rather than implicitly healthy;
- no migration, new provider, canonical mutation path or generic integration framework was introduced.

## Current blockers / risks

- Exact-tree `./songchart impact --verify`, diagnostic audit, candidate and canonical closure are not yet recorded for the current Stage 19 tree.
- `DEVELOPMENT_STATE.md` is a generated-context input, so this evidence refresh requires one deterministic reconcile/generated-only checkpoint before the next broad verification pass.
- Production environment values remain ambiguous until 19.0.2 defines a hardened environment/secrets contract.
- The existing `docker/verify/Dockerfile` and `compose.dev.yml` remain verification/development infrastructure, not production runtime.
- Scheduler lifecycle, queue/Horizon restart policy, observability retention and PostgreSQL backup/restore remain later Stage 19 slices.
- Any workflow mechanism change still requires owning Markdown authority, machine contract/routing and permanent regression in the same logical change.

## Latest focused evidence

- Generated authority was reconciled from exact branch tree `cc329eab3a43211aaf6459e2638c2252796caf57` and committed as generated-only checkpoint `477d74f228dc22087c3b7c7c8453ec74cdae7b4e`.
- `candidate-verification.json` was aligned from Stage 18.6 to current Stage 19 in follow-up commit `98f38b8b3839ed57c4c268c4f6b30c0feb32580c`.
- GitHub Actions run #169 checked out exact PR head `98f38b8b3839ed57c4c268c4f6b30c0feb32580c`.
- PostgreSQL job in run #169: PASS.
- Frontend build job in run #169: PASS.
- Quality run #169 passed executable repository authority, regression ledger, schema/model/runtime/route/release/privileged-operation/candidate/toolchain gates and then stopped at the AI development protocol because this required `## Latest focused evidence` checkpoint section was missing.
- No Stage 19 candidate PASS or canonical PASS is claimed from the focused/CI evidence above.

## Latest acceptance evidence

- Stage 18.6 exact sealed head: `0ba79a89994b0a2722f5f6c18a84af0045e88cc6`.
- Stage 18.6 candidate verification contract: PASS.
- Stage 18.6 canonical verification: PASS.
- GitHub Actions run #161 on exact sealed head: PASS.
- Accepted `main` merge commit: `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch starts exactly from that accepted merge commit.
- Stage 19 remains open and requires fresh exact-tree closure evidence after the current authority checkpoint.

## Next required action

1. Reconcile generated repository authority from the current Stage 19 exact tree and commit only expected `docs/project/generated/` outputs.
2. Run `./songchart impact --verify` on that reconciled tree.
3. Resolve any remaining focused/quality findings without weakening gates, then run `./songchart audit` as diagnostic evidence.
4. Only after current-tree PASS close the convergence slice and proceed to 19.0.2 production environment/secrets hardening.
5. Candidate/canonical closure remains deferred until the Stage 19 task contract reaches the appropriate exact-tree closure checkpoint.

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
