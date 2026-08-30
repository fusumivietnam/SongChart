# Stage 18.6 Validation Report — Provider Destination & Media Quality

Status: in progress. Record only verification actually observed for the active Stage 18.6 tree.

## Accepted baseline

- Stage 18.5/18.5.1 exact sealed head `67df5ae72f9dbdc29c43e7afbc7e645203e37cff` passed candidate and canonical verification.
- PR #14 passed GitHub Actions workflow run #159 and merged to `main` as `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Stage 18.6 branch was created from that accepted merge commit.

## Closed slice evidence

### 18.6.1 — Destination Eligibility + Deterministic Preference

Implemented source intent:

- public Recording destination selection uses provider-neutral eligibility + deterministic preference rather than latest `verified_at`;
- provider-disabled/unapproved, destination-unapproved, non-public/unknown privacy, stale/unknown freshness, and unsafe outbound URLs fail closed;
- otherwise eligible non-embeddable destinations remain outbound-only, never playable/embed media;
- provider destination evidence remains external media evidence and never defines canonical Recording identity.

Observed closure:

- Candidate verification contract: PASS.
- Canonical verification: PASS.
- Exact closed HEAD: `17d9606a9094f879d9a467cf4ba47e7753bdecb0`.
- Tracked tree: clean at seal.
- Local/upstream relationship: synchronized at seal.

Any tracked 18.6.2 change is a new tree and does not reuse the 18.6.1 closure evidence.

## Active slice

### 18.6.2 — Freshness + stale/unavailable operational state

Implemented source intent now under verification:

- `ProviderDestinationPreference` remains the single owner of the 30-day freshness and public eligibility semantics;
- the domain policy emits stable reason codes for provider approval/enabled state, review state, privacy, freshness and outbound URL safety;
- `ProviderDestinationAttention` is a read-only Admin projection over existing `provider_destinations` evidence;
- operator state distinguishes `Chưa kiểm tra`, `Quá hạn kiểm tra`, `Không khả dụng`, `Sẵn sàng phát` and `Chỉ mở ngoài`;
- unknown freshness is fail-closed; stale evidence stays persisted but is not public eligible;
- no migration, route, scheduler or remediation mutation is introduced by 18.6.2.

## Focused verification evidence

Pending on the current 18.6.2 tree. Do not record PASS until observed.

Required focused checks:

```text
./songchart composer exec pint -- <exact changed PHP>
./songchart composer exec pint -- --test <exact changed PHP>
./songchart dev test tests/Unit/Providers/ProviderDestinationPreferenceTest.php
./songchart dev test tests/Feature/Providers/ProviderDestinationAttentionTest.php
./songchart composer exec phpstan analyse
./songchart impact --diff
./songchart reconcile
./songchart impact --verify
```

## Known correctives during Stage 18.6

- Repository-state governance exposed the Stage 18.6 current-stage marker/checkpoint heading drift; the owner document was corrected rather than weakening the verifier.
- Candidate metadata was advanced from Stage 18.5 to Stage 18.6 before generated project context was committed.
- Formatter drift and PHPStan type-contract errors were corrected at source before 18.6.1 closure.

## Candidate / canonical closure

- 18.6.1 exact closure: `17d9606a9094f879d9a467cf4ba47e7753bdecb0` PASS.
- 18.6.2 candidate: not run on the current changed tree.
- 18.6.2 canonical: not run on the current changed tree.
- 18.6.2 exact closed HEAD: not established.

Any later tracked change invalidates closure evidence for the previous exact HEAD and must be reflected here before calling the new tree closed.
