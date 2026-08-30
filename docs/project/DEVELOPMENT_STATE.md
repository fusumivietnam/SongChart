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
- Candidate closure: 18.6.1 closed on exact head `17d9606a9094f879d9a467cf4ba47e7753bdecb0`; any 18.6.2 tracked change requires new closure evidence.
- Strategy: improve destination/media eligibility, deterministic preference, freshness/provenance and operator visibility over existing provider infrastructure before considering provider breadth.

## Stage map

```text
18.6 PROVIDER DESTINATION & MEDIA QUALITY
        |
        +--> [DONE] inventory destination/media authority + existing runtime behavior
        |
        +--> [DONE] 18.6.1 deterministic eligibility/preference contract
        |
        +--> [IN PROGRESS] 18.6.2 freshness + stale/unavailable operational state
        |
        +--> [NEXT] YouTube/media verification quality convergence
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
- Destination/media inventory confirmed the existing `provider_destinations` schema can express the 18.6.1/18.6.2 quality slices without schema expansion.
- 18.6.1 routes public Recording media selection through provider-neutral fail-closed eligibility and deterministic preference.
- 18.6.1 candidate and canonical verification passed on exact clean/pushed head `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Workflow hardening from 18.5.1 remains cross-stage engineering authority.

## In progress

### 18.6.2 — Freshness + stale/unavailable operational state

This slice exposes destination quality to operators without introducing a second freshness policy owner:

- `ProviderDestinationPreference` owns public eligibility and freshness semantics and now emits explainable issue codes;
- Admin destination attention reuses those issue codes to classify `Chưa kiểm tra`, `Quá hạn kiểm tra`, `Không khả dụng`, `Sẵn sàng phát` and `Chỉ mở ngoài`;
- unknown `last_checked_at` remains fail-closed;
- stale destinations remain persisted evidence but are excluded from public selection;
- non-embeddable but otherwise eligible destinations remain outbound-only, not unavailable;
- no route, migration, scheduler or remediation mutation is added in this slice.

## Current blockers / risks

- Do not duplicate the 30-day freshness window in Admin presentation/read models.
- Do not equate provider media resources (especially YouTube Video) with canonical Recording identity.
- Unknown freshness/availability must not silently mean fresh/available.
- A destination may be stale evidence without being deleted; operator attention and public eligibility are separate concerns.
- Provider expansion must not precede an evidence-quality use case and official API capability review.
- Admin remediation mutations, if later added, must preserve authorization/audit/write boundaries and Vietnamese operator-facing language.
- New route/schema/provider fields cannot be introduced silently.

## Latest focused evidence

- Stage 18.6.1 exact sealed head: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Candidate verification contract passed on that exact tree.
- Canonical verification passed on that exact tree.
- Tracked tree was clean and local/upstream were synchronized at the 18.6.1 seal.
- 18.6.2 starts after that seal and therefore requires new focused/candidate/canonical evidence before it can be called closed.

## Next required action

1. Sync local workspace to the latest stage branch before editing locally.
2. Run Pint write + `--test` on exact 18.6.2 PHP files.
3. Run `tests/Unit/Providers/ProviderDestinationPreferenceTest.php` and `tests/Feature/Providers/ProviderDestinationAttentionTest.php`.
4. Run focused PHPStan, then `./songchart impact --diff` and reconcile any newly surfaced authority/consumer.
5. Run `./songchart reconcile`, commit expected generated-only changes, then `./songchart impact --verify`.
6. Record only observed verification in `STAGE_18_6_VALIDATION_REPORT.md` before candidate/canonical closure.

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
