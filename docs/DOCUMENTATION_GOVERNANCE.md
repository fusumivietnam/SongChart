# Documentation Governance

Status: authoritative project workflow contract.

## Purpose

Keep implementation, architecture and operating instructions synchronized without creating parallel documentation hierarchies.

## Authority order

When documents overlap, use this order:

1. `AGENTS.md` — repository-wide agent rules;
2. `docs/START_HERE.md` — reading order and task routing;
3. canonical project documents under `docs/project/docs/`;
4. module authorities under `docs/providers/`, `docs/operations/`, `docs/extensions/` and `docs/ui/`;
5. current stage task contract and implementation status;
6. implementation notes and change manifests.

A lower-level document may specialize a higher-level rule for its module, but must not silently contradict it.

## Documentation classes

| Class | Purpose | Examples |
|---|---|---|
| Authority | Stable project or module rules | architecture, security, design authority, provider policy |
| Task contract | Scope and acceptance criteria before implementation | `STAGE_*_TASK_CONTRACT.md` |
| Status | Factual implementation and verification state | `*_IMPLEMENTATION_STATUS.md` |
| Audit | Findings at a point in time | source audit, dependency audit |
| ADR | Durable architectural decision and consequences | `docs/project/docs/adr/` |
| Manifest | Exact delivery file changes | `*_CHANGE_MANIFEST.md` |

Do not use a status, audit or manifest as a replacement for an authority document.

## Official-source governance

All implementation tasks must follow `docs/project/docs/OFFICIAL_SOURCE_POLICY.md` and start from `docs/templates/TASK_CONTRACT_TEMPLATE.md`. The current task contract must record installed-version authority, official sources, native capability assessment, custom implementation justification, and unperformed verification. `composer official-sources:verify` is mandatory in the quality chain.

## Repository state authority

`README.md` is the only document allowed to declare `Current stage:`. `docs/project/DEVELOPMENT_HISTORY.md` owns chronological stage/hotfix history from Stage 16.1 onward. `docs/START_HERE.md` is a reading map and must not duplicate the current-stage pointer. Legacy stage manifests are historical delivery records; once marked frozen they must not receive later-stage entries. Run `composer repository-state:verify` after stage, history, or governance changes.

## Before implementation

Every task must:

1. read `AGENTS.md` and `docs/START_HERE.md`;
2. identify the owning module;
3. read the canonical authority and applicable module documents;
4. read relevant ADRs;
5. create or update one task contract containing goal, non-goals, acceptance criteria, affected modules, data/provider/policy impact, tests and documentation impact;
6. search for existing documents, classes, routes, services and contracts before adding new ones.

## During implementation

Implementation must not silently introduce:

- a new architectural pattern;
- a new dependency;
- a new environment variable or configuration key;
- a new database table, column, index or constraint;
- a new route or public URL contract;
- a new permission or authorization rule;
- a new provider capability or policy assumption;
- a new command, queue, scheduled task or operational requirement.

Record architectural changes in an ADR. Update the owning authority document when behavior or policy changes.

## After implementation

Review and update documentation when code changes:

- runtime behavior;
- module ownership or dependency direction;
- schema or data lifecycle;
- authentication, authorization or security assumptions;
- configuration or environment requirements;
- provider contracts, attribution or retention rules;
- commands, queues, schedules, deployment or rollback procedures;
- test expectations or quality gates;
- known limitations or technical debt.

Update implementation status with only verified facts. Explicitly list checks that could not run.

## Duplication rules

Before creating a document:

1. search `docs/` by subject and responsibility;
2. update the existing authority when ownership is the same;
3. create a new document only when it has a distinct owner, lifecycle or audience;
4. link to authorities instead of copying their rules;
5. never create a second `README`, architecture, workflow, security or design authority for the same scope.

Historical documents may remain when required for traceability, but must be labelled as historical or status-only.

## File placement

- Repository operating rules: root `AGENTS.md`.
- Reading map: `docs/START_HERE.md`.
- Stable project authorities and ADRs: `docs/project/docs/`.
- Provider rules: `docs/providers/`.
- Operational integrations and policies: `docs/operations/`.
- Extension lifecycle: `docs/extensions/`.
- UI and design contracts: `docs/ui/`.
- Cross-cutting stage audits/status: `docs/foundation/`.
- Setup troubleshooting: `docs/setup/`.

## Required metadata

Task, status, audit and ADR documents must clearly state their role or status near the top. Stage documents must identify the product stage they govern.

## Definition of Done

Documentation work is complete only when:

- authority ownership is unambiguous;
- links and required files pass `composer docs:verify`;
- the task contract reflects the implemented scope;
- implementation status separates performed and unperformed verification;
- affected authority documents are updated;
- the change manifest lists added, changed and removed files;
- no obsolete duplicate document remains without an explicit historical label.
