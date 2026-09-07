# Start Here

SongChart documentation is organized by semantic ownership, not development chronology.

## Before implementation

Read in this order:

1. `PROJECT_AUTHORITY.md`
2. `docs/ATLAS.md` for the human-readable system map connecting product, source, database, AI/MCP and delivery
3. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
4. `./songchart ai status --json` and `docs/project/generated/project-context.json` for current/runtime-sensitive work
5. the current task contract named by `docs/project/engineering/stage-plan.json`
6. the owning domain/stack/security/provider/UI/operations authority

Use `docs/DOCUMENTATION_INDEX.md` to locate owners. `docs/ATLAS.md` is an orientation aid; machine-readable semantic owners remain authoritative when a detail differs.

## Routing

- System overview: `docs/ATLAS.md`
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

Accepted chronology: `docs/project/DEVELOPMENT_HISTORY.md`.

Future direction: `docs/project/docs/ROADMAP.md`.

`docs/project/DEVELOPMENT_STATE.md` is compatibility-pointer only.

## Historical stage records

Historical `STAGE_*` files are not orientation sources. They are being consolidated according to `docs/project/engineering/documentation-consolidation-contract.json`. Use current authorities and Development History first; inspect Git/PR history when exact retired evidence is needed.

## External providers and MCP

Use `docs/project/engineering/external-systems-registry.json` and `docs/project/engineering/mcp-governance-contract.json`. When a relevant provider has an official MCP/integration, evaluate it before custom bridging, but adopt only when it adds non-overlapping value.

## Verification

For architecture, persistence, Docker, provider, migration, seeder or verification changes, read the generated context and use `./songchart impact`/focused checks. Closure still requires governed candidate and canonical verification.
