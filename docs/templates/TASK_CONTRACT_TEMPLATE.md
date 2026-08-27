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

## Post-diff impact and scope deviations

- Run `./songchart impact --diff` after implementation and before closure.
- Record newly impacted authorities/consumers/checks that were not in the planned impact.
- Record every changed file outside the planned surface with reason, impact, and verification.
- Use `None` only when actual diff confirms no deviations.

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
- Generated authority reconcile: `./songchart reconcile` when registered source inputs changed.
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

## Documentation impact

- Authorities, ADRs, status, indexes, and operational notes to update.

## Delivery and handoff

- Current writer/owner for overlapping source surfaces:
- Handoff commit SHA when switching AI/device/writer:
- Rebase/cherry-pick plan if branch diverged:
- Exact closed HEAD expected before PR merge:

## Rollback

- File, configuration, schema, and operational rollback boundaries.
