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
- Candidate closure: 18.6.2 closed on exact head `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`; any 18.6.3 tracked change requires new closure evidence.
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

This slice makes provider media evidence trustworthy enough to participate in public selection without treating provider media as canonical Recording identity:

- inspect the existing YouTube search/approval/write path and normalize verification evidence before persistence;
- require explicit public privacy evidence before a destination can be considered available;
- preserve embeddability separately from availability so a public non-embeddable video may remain outbound-only;
- ensure verification refresh updates `last_checked_at` and observed media state through existing authorized/audited write boundaries;
- preserve provenance such as provider resource ID, channel/title and verification evidence without promoting them into canonical identity;
- fail closed when provider response omits or contradicts privacy/availability/embeddability evidence;
- do not add a scheduler, new provider, new canonical identifier, or opaque ranking behavior in this slice.

## Current blockers / risks

- YouTube Video identity must remain external media evidence and must never become canonical Recording identity.
- Privacy, availability and embeddability are distinct facts; do not collapse them into one boolean.
- Missing verification evidence must remain unknown/fail-closed, not silently public/available.
- Refreshing verification must use the existing provider credential, quota/rate, authorization and audit boundaries.
- Do not duplicate the 30-day freshness policy outside `ProviderDestinationPreference`.
- A public but non-embeddable destination may be outbound-only rather than unavailable.
- New route/schema/provider fields cannot be introduced silently.

## Latest focused evidence

- Stage 18.6.1 exact sealed head: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Stage 18.6.2 exact sealed head: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- 18.6.2 candidate verification contract passed on that exact tree.
- 18.6.2 canonical verification passed on that exact tree.
- Tracked tree was clean and local/upstream were synchronized at the 18.6.2 seal.
- 18.6.3 starts after that seal and therefore requires new focused/candidate/canonical evidence before it can be called closed.

## Next required action

1. Inventory the existing YouTube destination search, candidate presentation, approval/write service, provider adapter and focused tests.
2. Identify the single write owner for verification evidence and the normalized fields already available in `provider_destinations`.
3. Add focused regression for public/private/unknown privacy, embeddable/non-embeddable, unavailable/missing resource and verification refresh timestamps.
4. Implement the smallest convergence in the existing adapter/write path; do not add schema or routes unless existing authority proves insufficient.
5. Run Pint, focused tests, PHPStan, `./songchart impact --diff`, `./songchart reconcile`, then `./songchart impact --verify` before closure.

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
