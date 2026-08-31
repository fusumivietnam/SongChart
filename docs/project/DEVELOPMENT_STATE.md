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

The smallest justified remediation mutation is now implemented and pending focused verification:

- the existing `POST /admin/providers/{provider}/operations` mutation route is reused; no new route surface was added;
- the route keeps the existing `can:manage-providers` and `password.confirm` protections;
- `ProviderMutationController` accepts the bounded `destination_reverify` action and delegates immediately to `ProviderMutationService`;
- `ProviderMutationService::reverifyDestination()` locks provider + destination, verifies ownership, supports only YouTube, preserves idempotency, calls `YouTubeDestinationWorkbench::reverify()`, and records both immutable `ProviderOperationAudit` evidence and the existing `PrivilegedAuditLogger` event;
- Admin destination attention rows expose a per-destination `Kiểm tra lại` form only for YouTube rows currently requiring attention;
- re-verification preserves canonical entity linkage, review state and original `verified_at`; it refreshes provider-observed evidence and `last_checked_at` only through the existing workbench owner;
- focused regression covers provider ownership, audit before/after evidence, idempotent repeat submission and a private/non-embeddable refresh outcome;
- no generic CRUD, bulk remediation, scheduler, schema expansion, provider breadth or public mutation route was introduced.

## Current blockers / risks

- Controllers remain transport adapters only; no direct destination persistence/query logic may be introduced there.
- Re-verification must go through the existing provider adapter/workbench so credential, quota/rate and provider evidence semantics are preserved.
- Admin actions must remain behind existing authorization and audit boundaries; no unaudited remediation shortcut.
- A remediation attempt may legitimately result in private/unavailable/outbound-only state; success means evidence was refreshed, not that the destination became public-playable.
- Stale/unavailable evidence must not be silently deleted to make the attention panel look green.
- Do not duplicate freshness/privacy/eligibility semantics outside `ProviderDestinationPreference` and the provider verification owner.
- Live provider smoke testing is useful for release confidence but must not become a network/quota-dependent canonical gate.
- New route/schema/provider fields cannot be introduced silently.

## Latest focused evidence

- Stage 18.6.1 exact sealed head: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Stage 18.6.2 exact sealed head: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- Stage 18.6.3 exact sealed head: `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`.
- Stage 18.6.4 exact sealed head: `76f35c4fcf048ddccfb239c2845e8e872587a36c`.
- 18.6.4 candidate verification contract passed on that exact tree.
- 18.6.4 canonical verification passed on that exact tree.
- Tracked tree was clean and local/upstream were synchronized at the 18.6.4 seal.
- 18.6.5 implementation and focused regression are committed on the stage branch; no 18.6.5 PASS is recorded yet.

## Next required action

1. Sync local workspace to the current stage-branch HEAD.
2. Run Pint write + `--test` on `ProviderMutationController`, `ProviderMutationService` and `ProviderDestinationRemediationTest`.
3. Run the new remediation feature regression plus existing YouTube destination and ProviderDestinationAttention regressions.
4. Run focused PHPStan on the changed production PHP.
5. Manually smoke the real Admin provider URL once with a real configured YouTube credential, verifying the attention state before/after and the audit entry; do not use live provider access as a CI/canonical gate.
6. Run `./songchart impact --diff`, `./songchart reconcile`, then `./songchart impact --verify`; only after PASS advance to candidate/canonical closure.

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
