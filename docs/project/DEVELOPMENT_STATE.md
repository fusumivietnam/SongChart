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
- Candidate closure: 18.6.3 closed on exact head `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`; any 18.6.4 tracked change requires new closure evidence.
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
        +--> [IN PROGRESS] 18.6.4 public selected-destination projection + explainability
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
- 18.6.3 converges YouTube destination evidence around explicit resource inspection, public approval verification, outbound-only non-embeddable handling, and fail-closed re-verification without changing canonical Recording identity.
- 18.6.3 pre-closure impact verification, candidate verification and canonical verification passed on exact clean/pushed head `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`.
- Workflow hardening from 18.5.1 remains cross-stage engineering authority.

## In progress

### 18.6.4 — Public selected-destination projection + explainability

This slice makes the existing destination eligibility/preference decision observable to public read models without creating a second selection policy:

- keep `ProviderDestinationPreference` as the single semantic owner of public eligibility and deterministic ordering;
- project the selected destination into a stable public-facing read shape that distinguishes playable embed from outbound-only media;
- expose bounded, non-sensitive explainability for why the selected destination won and why otherwise relevant destinations were not public-eligible;
- preserve provider provenance needed by the public product while avoiding internal credential, quota, review-operator or audit detail leakage;
- ensure no public read model reimplements freshness, privacy, provider approval, review-state or URL-safety rules;
- keep provider media external to canonical Recording identity;
- avoid new schema, provider, scheduler or opaque ranking behavior in this slice.

## Current blockers / risks

- Explainability must reuse stable reason semantics from `ProviderDestinationPreference`; public/read projections must not become a second policy owner.
- Public output must distinguish playable embed from outbound-only destination rather than collapsing them into a generic URL.
- Internal-only provider/operator evidence must not leak through public projection.
- YouTube Video identity must remain external media evidence and must never become canonical Recording identity.
- Missing verification evidence must remain unknown/fail-closed, not silently public/available.
- Do not duplicate the 30-day freshness policy outside `ProviderDestinationPreference`.
- New route/schema/provider fields cannot be introduced silently.

## Latest focused evidence

- Stage 18.6.1 exact sealed head: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Stage 18.6.2 exact sealed head: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- Stage 18.6.3 exact sealed head: `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`.
- 18.6.3 pre-closure impact verification passed on that exact tree.
- 18.6.3 candidate verification contract passed on that exact tree.
- 18.6.3 canonical verification passed on that exact tree.
- Tracked tree was clean and local/upstream were synchronized at the 18.6.3 seal.
- 18.6.4 starts after that seal and therefore requires new focused/candidate/canonical evidence before it can be called closed.

## Next required action

1. Inventory the current public Recording media/read projection and every consumer of selected provider destination data.
2. Identify the smallest provider-neutral read DTO/projection boundary that can expose selected destination mode, safe public provenance and explainability without duplicating policy.
3. Add regression for playable selection, outbound-only selection, no eligible destination, deterministic winner, and stable public reason exposure without internal evidence leakage.
4. Implement the projection through the existing application/support read path; do not add schema or routes unless current authority proves insufficient.
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
