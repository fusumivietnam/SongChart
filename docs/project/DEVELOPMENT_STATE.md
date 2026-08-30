# Development State

Status: operational checkpoint only. Repository authorities remain authoritative.

## Accepted baseline

- Stage `18.4 — Admin Completion & Operational Convergence` merged to `main` via PR #13 at `2ed9e6d3a5fb8cef21fc69dd61317dceca8f5e94`.
- Stage `18.5 — Public Product / Frontend Release Pass` plus bounded corrective `18.5.1 — Workflow Hardening` closed on exact canonical-verified head `67df5ae72f9dbdc29c43e7afbc7e645203e37cff`.
- PR #14 passed GitHub Actions workflow run #159 and merged that exact head to `main` as accepted merge commit `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Development authority remains Linux/WSL2 + Docker through `./songchart`; GitHub is the development handoff source of truth.

## Current stage

- Stage `18.6 — Provider Destination & Media Quality`
- Branch: `stage-18.6-provider-destination-media-quality`.
- Base: accepted Stage 18.5/18.5.1 merge commit `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Task contract: `docs/foundation/STAGE_18_6_TASK_CONTRACT.md`.
- Candidate closure: 18.6.2 closed on exact head `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`; 18.6.3 is a new changed tree and requires new closure evidence.
- Strategy: improve destination/media eligibility, deterministic preference, freshness/provenance and operator visibility over existing provider infrastructure before considering provider breadth.

## Stage map

```text
18.6 PROVIDER DESTINATION & MEDIA QUALITY
        |
        +--> [DONE] inventory destination/media authority + existing runtime behavior
        |
        +--> [DONE] 18.6.1 deterministic eligibility/preference contract
        |
        +--> [DONE] 18.6.2 freshness + stale/unavailable operational state
        |
        +--> [IN PROGRESS] 18.6.3 YouTube/media verification quality convergence
        |
        +--> [NEXT] public selected-destination projection + explainability
        |
        +--> [NEXT] Admin remediation mutation UX where justified
        |
        `--> [FINAL] focused QA -> candidate -> canonical -> exact-head delivery
```

## Done

- Stage 18.5/18.5.1 accepted through PR #14 after exact-head candidate/canonical closure and green PR CI.
- Stage 18.6 branch created directly from accepted `main` merge commit `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Stage 18.6 task contract initialized with provider-neutral destination/media quality boundaries and explicit non-goals.
- Destination/media inventory confirmed the existing `provider_destinations` schema can express the 18.6 quality slices without schema expansion.
- 18.6.1 routes public Recording media selection through provider-neutral fail-closed eligibility and deterministic preference.
- 18.6.1 candidate and canonical verification passed on exact clean/pushed head `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- 18.6.2 exposes freshness/stale/unavailable operational state through the existing destination evidence model without introducing a second freshness owner.
- 18.6.2 candidate and canonical verification passed on exact clean/pushed head `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- Workflow hardening from 18.5.1 remains cross-stage engineering authority.

## In progress

### 18.6.3 — YouTube/media verification quality convergence

The source slice is implemented and now requires focused verification:

- `VideoDestinationDiscovery` separates public approval verification from exact-resource inspection;
- YouTube search remains actionable/public-only, while inspection can preserve observed private/non-embeddable state for re-verification;
- approval requires explicit public privacy evidence but no longer rejects a public non-embeddable destination, which is valid outbound-only evidence under the 18.6.1 policy;
- `YouTubeDestinationWorkbench::reverify()` refreshes provider metadata and `last_checked_at` without changing canonical Recording linkage, `review_state`, or original `verified_at` approval evidence;
- missing/inaccessible provider resources remain persisted as historical destination evidence and are marked unavailable/fail-closed instead of being silently deleted;
- no new route, migration, scheduler, provider, or canonical identifier was introduced.

## Current blockers / risks

- YouTube Video identity must remain external media evidence and must never become canonical Recording identity.
- Privacy, availability and embeddability are distinct facts; do not collapse them into one boolean.
- Missing verification evidence must remain unknown/fail-closed, not silently public/available.
- `verified_at` must not be rewritten merely because provider availability was rechecked; `last_checked_at` owns provider observation freshness in this slice.
- Refreshing verification must use the existing provider credential and quota/rate infrastructure.
- Do not duplicate the 30-day freshness policy outside `ProviderDestinationPreference`.
- A public but non-embeddable destination may be outbound-only rather than unavailable.
- New route/schema/provider fields cannot be introduced silently.

## Latest focused evidence

- Stage 18.6.1 exact sealed head: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Stage 18.6.2 exact sealed head: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- 18.6.2 candidate verification contract passed on that exact tree.
- 18.6.2 canonical verification passed on that exact tree.
- Tracked tree was clean and local/upstream were synchronized at the 18.6.2 seal.
- 18.6.3 implementation and focused regressions are now committed on the stage branch; no 18.6.3 PASS is recorded yet.

## Next required action

1. Sync local workspace to the exact current stage-branch HEAD before editing locally.
2. Run Pint write then `--test` on the 18.6.3 contract/adapter/workbench/test files.
3. Run `tests/Feature/Providers/YouTubeVideoDestinationTest.php`, then existing destination preference and Recording media regression tests.
4. Run focused PHPStan on the changed production PHP files.
5. Run `./songchart impact --diff`, then `./songchart reconcile` and commit only expected generated authority changes if any.
6. Run `./songchart impact --verify`; only then advance to candidate/canonical closure.

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
