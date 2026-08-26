# Documentation Governance

Status: authoritative project workflow contract.

## Purpose

Keep implementation, architecture, operating instructions, and development state synchronized without creating parallel documentation hierarchies.

## Authority order

When documents overlap, use this order:

1. `PROJECT_AUTHORITY.md` and `AGENTS.md` — repository-wide engineering rules;
2. `docs/START_HERE.md` — reading order and task routing;
3. canonical project authorities under `docs/project/`;
4. module authorities under `docs/providers/`, `docs/operations/`, `docs/extensions/` and `docs/ui/`;
5. the current stage task contract;
6. historical audits, validation reports, implementation notes, and manifests.

A lower-level document may specialize a higher-level rule for its module, but must not silently contradict it.

## Documentation classes

| Class | Purpose | Examples |
|---|---|---|
| Authority | Stable project or module rules | architecture, security, provider policy |
| Task contract | Scope and acceptance criteria before implementation | `STAGE_*_TASK_CONTRACT.md` |
| Development state | Current stage, blocker, evidence, next action | `docs/project/DEVELOPMENT_STATE.md` |
| History | Accepted delivery chronology | `docs/project/DEVELOPMENT_HISTORY.md` |
| Audit / validation | Findings or evidence at a point in time | validation report, source audit |
| ADR | Durable architectural decision and consequences | `docs/project/docs/adr/` |
| Manifest | Historical exact delivery record | `*_CHANGE_MANIFEST.md` |

Do not use a status, audit, validation report, or manifest as a replacement for an authority document.

## Repository state authority

`README.md` is a durable project introduction. It must not own current stage, candidate, blocker, or checkpoint state.

`docs/project/DEVELOPMENT_STATE.md` is the single operational owner for current stage and current verification state.

`docs/project/DEVELOPMENT_HISTORY.md` owns accepted chronological history. `docs/project/docs/ROADMAP.md` owns future direction.

`docs/START_HERE.md` and `docs/DOCUMENTATION_INDEX.md` route readers to these owners instead of duplicating their state.

Historical stage manifests, audits, task contracts, and validation reports remain implementation evidence but must not become current-state authorities.

## Before implementation

Every task must:

1. read `PROJECT_AUTHORITY.md`, `AGENTS.md`, and `docs/START_HERE.md`;
2. identify the owning module or machine authority;
3. read the canonical authority and applicable module documents;
4. read relevant ADRs;
5. create or update one task contract containing goal, non-goals, acceptance criteria, affected modules, data/provider/policy impact, tests, and documentation impact;
6. search for existing documents, classes, routes, services, and contracts before adding new ones.

## During implementation

Implementation must not silently introduce a new architectural pattern, dependency, environment key, schema object, public URL, permission, provider capability, command, queue, scheduled task, or operational requirement.

Record durable architectural changes in an ADR or the owning authority. Update machine contracts when executable ownership changes.

## After implementation

Review and update documentation when code changes runtime behavior, ownership boundaries, schema lifecycle, security assumptions, configuration, provider policy, commands, queues, schedules, deployment, rollback, testing expectations, or known limitations.

Update `DEVELOPMENT_STATE.md` with verified facts only. Separate performed verification from checks not executed.

## Duplication rules

Before creating a document:

1. search `docs/` by subject and responsibility;
2. update the existing authority when ownership is the same;
3. create a new document only when it has a distinct owner, lifecycle, or audience;
4. link to authorities instead of copying their rules;
5. never create a second architecture, workflow, security, design, project-context, or development-state authority for the same scope.

Historical documents may remain when required for traceability, but they must be treated as historical evidence rather than active navigation.

## File placement

- Repository operating rules: `PROJECT_AUTHORITY.md` and root agent bootstraps.
- Reading map: `docs/START_HERE.md`.
- Current development state: `docs/project/DEVELOPMENT_STATE.md`.
- Accepted history: `docs/project/DEVELOPMENT_HISTORY.md`.
- Stable project authorities: `docs/project/`.
- Provider rules: `docs/providers/`.
- Operational integrations and policies: `docs/operations/`.
- Extension lifecycle: `docs/extensions/`.
- UI and design contracts: `docs/ui/`.
- Historical stage evidence: `docs/foundation/` and stage-specific module records.
- Setup troubleshooting: `docs/setup/`.

## Definition of Done

Documentation work is complete only when authority ownership is unambiguous, links and required files pass `composer docs:verify`, the task contract reflects implemented scope, `DEVELOPMENT_STATE.md` reflects verified current state, affected authorities are updated, and no obsolete duplicate remains in active navigation.
