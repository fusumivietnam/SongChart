# Start Here

SongChart documentation is organized by semantic ownership, not development chronology.

## Before implementation

Start with the generated orientation layer instead of reading the repository broadly:

1. `docs/project/generated/SYSTEM_GUIDE.md` — what SongChart is, what exists, what is partial/missing, and how to trace one feature.
2. `docs/project/generated/FEATURE_MATRIX.md` — journey/capability coverage and explicitly unmapped entrypoints.
3. `docs/project/generated/system-knowledge.json` — machine-readable semantic index for AI/tools.
4. `./songchart ai status --json` — live branch/PR/SHA/check state for the current work lease.
5. Open only the source authorities referenced by the journey/use-case/capability you are changing.
6. Read the current task contract when the change belongs to the active stage.

`PROJECT_AUTHORITY.md` remains mandatory engineering authority, but new humans and AI should use the generated semantic layer to decide which detailed authorities are relevant before reading them. `docs/ATLAS.md` remains a visual architecture aid; it is not the primary mapping authority.

## The trace model

Use this mental path for almost every question:

```text
user/audience
  -> journey
  -> capability / use case
  -> route / implementation
  -> domain + data owner
  -> verification
  -> current stage / roadmap
```

Do not infer ownership from a filename, class name, function name, table name, or similarly named feature. Resolve the semantic ID and its source authority from `system-knowledge.json` first. If a mapping is missing, treat it as an explicit gap to close rather than guessing.

## Routing

- Semantic system map: `docs/project/generated/system-knowledge.json`
- Human system orientation: `docs/project/generated/SYSTEM_GUIDE.md`
- Capability coverage: `docs/project/generated/FEATURE_MATRIX.md`
- Visual architecture atlas: `docs/ATLAS.md`
- Domain/persistence: `docs/project/domain/`
- Providers: `docs/providers/` plus provider machine contracts
- Runtime/dependencies: `docs/project/stack/`
- Verification/development/MCP/project intelligence: `docs/project/engineering/`
- Security: `docs/project/security/` and `docs/project/docs/SECURITY.md`
- UI/design: `docs/ui/`
- Operations: `docs/operations/`
- Extensions: `docs/extensions/`

Use `docs/DOCUMENTATION_INDEX.md` when you need to browse documentation by owner, not as the first step for understanding a feature.

## Project state

Authored stage semantics: `docs/project/engineering/stage-plan.json`.

Generated current state: `docs/project/generated/development-state.json` and `docs/project/generated/DEVELOPMENT_STATE.md`.

Generated control-plane status: `docs/project/generated/system-status.json` and `docs/project/generated/SYSTEM_STATUS.md`.

Live branch/PR/SHA/check state: resolve from Git/GitHub with `./songchart ai status --json`.

Accepted chronology: `docs/project/DEVELOPMENT_HISTORY.md`.

Future direction: `docs/project/engineering/roadmap.json` with human projections generated from repository authority.

`docs/project/DEVELOPMENT_STATE.md` is compatibility-pointer only.

## Documentation generation rule

End-user, developer, AI, operator and product-owner guides should be projections of the same semantic knowledge layer, not independently maintained copies of system facts.

When a guide is incomplete or wrong:

1. correct the owning journey/use-case/domain/schema/stage authority or mapping;
2. regenerate the semantic knowledge layer;
3. regenerate the audience-specific guide.

This preserves one source of meaning while allowing different levels of detail for different audiences.

## Historical stage records

Historical `STAGE_*` files are not orientation sources. They are consolidation inputs under `docs/project/engineering/documentation-consolidation-contract.json`. Use current authorities and Development History first; inspect Git/PR history only when exact retired evidence is needed.

## External providers and MCP

Use `docs/project/engineering/external-systems-registry.json` and `docs/project/engineering/mcp-governance-contract.json`. When a relevant provider has an official MCP/integration, evaluate it before custom bridging, but adopt only when it adds non-overlapping value.

## Verification

For architecture, persistence, Docker, provider, migration, seeder or verification changes, resolve the semantic owner first, then use generated context and `./songchart impact`/focused checks. Closure still requires governed candidate and canonical verification.
