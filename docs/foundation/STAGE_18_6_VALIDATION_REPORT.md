# Stage 18.6 Validation Report — Provider Destination & Media Quality

Status: in progress. Record only verification actually observed for the active Stage 18.6 tree.

## Accepted baseline

- Stage 18.5/18.5.1 exact sealed head `67df5ae72f9dbdc29c43e7afbc7e645203e37cff` passed candidate and canonical verification.
- PR #14 passed GitHub Actions workflow run #159 and merged to `main` as `c27c7c90b04e9f2917a60c59e6805ee51c24618b`.
- Stage 18.6 branch was created from that accepted merge commit.

## Active slice

### 18.6.1 — Destination Eligibility + Deterministic Preference

Implemented source intent:

- replace public Recording destination selection by latest `verified_at` with provider-neutral eligibility + deterministic preference;
- fail closed for provider-disabled/unapproved, destination-unapproved, non-public/unknown privacy, stale/unknown freshness, or unsafe/non-HTTPS outbound URLs;
- allow an otherwise eligible non-embeddable destination only as outbound, never as playable/embed media;
- prefer playable destination, then last-check recency, match score, verification recency, and stable provider/destination tie-break;
- preserve provider destination evidence as external media evidence and never infer canonical Recording identity from a provider resource.

## Focused verification evidence

Pending re-run after the repository-state governance corrective.

Required focused checks for this slice:

```text
./songchart dev test tests/Unit/Providers/ProviderDestinationPreferenceTest.php
./songchart dev test tests/Feature/Catalog/RecordingMediaExperienceTest.php
./songchart composer exec phpstan analyse
./songchart impact --diff
./songchart impact --verify
```

No PASS is recorded here until observed on the current committed tree.

## Known corrective during verification

`RepositoryStateGovernanceTest` exposed a Stage 18.6 bootstrap metadata mismatch: the unique current-stage line in `docs/project/DEVELOPMENT_STATE.md` ended with punctuation after the closing backtick, so the governance regex resolved zero current-stage markers. The marker was restored to the executable contract form and this required Stage 18.6 validation report was created before re-running verification.

## Candidate / canonical closure

- Candidate: not run for Stage 18.6 current tree.
- Canonical: not run for Stage 18.6 current tree.
- Exact closed HEAD: not established.

Any later tracked change invalidates previous slice evidence and must be reflected here before stage closure.
