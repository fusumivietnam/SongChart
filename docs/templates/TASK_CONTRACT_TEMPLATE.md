# Stage X.Y Task Contract — Title

Status: task contract.

## Goal

Describe the single outcome this task owns.

## Non-goals

- Explicitly excluded behavior.

## Acceptance criteria

- Observable and testable requirement.

## Affected modules and boundaries

- Modules, routes, schema, provider, security, operations, and documentation affected.

## Expected files

- List every planned production, test, documentation, configuration, and verifier file.

## Planned impact

- Planned changed paths:
- Expected semantic authorities:
- Expected reverse verification consumers:
- Expected focused checks:
- Record `./songchart impact <planned-paths...>` evidence before implementation when paths are known.

## Allowed incidental files

- List formatter-only, generated, lockfile, manifest, or delivery metadata files that may change without expanding product scope.

## Source hygiene and verifier ownership before commit

Before committing authoritative source/contract/test changes:

- run Pint in write mode on the exact changed PHP files, then inspect the formatter diff;
- require Pint `--test` to pass after write-mode normalization;
- when adding `scripts/verify-*.php` or `tests/Architecture/*.php`, register it under exactly one existing rule in `docs/project/engineering/verification-consumer-graph.json` in the same logical source change;
- run repository compiler/consumer ownership verification before broad quality verification;
- do not commit generated repository authority until the authoritative source/contract/consumer tree is final for that slice.

Required order:

```text
SOURCE / CONTRACT / TEST CHANGE
        ↓
PINT WRITE ON CHANGED PHP
        ↓
PINT --TEST
        ↓
VERIFIER OWNER (when applicable)
        ↓
SOURCE COMMIT
        ↓
./songchart reconcile
        ↓
GENERATED-ONLY COMMIT (when needed)
        ↓
./songchart impact --verify
```

## Post-diff impact and scope deviations

- Run `./songchart impact --diff` after implementation and before closure.
- Record newly impacted authorities/consumers/checks that were not in the planned impact.
- Record every changed file outside the planned surface with reason, impact, and verification.
- Use `None` only when actual diff confirms no deviations.

## Command mutation envelopes

For every workflow command introduced or changed, classify its allowed repository mutation surface before implementation:

| Command | Envelope | Allowed tracked mutation | Interactive output allowed |
|---|---|---|---|
| Example | `read-only` / `generated-only` / `runtime-only` / `closure` | Exact paths or `none` | yes / no |

Rules:

- `read-only`: no tracked source mutation.
- `generated-only`: may change only explicitly governed generated paths and must show the diff without committing unless the owning contract says otherwise.
- `runtime-only`: may change only ignored runtime evidence/state; tracked mutation is a failure.
- `closure`: may create runtime evidence but the verified tracked tree must remain unchanged.
- Non-interactive workflow commands must disable pagers/prompts unless interaction is an explicit part of the contract.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- Add applicable architecture, security, stack, module, and ADR authorities.

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| Laravel | Record the resolved version | `composer.lock` |

### Official external sources

| Owner | Official source | Capability supported | Reviewed on |
|---|---|---|---|
| Laravel | Official documentation/source reference | Exact API or behavior | YYYY-MM-DD |

### Native capability assessment

- Capability owner:
- Native/first-party capability available: yes / partial / no
- Selected official API or primitive:
- Why it satisfies the requirement:

### Custom implementation justification

- Custom code required: yes / no
- Missing official behavior:
- Narrow custom boundary:
- Framework primitives reused:
- Non-goals:

## Domain contract and use-case data surface

- Actor and preconditions:
- Input types and identifier formats:
- Exact entity fields read:
- Exact entity fields written:
- Null/unknown semantics:
- Output DTO/presentation contract:
- Route/API contract:
- Relationship invariants:
- Contract changes required: yes / no
- `domain-contracts.json` entries affected:

## Security, authorization, and data impact

- Authentication/authorization changes.
- Sensitive data, logging, retention, and provider-policy impact.

## Verification plan

- Planned impact lane: `./songchart impact <planned-paths...>`.
- Actual-diff lane: `./songchart impact --diff`.
- Changed-PHP formatter lane: Pint write mode on exact changed PHP paths, followed by Pint `--test`, before source commit.
- Verification-consumer ownership lane: exactly one owner for every new verifier/Architecture test before broad quality verification.
- Generated authority reconcile: `./songchart reconcile` only after authoritative source/contract/consumer changes are committed and stable for the slice.
- Pre-closure dynamic lane: `./songchart impact --verify`; this must fail fast on diff hygiene, known upstream divergence, Pint drift, stale compiler fingerprints or unowned verification consumers before expensive checks.
- Collect-all diagnostic lane: `./songchart audit` when broad static/governance feedback is useful.
- Focused implementation gates:
- Stage closure owner: `composer stage:verify` / `./songchart candidate`.
- Canonical closure owner: `composer canonical:verify` / `./songchart verify` or governed `./songchart close`.
- Packaging owner: `composer release:package`.
- Explicitly avoided duplicate/nested gates:

## Tests and verification

- Behavioral tests.
- Architecture/security tests.
- Pint and static analysis.
- PostgreSQL/runtime checks; optional compatibility checks must be labeled non-authoritative.
- Checks not performed must be listed explicitly.

## Post-closure seal

After canonical PASS:

- Record the exact closed HEAD SHA.
- Verify `git status --short` has no unexpected tracked mutation.
- Any source, authority, generated, formatter, rebase or amend change after canonical invalidates the prior closure evidence and requires reclosure.
- Push the exact closed HEAD and verify PR/required status checks target the latest SHA before merge.

## Documentation impact

- Authorities, ADRs, status, indexes, and operational notes to update.

## Delivery and handoff

- Current writer/owner for overlapping source surfaces:
- Handoff commit SHA when switching AI/device/writer:
- Local-only commits before handoff: must be `none`; push/integrate them before another writer performs remote/GitHub writes.
- Upstream relationship before handoff: branch must not be known-behind or diverged.
- Rebase/cherry-pick plan if branch diverged:
- Exact closed HEAD expected before PR merge:

## Rollback

- File, configuration, schema, and operational rollback boundaries.
