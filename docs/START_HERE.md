# Start Here

SongChart documentation is organized by ownership rather than by development chronology.

## Before implementation

Read in this order:

1. `PROJECT_AUTHORITY.md`
2. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
3. `docs/project/DEVELOPMENT_STATE.md` when working on the active stage
4. the current task contract
5. the authority for the domain being changed

Use `docs/DOCUMENTATION_INDEX.md` to locate the owning authority.

## Routing

### Domain and persistence

Use `docs/project/domain/` for canonical entities, fields, identifiers, relationships, schema ownership, URL contracts, application data boundaries, and use-case contracts.

### Providers

Use `docs/providers/` together with provider-related machine contracts under `docs/project/domain/`.

### Runtime and dependencies

Use `docs/project/stack/`.

### Verification and development workflow

Use `docs/project/engineering/`.

### Security and authorization

Use `docs/project/security/` and `docs/project/docs/SECURITY.md`.

### UI

Use `docs/ui/`.

### Operations

Use `docs/operations/`.

### Extensions

Use `docs/extensions/`.

## Project state

Current work belongs in `docs/project/DEVELOPMENT_STATE.md`.

Accepted history belongs in `docs/project/DEVELOPMENT_HISTORY.md`.

Future direction belongs in `docs/project/docs/ROADMAP.md`.

Historical `STAGE_*` documents are implementation evidence. They are not active navigation or current-state authority.

## Verification

Before architecture, persistence, Docker, provider, migration, seeder, or verification changes:

```bash
./songchart context --json
```

Use focused tests during implementation and governed candidate/canonical verification for closure.
