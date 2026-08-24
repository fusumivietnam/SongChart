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

## Allowed incidental files

- List formatter-only, generated, lockfile, manifest, or delivery metadata files that may change without expanding product scope.

## Scope deviations

- Record any changed file outside the planned surface with reason, impact, and verification. Use `None` when there are no deviations.

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

- Impact lane:
- Focused implementation gates:
- Stage closure owner: `composer stage:verify`
- Canonical closure owner: `composer canonical:verify`
- Packaging owner: `composer release:package`
- Explicitly avoided duplicate/nested gates:

## Tests and verification

- Behavioral tests.
- Architecture/security tests.
- Pint and static analysis.
- PostgreSQL/runtime checks; optional compatibility checks must be labeled non-authoritative.
- Checks not performed must be listed explicitly.

## Documentation impact

- Authorities, ADRs, status, indexes, and operational notes to update.

## Rollback

- File, configuration, schema, and operational rollback boundaries.
