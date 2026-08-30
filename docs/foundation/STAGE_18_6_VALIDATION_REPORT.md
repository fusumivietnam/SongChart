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

### 18.6.3 — YouTube/media verification quality convergence

Implemented source intent:

- `VideoDestinationDiscovery` separates public approval verification from exact-resource inspection;
- YouTube search remains actionable/public-only, while inspection preserves observed privacy and embeddability evidence for re-verification;
- approval requires explicit public privacy evidence but permits public non-embeddable media as outbound-only evidence;
- `YouTubeDestinationWorkbench::reverify()` refreshes observed provider metadata and `last_checked_at` without changing canonical Recording linkage, review state, or original `verified_at` approval evidence;
- missing/inaccessible YouTube resources remain persisted as historical evidence and are marked fail-closed/unavailable rather than silently deleted;
- provider resource/channel/title evidence remains external media evidence and never becomes canonical Recording identity;
- no route, scheduler, schema or provider expansion was introduced.

Observed closure:

- Pre-closure impact verification: PASS.
- Candidate verification contract: PASS.
- Canonical verification: PASS.
- Exact closed HEAD: `1a88d3915cef69a33845d4f1b2db34b103dcf6ff`.
- Tracked tree: clean at seal.
- Local/upstream relationship: synchronized at seal.

Any tracked 18.6.4 change is a new tree and does not reuse 18.6.3 closure evidence.

## Active slice

### 18.6.4 — Public selected-destination projection + explainability

Implemented source intent now under verification:

- `PublicProviderDestinationProjection` defines explicit public states `playable`, `outbound_only`, and `no_selection` plus bounded reason codes;
- `EloquentProviderDestinationSelector::project()` derives no-selection reason codes from `ProviderDestinationPreference::publicEligibilityIssues()` and uses the same preference owner for deterministic selection and embeddability;
- the existing `select()` method remains as a compatibility facade over the new projection so other consumers do not receive a policy fork;
- `RecordingMediaExperience` exposes public-safe state, reason codes, selection explanation and provider provenance without leaking raw evidence, credentials, quota/rate state, operator review/audit metadata or persistence internals;
- the public media Blade component renders playable, outbound-only and no-selection states explicitly;
- provider media remains external evidence and does not mutate canonical Recording identity;
- no route, migration, scheduler, provider expansion or opaque ranking behavior is introduced.

Focused regression added for:

- playable fresh approved embeddable YouTube destination;
- outbound-only fresh approved public non-embeddable destination;
- stale destination producing `no_selection` plus freshness reason code;
- private destination producing `no_selection` plus privacy reason code without exposing its URL;
- newer private destination not shadowing the deterministic eligible public winner.

## Focused verification evidence

Pending on the current 18.6.4 tree. Do not record PASS until observed.

Required focused checks:

```text
Pint write on changed 18.6.4 PHP
Pint --test on changed 18.6.4 PHP
./songchart dev test tests/Feature/Catalog/RecordingMediaExperienceTest.php
./songchart dev test tests/Unit/Providers/ProviderDestinationPreferenceTest.php
focused PHPStan on PublicProviderDestinationProjection, EloquentProviderDestinationSelector and RecordingMediaExperience
./songchart impact --diff
./songchart reconcile
./songchart impact --verify
```

## Known correctives during Stage 18.6

- Repository-state governance exposed the Stage 18.6 current-stage marker/checkpoint heading drift; the owner document was corrected rather than weakening the verifier.
- Candidate metadata was advanced from Stage 18.5 to Stage 18.6 before generated project context was committed.
- Formatter drift and PHPStan type-contract errors were corrected at source before 18.6.1 closure.
- 18.6.2 verification corrected a redundant nullsafe enum access and an invalid overlength ULID test fixture without weakening PHPStan or schema authority.
- 18.6.3 verification corrected a timestamp round-trip precision defect in its regression fixture by comparing persisted `verified_at` evidence; production timestamp semantics and schema were left unchanged.

## Candidate / canonical closure

- 18.6.1 exact closure: `17d9606a9094f879d9a467cf4ba47e7753bdecb0` PASS.
- 18.6.2 exact closure: `f06e99190e9dc4b14ad9047f30af0dd87b10fea1` PASS.
- 18.6.3 exact closure: `1a88d3915cef69a33845d4f1b2db34b103dcf6ff` PASS.
- 18.6.4 candidate: not run on the current changed tree.
- 18.6.4 canonical: not run on the current changed tree.
- 18.6.4 exact closed HEAD: not established.

Any later tracked change invalidates closure evidence for the previous exact HEAD and must be reflected here before calling the new tree closed.
