# Documentation Governance

Status: authoritative project workflow contract.

## Purpose

Keep implementation, architecture, operating instructions, development state and historical evidence synchronized without creating parallel documentation hierarchies.

## Authority order

When documents overlap, use this order:

1. `PROJECT_AUTHORITY.md` and thin root agent bootstraps;
2. `docs/START_HERE.md` for reading/task routing;
3. current machine/project authorities under `docs/project/`;
4. module authorities under `docs/providers/`, `docs/operations/`, `docs/extensions/` and `docs/ui/`;
5. the current stage task contract;
6. Git/PR history and any still-unmigrated historical stage records.

A lower-level document may specialize a higher-level rule for its module, but must not silently contradict it.

## Current state ownership

`README.md` is a durable project introduction and never owns volatile stage/checkpoint facts.

Authored stage semantics live in `docs/project/engineering/stage-plan.json`. The generated current-state machine projection is `docs/project/generated/development-state.json`, with `docs/project/generated/DEVELOPMENT_STATE.md` as human projection. `docs/project/DEVELOPMENT_STATE.md` is compatibility-pointer only. Live branch, PR, SHA and CI facts are resolved from Git/GitHub with `./songchart ai status --json`.

Accepted chronology belongs in `docs/project/DEVELOPMENT_HISTORY.md`; future direction belongs in `docs/project/docs/ROADMAP.md`.

## Documentation classes

| Class | Owner/use |
|---|---|
| Machine/project authority | Stable executable ownership under `docs/project/` |
| Module authority | Provider, operations, extension or UI rules |
| Current task contract | Active stage scope and acceptance only |
| Generated state | Repository-derived current projection; never hand-edited |
| History | Accepted chronology and durable outcome summary |
| ADR | Durable architectural decision and consequences |
| Regression ledger | Repeated failure class with permanent guard |
| Historical stage source | Temporary migration input or exceptional retained evidence; not active authority |

## Legacy stage consolidation

`docs/project/engineering/documentation-consolidation-contract.json` governs retirement of legacy `STAGE_*` material. The target is not a renamed archive tree. Durable semantics are extracted into current owners, accepted chronology into Development History, guarded failures into the regression ledger, and exact retired source remains available from Git/PR history.

A legacy stage file may be deleted only after active links, tests, verifiers and generators stop requiring the path. Standalone historical retention requires a distinct legal/compliance/release reason that Git history plus structured owners cannot satisfy.

## Before implementation

Every task must identify the semantic owner, read current authorities and relevant ADRs, check the active task contract, and search existing source/contracts before adding new surfaces. Provider/platform work must also follow the official-first source policy and MCP governance when an official integration is relevant.

## During and after implementation

Do not silently introduce a new architectural pattern, dependency, environment key, schema object, public URL, permission, provider capability, command, queue, scheduled task or operational requirement. Update the owning authority in the same logical change. Generated state is refreshed only by repository tooling.

For documentation changes, prefer updating or consolidating existing owners. New documents require a distinct owner, lifecycle or audience.

## File placement

- Repository operating rules: `PROJECT_AUTHORITY.md` and thin root agent bootstraps.
- Reading map: `docs/START_HERE.md`.
- Authored current-stage semantics: `docs/project/engineering/stage-plan.json`.
- Generated current state: `docs/project/generated/`.
- Accepted chronology: `docs/project/DEVELOPMENT_HISTORY.md`.
- Stable project authorities: `docs/project/`.
- Provider/operations/extensions/UI authorities: their existing module directories.
- Setup troubleshooting: `docs/setup/`.
- Legacy stage records: migration inputs only while `documentation-consolidation-contract.json` marks their consumer closure incomplete.

## Definition of Done

Documentation work is complete only when ownership is unambiguous, active links/checks pass, generated projections are reconciled through repository tooling, no obsolete duplicate remains in active navigation, and legacy stage files touched by the change have either been converted to current owners or retained with an explicit reason.
