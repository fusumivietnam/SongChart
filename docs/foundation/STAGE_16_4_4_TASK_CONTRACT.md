# Stage 16.4.4 Task Contract — Repository Reconciliation & Historical Closure

Status: task contract.

## Goal

Reconcile repository documentation/state authorities after Stage 16.1–16.4 corrective work, close missing historical governance pairs, and add an executable verifier that prevents current-stage/history drift.

## Non-goals

- No runtime feature.
- No database schema change.
- No provider or catalog mutation behavior.
- No rewrite of historical evidence to claim tests that were not run.

## Acceptance criteria

- README is the only current-stage pointer and identifies Stage 16.4.4.
- `START_HERE.md` contains no stale current-stage marker.
- Stage 16.3 is consistently named Catalog Administration.
- Stage 16.1 and 16.3 have task-contract/validation-report pairs.
- `docs/project/DEVELOPMENT_HISTORY.md` owns chronological Stage 16 history.
- `STAGE_12_CHANGE_MANIFEST.md` is explicitly historical/frozen.
- `composer repository-state:verify` enforces repository-state invariants and runs in `quality:verify`.
- An architecture test protects the verifier and authority ownership.

## Affected modules and boundaries

Documentation/governance, Composer quality scripts, one repository-state verifier, and one architecture test only.

## Expected files

- `README.md`
- `STAGE_12_CHANGE_MANIFEST.md`
- `composer.json`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `docs/project/DEVELOPMENT_HISTORY.md`
- `docs/foundation/STAGE_16_1_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_1_VALIDATION_REPORT.md`
- `docs/foundation/STAGE_16_3_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_3_VALIDATION_REPORT.md`
- `docs/foundation/STAGE_16_4_4_TASK_CONTRACT.md`
- `docs/foundation/STAGE_16_4_4_VALIDATION_REPORT.md`
- `scripts/verify-official-sources.php`
- `scripts/verify-repository-state.php`
- `tests/Architecture/RepositoryStateGovernanceTest.php`

## Allowed incidental files

- Changeset/full-source manifests, README/rollback notes, ZIP hashes, and `.changeset-backups/` created by delivery tooling.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/project/docs/ENGINEERING_WORKFLOW.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP | `^8.3` | `composer.json` |
| Composer scripts | repository-defined | `composer.json` |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Composer | `https://getcomposer.org/doc/articles/scripts.md` | Composer script aliases and ordered quality-gate execution | 2026-08-07 |

### Native capability assessment

- Capability owner: Composer for script orchestration; repository documentation governance for project-specific invariants.
- Native/first-party capability available: partial.
- Selected official API or primitive: Composer scripts.
- Why it satisfies the requirement: Composer can execute the verifier but cannot know SongChart-specific stage/history ownership rules.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: repository-specific current-stage, history, task/report, and manifest ownership checks.
- Narrow custom boundary: `scripts/verify-repository-state.php`.
- Framework primitives reused: Composer script orchestration and Pest architecture testing.
- Non-goals: replacing documentation governance or implementing a general changelog system.

## Domain contract and use-case data surface

- Actor and preconditions: developer/release process.
- Input types and identifier formats: repository Markdown/JSON/Composer metadata only.
- Exact entity fields read: none.
- Exact entity fields written: none.
- Null/unknown semantics: missing governance files/state markers are verifier failures.
- Output DTO/presentation contract: CLI pass/fail diagnostics.
- Route/API contract: none.
- Relationship invariants: none.
- Contract changes required: no.
- `domain-contracts.json` entries affected: none.

## Security, authorization, and data impact

No authentication, authorization, secret, provider, or persistent data changes.

## Tests and verification

- `php scripts/verify-documentation.php`.
- `php scripts/verify-official-sources.php`.
- `php scripts/verify-domain-contracts.php`.
- `php scripts/verify-repository-state.php`.
- Architecture test for repository-state governance.
- Pint/Larastan/SQLite/PostgreSQL/full release remain target-machine gates where vendor/runtime is required.

## Documentation impact

README, START_HERE, documentation governance/index, development history, historical manifest labels, and Stage 16 governance records.

## Rollback

Restore changed documentation/composer files and remove the new verifier/test/history/backfilled governance files. No database rollback is required.
