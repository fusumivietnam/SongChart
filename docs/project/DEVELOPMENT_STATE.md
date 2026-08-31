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
- Latest sealed slice: 18.6.5 closed on exact head `2e4b1752a6df4bc8eb00a40334608e582ee75efd`; any final-closure tracked change requires new exact-tree evidence.
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
        +--> [DONE] 18.6.5 Admin remediation mutation UX where justified
        |
        `--> [IN PROGRESS] FINAL focused QA -> candidate -> canonical -> exact-head delivery
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
- 18.6.5 adds the narrow operator remediation needed for single-destination YouTube re-verification through the existing Admin mutation, authorization, workbench and audit boundaries without generic CRUD or a new route.
- 18.6.5 focused verification, candidate verification and canonical verification passed on exact clean/pushed head `2e4b1752a6df4bc8eb00a40334608e582ee75efd`.
- Workflow hardening from 18.5.1 remains cross-stage engineering authority.

## In progress

### Stage 18.6 final closure

No additional product behavior is planned in this closure slice. The remaining work is to verify the complete Stage 18.6 contract on one exact tree and deliver that tree through the governed PR path:

- reconcile any generated authority caused by this final checkpoint before closure evidence is collected;
- run final impacted/focused QA spanning destination preference, YouTube verification, Admin attention/remediation and public Recording media projection;
- run PostgreSQL-authoritative quality as required by impact/candidate/canonical contracts;
- require `./songchart impact --verify` PASS before candidate;
- require candidate and canonical PASS on the final exact tree;
- require clean tracked state, exact pushed HEAD and local/upstream synchronization;
- create/refresh the Stage 18.6 PR only after exact-head closure, then require PR CI to target that same SHA before merge;
- do not move 18.6 chronology to Development History or remove it from the roadmap until governed acceptance on `main`.

## Current blockers / risks

- Any tracked change after canonical PASS invalidates final Stage 18.6 closure and requires rerunning closure on the new tree.
- Final QA must not introduce a new policy owner, route, schema, provider expansion or workflow mechanism merely to make closure easier.
- Live provider smoke testing is useful release-confidence evidence but remains non-canonical because network/quota/credentials are nondeterministic.
- Provider destination/media evidence remains external to canonical Recording identity.
- Unknown availability/freshness remains fail-closed.
- Public projection must not leak privileged provider/operator evidence.

## Latest focused evidence

- Stage 18.6.1 exact sealed head: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Stage 18.6.2 exact sealed head: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- Stage 18.6.3 exact sealed head: `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`.
- Stage 18.6.4 exact sealed head: `76f35c4fcf048ddccfb239c2845e8e872587a36c`.
- Stage 18.6.5 exact sealed head: `2e4b1752a6df4bc8eb00a40334608e582ee75efd`.
- 18.6.5 candidate verification contract passed on that exact tree.
- 18.6.5 canonical verification passed on that exact tree.
- Tracked tree was clean and local/upstream were synchronized at the 18.6.5 seal.
- This final documentation checkpoint is a new tree and therefore requires fresh final-stage closure evidence.

## Next required action

1. Sync local workspace to the exact current stage-branch HEAD.
2. Run `./songchart impact --diff` to derive final changed-tree verification needs.
3. Run `./songchart reconcile`; commit only expected generated authority changes if any.
4. Require generated authority clean, then run `./songchart impact --verify`.
5. Run final focused regressions when selected by impact, with explicit attention to destination preference, YouTube verification, Admin attention/remediation and public Recording media projection.
6. Run `./songchart candidate`, then `./songchart verify` on the same final exact tree.
7. Confirm exact HEAD, clean tracked state, ahead/behind 0, push exact HEAD, then use that SHA for PR CI/merge acceptance.

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
