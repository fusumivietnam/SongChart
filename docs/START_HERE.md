# Start Here

SongChart documentation is organized by semantic ownership, not development chronology.

## Before implementation

Read in this order:

1. `PROJECT_AUTHORITY.md`
2. `docs/ATLAS.md` for the human-readable system map connecting product, source, database, AI/MCP and delivery
3. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
4. `./songchart ai status --json` and `docs/project/generated/project-context.json` for current/runtime-sensitive work
5. for non-trivial work, run `php scripts/ai-brief.php --intent "<task>" --json` to resolve the semantic owner, placement, relevant skills and focused checks from `docs/project/engineering/ai-control-plane-contract.json`
6. the current task contract named by `docs/project/engineering/stage-plan.json`
7. the owning domain/stack/security/provider/UI/operations authority returned by the brief or documentation index

Use `docs/DOCUMENTATION_INDEX.md` to locate owners. `docs/ATLAS.md` is an orientation aid; machine-readable semantic owners remain authoritative when a detail differs. The AI brief narrows search space only; it never overrides an owning authority.

## Routing

- System overview: `docs/ATLAS.md`
- Task-scoped AI routing / placement / hygiene: `docs/project/engineering/ai-control-plane-contract.json`
- Domain/persistence: `docs/project/domain/`
- Providers: `docs/providers/` plus provider machine contracts
- Runtime/dependencies: `docs/project/stack/`
- Verification/development/MCP/project intelligence: `docs/project/engineering/`
- Security: `docs/project/security/` and `docs/project/docs/SECURITY.md`
- UI/design: `docs/ui/`
- Operations: `docs/operations/`
- Extensions: `docs/extensions/`

## Project state

Authored stage semantics: `docs/project/engineering/stage-plan.json`.

Generated current state: `docs/project/generated/development-state.json` and `docs/project/generated/DEVELOPMENT_STATE.md`.

Live branch/PR/SHA/check state: resolve from Git/GitHub with `./songchart ai status --json`.

Task-scoped derived context: `php scripts/ai-brief.php --intent "<task>" --json`; add `--hygiene` for repository-maintenance work.

Accepted chronology: `docs/project/DEVELOPMENT_HISTORY.md`.

Future direction: `docs/project/docs/ROADMAP.md`.

`docs/project/DEVELOPMENT_STATE.md` is compatibility-pointer only.

## Historical stage records

Historical `STAGE_*` files are not orientation sources. They are being consolidated according to `docs/project/engineering/documentation-consolidation-contract.json`. Use current authorities and Development History first; inspect Git/PR history when exact retired evidence is needed. Current packaging/source verification resolves the active task contract through `stage-plan.json`; it must not require stage-number-derived validation-report paths.

## Skills

SongChart-managed project skills are authored under `.agents/skills`. Equivalent client copies are projections, not independent authorities. Check projection drift with `php scripts/sync-agent-skills.php` and refresh declared projections with `php scripts/sync-agent-skills.php --write`.

## External providers and MCP

Use `docs/project/engineering/external-systems-registry.json` and `docs/project/engineering/mcp-governance-contract.json`. When a relevant provider has an official MCP/integration, evaluate it before custom bridging, but adopt only when it adds non-overlapping value.

## Verification

For architecture, persistence, Docker, provider, migration, seeder or verification changes, read the generated context and use `./songchart impact`/focused checks. Closure still requires governed candidate and canonical verification.
