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

### 18.6.2 — Freshness + stale/unavailable operational state

Implemented source intent:

- `ProviderDestinationPreference` remains the single owner of the 30-day freshness and public eligibility semantics;
- the domain policy emits stable reason codes for provider approval/enabled state, review state, privacy, freshness and outbound URL safety;
- `ProviderDestinationAttention` is a read-only Admin projection over existing `provider_destinations` evidence;
- operator state distinguishes `Chưa kiểm tra`, `Quá hạn kiểm tra`, `Không khả dụng`, `Sẵn sàng phát` and `Chỉ mở ngoài`;
- unknown freshness is fail-closed; stale evidence stays persisted but is not public eligible;
- no migration, route, scheduler or remediation mutation is introduced by 18.6.2.

Observed closure:

- Pre-closure impact verification: PASS.
- Candidate verification contract: PASS.
- Canonical verification: PASS.
- Exact closed HEAD: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1`.
- Tracked tree: clean at seal.
- Local/upstream relationship: synchronized at seal.

Any tracked 18.6.3 change is a new tree and does not reuse 18.6.2 closure evidence.

## Active slice

### 18.6.3 — YouTube/media verification quality convergence

Implemented source intent now under verification:

- `VideoDestinationDiscovery` now exposes a provider inspection path distinct from approval verification;
- YouTube search still returns actionable public candidates, while exact-resource inspection preserves observed privacy and embeddability evidence even when the media is no longer public/playable;
- approval requires the resource to exist and be explicitly public, but public non-embeddable media is now allowed as outbound-only evidence instead of being rejected;
- `YouTubeDestinationWorkbench::reverify()` refreshes observed provider metadata and `last_checked_at` without changing canonical Recording linkage, review state, or the original human-verification timestamp;
- a missing/inaccessible YouTube resource is retained as historical destination evidence but is marked fail-closed with unknown privacy, non-embeddable state, `availability=unavailable`, and a refreshed `last_checked_at`;
- provider/channel/title/resource evidence stays external evidence and never becomes canonical Recording identity;
- no route, scheduler, schema or provider expansion is introduced by 18.6.3.

Focused regression added for:

- public embeddable candidate evidence;
- public non-embeddable approval as outbound-only;
- private re-verification preserving canonical linkage while becoming public-ineligible;
- missing resource re-verification becoming unavailable without deleting evidence or mutating canonical identity.

## Focused verification evidence

Pending on the current 18.6.3 tree. Do not record PASS until observed.

Required focused checks:

```text
./songchart composer exec pint -- app/Contracts/Providers/Destinations/VideoDestinationDiscovery.php app/Support/Providers/Destinations/YouTubeVideoDestinationDiscovery.php app/Support/Providers/Destinations/YouTubeDestinationWorkbench.php tests/Feature/Providers/YouTubeVideoDestinationTest.php
./songchart composer exec pint -- --test app/Contracts/Providers/Destinations/VideoDestinationDiscovery.php app/Support/Providers/Destinations/YouTubeVideoDestinationDiscovery.php app/Support/Providers/Destinations/YouTubeDestinationWorkbench.php tests/Feature/Providers/YouTubeVideoDestinationTest.php
./songchart dev test tests/Feature/Providers/YouTubeVideoDestinationTest.php
./songchart dev test tests/Unit/Providers/ProviderDestinationPreferenceTest.php
./songchart dev test tests/Feature/Catalog/RecordingMediaExperienceTest.php
./songchart composer exec phpstan analyse app/Contracts/Providers/Destinations/VideoDestinationDiscovery.php app/Support/Providers/Destinations/YouTubeVideoDestinationDiscovery.php app/Support/Providers/Destinations/YouTubeDestinationWorkbench.php
./songchart impact --diff
./songchart reconcile
./songchart impact --verify
```

## Known correctives during Stage 18.6

- Repository-state governance exposed the Stage 18.6 current-stage marker/checkpoint heading drift; the owner document was corrected rather than weakening the verifier.
- Candidate metadata was advanced from Stage 18.5 to Stage 18.6 before generated project context was committed.
- Formatter drift and PHPStan type-contract errors were corrected at source before 18.6.1 closure.
- 18.6.2 verification corrected a redundant nullsafe enum access and an invalid overlength ULID test fixture without weakening PHPStan or schema authority.

## Candidate / canonical closure

- 18.6.1 exact closure: `17d9606a9094f879d9a467cf4ba47e7753bdecb0` PASS.
- 18.6.2 exact closure: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1` PASS.
- 18.6.3 candidate: not run on the current changed tree.
- 18.6.3 canonical: not run on the current changed tree.
- 18.6.3 exact closed HEAD: not established.

Any later tracked change invalidates closure evidence for the previous exact HEAD and must be reflected here before calling the new tree closed.
