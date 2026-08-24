# Stage 17.9.4 Task Contract — Candidate Stage Consistency Closure

Status: task contract.

## Goal

Close the candidate-evidence stage identity gap so canonical verification cannot pass or record evidence when `candidate-verification.json` names a stage different from the repository current-stage authority in `README.md`.

## Non-goals

- No application runtime or product behavior changes.
- No database migration or provider/API changes.
- No change to the required canonical gates or closure rule.
- No automatic claim of canonical verification in this source environment.

## Acceptance criteria

- `scripts/verify-candidate-contract.php` resolves the current stage from `README.md`.
- Candidate verification fails closed when the manifest stage differs from the README current stage.
- The current unverified candidate manifest is reset to Stage 17.9.4 with all gates `not_run` and `closure_ready=false`.
- Repository-state and official-source governance checks remain green.

## Affected modules and boundaries

- Release/candidate governance only.
- Candidate verification authority and current-stage documentation metadata.
- No HTTP routes, schema, provider, security, or user-facing runtime surface.

## Expected files

- `scripts/verify-candidate-contract.php`
- `candidate-verification.json`
- `README.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_17_9_4_TASK_CONTRACT.md`
- `docs/foundation/STAGE_17_9_4_VALIDATION_REPORT.md`

## Allowed incidental files

- None.

## Scope deviations

- None.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/stack/candidate-verification-contract.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| Laravel | `^13.0` | `composer.json` / `composer.lock` |
| PHP canonical runtime | 8.5 | `PROJECT_AUTHORITY.md` |
| PostgreSQL canonical authority | 18.x | `PROJECT_AUTHORITY.md` |

### Official external sources

No external source is required. This corrective changes repository-owned verification logic and documentation only.

### Native capability assessment

- Capability owner: SongChart release/candidate governance.
- Native/first-party capability available: yes.
- Selected official API or primitive: existing README current-stage pointer and candidate manifest verifier.
- Why it satisfies the requirement: both authorities already exist; the missing behavior is a consistency assertion between them.

### Custom implementation justification

- Custom code required: yes, narrowly inside the existing candidate verifier.
- Missing official behavior: the verifier checked gate statuses but not stage identity.
- Narrow custom boundary: compare manifest `stage` against README current stage and fail closed on mismatch.
- Framework primitives reused: none required; this is a standalone PHP governance script.
- Non-goals: no parallel verifier, no new manifest format, no release-gate weakening.

## Domain contract and use-case data surface

- Actor and preconditions: release/canonical verification process.
- Input types and identifier formats: dotted numeric stage string such as `17.9.4`.
- Exact entity fields read: none.
- Exact entity fields written: none at runtime.
- Null/unknown semantics: missing/unparseable current stage or missing manifest stage is a verification failure.
- Output DTO/presentation contract: process exit status and diagnostic text only.
- Route/API contract: none.
- Relationship invariants: candidate manifest stage must equal repository current stage.
- Contract changes required: no schema/domain contract change.
- `domain-contracts.json` entries affected: none.

## Security, authorization, and data impact

No authentication, authorization, sensitive-data, retention, or provider-policy impact.

## Verification plan

- Impact lane: resolve candidate-verification/release-pipeline consumers.
- Focused implementation gates: candidate contract, repository state, official sources, repository compiler/authority where runnable.
- Stage closure owner: `composer stage:verify`.
- Canonical closure owner: `composer canonical:verify`.
- Packaging owner: `composer release:package`.
- Explicitly avoided duplicate/nested gates: do not claim canonical Docker closure from the PHP 8.4 authoring environment.

## Tests and verification

- Positive: current Stage 17.9.4 manifest passes the candidate contract.
- Negative regression: a temporary stale stage value must make the candidate verifier fail.
- `php scripts/verify-repository-state.php`.
- `php scripts/verify-official-sources.php`.
- Canonical PHP 8.5/PostgreSQL 18/Docker closure remains not performed in this environment.

## Documentation impact

Advance README/history/current-stage task and validation records to Stage 17.9.4.

## Rollback

Revert the six listed files. No data/schema rollback is required.
