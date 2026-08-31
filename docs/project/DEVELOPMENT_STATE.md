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
- Candidate closure: 18.6.4 closed on exact head `76f35c4fcf048ddccfb239c2845e8e872587a36c`; any 18.6.5 tracked change requires new closure evidence.
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
        +--> [DONE] 18.6.3 YouTube/media verification quality convergence
        |
        +--> [DONE] 18.6.4 public selected-destination projection + explainability
        |
        +--> [IN PROGRESS] 18.6.5 Admin remediation mutation UX where justified
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
- 18.6.3 converges YouTube destination evidence around explicit resource inspection, public approval verification, outbound-only non-embeddable handling, and fail-closed re-verification without changing canonical Recording identity.
- 18.6.3 pre-closure impact verification, candidate verification and canonical verification passed on exact clean/pushed head `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`.
- 18.6.4 exposes provider-neutral public destination states `playable`, `outbound_only`, and `no_selection` with bounded explainability sourced from `ProviderDestinationPreference` rather than a second policy owner.
- 18.6.4 candidate verification and canonical verification passed on exact clean/pushed head `76f35c4fcf048ddccfb239c2845e8e872587a36c`.
- Workflow hardening from 18.5.1 remains cross-stage engineering authority.

## In progress

### 18.6.5 — Admin remediation mutation UX where justified

This slice adds only narrowly justified operator mutations over the read-only destination attention state already delivered in 18.6.2:

- inventory the existing Admin provider operations routes/controllers/actions, authorization gates and audit write path before adding any remediation control;
- reuse the existing `YouTubeDestinationWorkbench::reverify()` write owner for YouTube destination refresh rather than persisting directly from a controller/view;
- expose re-verification only where the destination/provider combination is actually supported and the operator already has the required Admin permission;
- record remediation through the existing audit boundary and preserve provider credential/quota/rate protections;
- keep stale/private/missing destination evidence persisted after remediation so operators can understand the observed result instead of silently deleting or replacing evidence;
- do not add generic approve/reject/delete/provider-switch mutations merely for UI completeness; every mutation must have a concrete operational need and an existing semantic owner;
- no public route, scheduler, bulk mutation, schema expansion or canonical Recording identity mutation in this slice.

## Current blockers / risks

- Controllers remain transport adapters only; no direct destination persistence/query logic may be introduced there.
- Re-verification must go through the existing provider adapter/workbench so credential, quota/rate and provider evidence semantics are preserved.
- Admin actions must remain behind existing authorization and audit boundaries; no unaudited remediation shortcut.
- A remediation attempt may legitimately result in private/unavailable/outbound-only state; success means evidence was refreshed, not that the destination became public-playable.
- Stale/unavailable evidence must not be silently deleted to make the attention panel look green.
- Do not duplicate freshness/privacy/eligibility semantics outside `ProviderDestinationPreference` and the provider verification owner.
- New route/schema/provider fields cannot be introduced silently.

## Latest focused evidence

- Stage 18.6.1 exact sealed head: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Stage 18.6.2 exact sealed head: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- Stage 18.6.3 exact sealed head: `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`.
- Stage 18.6.4 exact sealed head: `76f35c4fcf048ddccfb239c2845e8e872587a36c`.
- 18.6.4 candidate verification contract passed on that exact tree.
- 18.6.4 canonical verification passed on that exact tree.
- Tracked tree was clean and local/upstream were synchronized at the 18.6.4 seal.
- 18.6.5 starts after that seal and therefore requires new focused/candidate/canonical evidence before it can be called closed.

## Next required action

1. Inventory existing Admin provider operations routes/controllers/views, authorization middleware/policies, audit service and `YouTubeDestinationWorkbench` call sites.
2. Identify the smallest remediation mutation justified by current attention states; default target is single-destination YouTube re-verification through the existing write owner.
3. Add regression for authorization, audit evidence, successful refresh, private/unavailable refresh outcomes, unsupported-provider fail-closed behavior and preservation of canonical linkage/review state.
4. Add the smallest Admin UX/action wiring needed for that remediation; avoid generic CRUD or bulk actions.
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
