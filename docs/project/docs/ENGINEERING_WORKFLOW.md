# Engineering Workflow Authority

Status: canonical workflow authority for AI-assisted development.

## Purpose

Keep every change scoped, evidence-based, reviewable and reversible. This workflow complements `OFFICIAL_SOURCE_POLICY.md`; it does not replace framework or module authorities.

## Mandatory state machine

Every development task moves in order:

1. `proposed` — request exists, no source changes.
2. `researched` — repository authorities, installed versions, official sources, current implementation and tests were read.
3. `scoped` — goal, non-goals, affected contexts and expected change surface are locked.
4. `planned` — implementation tasks, interfaces, tests and rollback are explicit.
5. `implementing` — only planned files are changed; scope expansion requires a recorded justification.
6. `verifying` — focused tests run before full quality and database lanes.
7. `reviewed` — spec-compliance review runs before code-quality review.
8. `closed` — validation evidence and rollback notes are complete.

Skipping a state is a workflow failure.

## Change-surface discipline

The current task contract must list `Expected files` and `Allowed incidental files`. Production changes outside those lists require an entry under `Scope deviations` with reason, impact and verification. Generated artifacts, lockfiles and formatter-only changes must still be declared.

## Contract-first domain changes

For catalog/domain work, the accepted use case must declare exact fields read/written, identifier and null semantics, route/API shape, and relationship invariants before implementation. Read `docs/project/domain/domain-contracts.json` before migration/model/service/UI changes. A new field or identifier behavior requires a contract revision first in the same task; application code must not invent undeclared schema. Run `composer domain-contracts:verify` before focused behavior tests.

## Impact analysis

Before implementation, identify entry points, callers, routes, jobs, models, migrations, bindings and protecting tests. If an authority changes, resolve all registered dependents in `docs/project/governance/authority-dependencies.json` and declare them in the task contract before coding. Use `docs/project/stack/impact-test-map.json` as the minimum test map; module authorities may require more. Run the authority contradiction scan before packaging so superseded current invariants cannot survive in tests, verifiers, CI or current design/testing documentation.

## Implementation discipline

- Prefer the smallest Laravel-native change that satisfies the accepted contract.
- Do not combine unrelated refactors or opportunistic cleanup.
- Do not add placeholders to production code.
- A public contract change requires a complete call-site search and consumer tests.
- A third failed corrective patch requires architecture review before another fix.

## Debugging discipline

No fix without a written root-cause hypothesis supported by evidence. Change one variable at a time, add a failing regression test first when practical, and state behavior intentionally unchanged.

## Review gates

### Gate 1 — Spec compliance

Confirm scope, acceptance criteria, non-goals, expected files and official-source decisions. Critical deviations block code-quality review.

### Gate 2 — Code quality

Confirm Laravel-native alignment, security, typing, data constraints, tests, documentation, rollback and absence of duplicate authority or abstractions.

## Completion claims

A validation report must distinguish source-level, runtime, PostgreSQL, optional compatibility, and full-release evidence. Never claim a lane passed without command output from that lane; SQLite compatibility is never release evidence.
