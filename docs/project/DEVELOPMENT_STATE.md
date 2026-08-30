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
- Candidate closure: not started.
- Strategy: improve destination/media eligibility, deterministic preference, freshness/provenance and operator visibility over existing provider infrastructure before considering provider breadth.

## Stage map

```text
18.6 PROVIDER DESTINATION & MEDIA QUALITY
        |
        +--> [DONE] inventory destination/media authority + existing runtime behavior
        |
        +--> [IN PROGRESS] 18.6.1 deterministic eligibility/preference contract
        |
        +--> [NEXT] freshness + stale/unavailable operational state
        |
        +--> [NEXT] YouTube/media verification quality convergence
        |
        +--> [NEXT] public selected-destination projection + explainability
        |
        +--> [NEXT] Admin attention/remediation UX
        |
        `--> [FINAL] focused QA -> candidate -> canonical -> exact-head delivery
```

## Done

- Stage 18.5/18.5.1 accepted through PR #14 after exact-head candidate/canonical closure and green PR CI.
- Stage 18.6 branch created directly from accepted `main` merge commit `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Stage 18.6 task contract initialized with provider-neutral destination/media quality boundaries and explicit non-goals.
- Destination/media inventory confirmed the existing `provider_destinations` schema can express the 18.6.1 eligibility/preference slice without schema expansion.
- 18.6.1 source now routes public Recording media selection through a provider-neutral eligibility/preference policy instead of selecting the latest approved destination directly.
- Workflow hardening from 18.5.1 remains cross-stage engineering authority; 18.6 does not reopen that framework unless a new evidence-backed failure class appears.

## In progress

### 18.6.1 — Destination Eligibility + Deterministic Preference

Public destination selection must fail closed on provider approval/enabled state, destination review state, public privacy state, freshness and safe HTTPS outbound URL before deterministic ranking. Embeddability is a preference/playability signal after eligibility, not a substitute for public eligibility.

Current ranking order for eligible destinations:

```text
EMBEDDABLE / PLAYABLE
        ↓
LAST CHECKED RECENCY
        ↓
MATCH SCORE
        ↓
VERIFIED RECENCY
        ↓
PROVIDER KEY + DESTINATION ID STABLE TIE-BREAK
```

Focused verification is in progress. No Stage 18.6 candidate/canonical closure is recorded yet.

## Current blockers / risks

- Do not equate provider media resources (especially YouTube Video) with canonical Recording identity.
- Do not prefer a destination that is private, unavailable, stale beyond accepted policy, unapproved or otherwise policy-invalid.
- Non-embeddable but otherwise eligible destinations may remain safe outbound destinations; they must not be presented as playable/embed destinations.
- Unknown freshness/availability must not silently mean fresh/available.
- Provider expansion must not precede an evidence-quality use case and official API capability review.
- Public selection must remain deterministic and explainable; no fabricated popularity or opaque recommendation score.
- Admin remediation must use existing authorization/audit/write boundaries and Vietnamese operator-facing language.
- New route/schema/provider fields cannot be introduced silently.

## Latest evidence

- Stage 18.5/18.5.1 exact sealed head: `67df5ae72f9dbdc29c43e7afbc7e645203e37cff`.
- Candidate verification contract passed on that exact tree.
- Canonical verification passed on that exact tree.
- GitHub Actions PR workflow run #159 completed successfully for that head.
- PR #14 merged to `main` as `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Stage 18.6 bootstrap branch created from that accepted merge commit.
- Stage 18.6.1 source and focused regressions are committed on the stage branch; broad verification remains pending.

## Next required action

1. Sync local workspace to the latest `stage-18.6-provider-destination-media-quality` HEAD.
2. Run the focused 18.6.1 Unit and Recording media feature tests.
3. Run Pint/PHPStan and `./songchart impact --diff`.
4. Run `./songchart impact --verify` after focused checks pass.
5. Record only verification actually observed in `STAGE_18_6_VALIDATION_REPORT.md`; do not advance to 18.6.2 until the 18.6.1 verification lane is green.

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
