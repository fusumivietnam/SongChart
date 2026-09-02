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
- Strategy: turn the accepted product tree into a reproducible first production release while reducing AI ambiguity and mobile workflow friction without weakening canonical, provider, auth, audit, migration or verification authorities.

## Stage map

```text
19.0 PRODUCTION READINESS & FIRST RELEASE
        |
        +--> [IMPLEMENTED] 19.0.1 production topology + environment inventory
        |
        +--> [SEALED] 19.0.1.1 data contract + provider taxonomy convergence
        |
        +--> [IMPLEMENTED / VERIFY] 19.0.1.2 mobile verification + AI UI design harness
        |
        +--> [IMPLEMENTING] 19.0.2 secrets/environment hardening
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
- 19.0.1.1 converged cross-boundary data conventions and provider category/role/capability vocabulary into current typed/domain authorities; exposed legacy fixtures/docs were migrated rather than preserved as compatibility dialects.
- 19.0.1.1 final documented-tree checkpoint `fea368104d6d941dd4968ff2b4faacab89d48976` passed `./songchart impact --verify`, `./songchart audit`, `./songchart candidate`, `./songchart verify`, finished with a clean tracked tree, and GitHub Actions run #174 passed on the same exact SHA.
- GitHub-native PostgreSQL failure evidence is exact-head/run-scoped and CI checks out the exact PR head SHA.

## In progress

### 19.0.1.2 — Mobile Verification + AI UI Design Harness

Implemented intent pending current-tree verification:

- executable `./mobile` is a presentation-only adapter over canonical `./songchart impact --verify` and `./songchart close`;
- successful mobile runs are compact and preserve full runtime output under `storage/logs`; failures print a bounded tail and preserve the full log path;
- `--verbose` delegates directly to the canonical command and no gate is skipped, cached or reimplemented;
- `verification-command-surface.json` owns the adapter semantics and `VerificationCommandSurfaceTest` permanently guards delegation/failure behavior;
- `docs/ui/AI_DESIGN_HARNESS.md` keeps Impeccable/external design guidance below SongChart UI/domain authorities;
- `.github/skills/songchart-impeccable/SKILL.md` gives GitHub Copilot project-local design guidance without vendoring Impeccable or adding a git submodule;
- `docs/ui/ai-design-harness.json` is the machine-readable advisory contract and `DocumentationAiDesignHarnessTest` routes through existing AI/documentation governance;
- external design findings remain supporting evidence only and cannot become canonical release evidence by themselves.

### 19.0.2 — Secrets / Environment Hardening

Initial implementation is in progress:

- `.env.production.example` defines production-relevant names and safe non-secret defaults/placeholders;
- `docs/operations/PRODUCTION_ENVIRONMENT.md` owns production environment/secrets expectations;
- `ProductionEnvironmentGuard` fails production boot when debug, HTTPS, PostgreSQL, Redis queue/cache, secure-session, Admin 2FA or design-lab invariants drift;
- `AppServiceProvider` invokes the guard only in production;
- `ProductionEnvironmentGuardTest` covers the supported baseline and each fail-closed invariant;
- no real secret, deployment-vendor manifest, provider breadth or migration change was introduced.

## Current blockers / risks

- Current 19.0.1.2/19.0.2 changes have not yet completed reconcile + exact-tree impact/canonical verification.
- Production secret values and deployment injection mechanism are intentionally not selected or stored in source; deployment-platform selection remains separate.
- Provider/service credential requirements still need to be cross-checked against enabled-state/taxonomy semantics before 19.0.2 can be sealed.
- The existing `docker/verify/Dockerfile`, `compose.dev.yml` and demo runtime remain verification/development infrastructure, not production runtime.
- Scheduler lifecycle, queue restart policy, observability retention and PostgreSQL backup/restore remain later Stage 19 slices.

## Latest focused evidence

Final sealed 19.0.1.1 checkpoint: `fea368104d6d941dd4968ff2b4faacab89d48976`.

Observed on that exact tree:

- `./songchart reconcile`: no generated diff after documentation refresh;
- `./songchart impact --verify`: PASS;
- `./songchart audit`: PASS;
- `./songchart candidate`: PASS;
- `./songchart verify`: canonical PASS;
- tracked tree after canonical verification: clean;
- GitHub Actions run #174 on the same exact SHA: PASS.

No PASS is yet claimed for the newer mobile/design-harness or 19.0.2 production-environment changes.

## Latest acceptance evidence

- Accepted Stage 18.6 `main` merge commit: `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch starts exactly from that accepted merge commit.
- 19.0.1.1 sealed checkpoint: `fea368104d6d941dd4968ff2b4faacab89d48976` with local closure PASS, clean tracked tree and GitHub Actions #174 PASS.
- Stage 19 remains open; no Stage 19 final release/tag acceptance is claimed.

## Next required action

1. Let PR CI exercise the current remote writer tree and fix only observed defects without weakening gates.
2. Reconcile generated authority after the current authority/source tranche stabilizes.
3. Use `./mobile check` for compact pre-closure verification; use `./mobile check --verbose` only when interactive detail is needed.
4. Use `./mobile close` for compact canonical closure after current-tree checks pass; it delegates to `./songchart close` without replacing verification logic.
5. Seal 19.0.1.2 and 19.0.2 only on an unchanged exact tree with clean tracked state and same-SHA GitHub Actions evidence.
6. Then proceed to 19.0.3 queue/scheduler production runtime.

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
