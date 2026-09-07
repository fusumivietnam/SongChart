# SongChart System Atlas

> A private, human-readable navigation aid for understanding how product flows, source code, data, runtime, AI tooling and delivery fit together. Repository contracts remain authority when this overview and machine-readable owners differ.

## 1. The whole system

```mermaid
flowchart LR
    U[User / Editor / Operator] --> UI[Web & Admin UI]
    UI --> HTTP[Routes & Controllers]
    HTTP --> APP[Application use cases / read models]
    APP --> DOM[SongChart domain contracts]
    APP --> DB[(PostgreSQL 18)]
    APP --> REDIS[(Redis\ncache / queue)]
    APP --> PROVIDERS[External providers\nMusicBrainz / YouTube / ...]
    APP --> STORAGE[Object storage\nLaravel Filesystem / R2 candidate]

    AI[AI development control plane] --> AUTH[Repository authority\nstage-plan / project-context / impact]
    AUTH --> GH[GitHub\nsource / PR / CI]
    AUTH --> DBOPS[Development DB authority\nNeon preferred remote]
    AUTH --> EXT[MCP / external capability registry]

    GH --> CI[PREPARE -> QUALITY -> runtime lanes -> CLOSE]
    CI --> MAIN[Accepted main]
```

## 2. From product intent to code

```mermaid
flowchart TD
    JOURNEY[Product / user journey] --> ROUTE[Route]
    ROUTE --> CTRL[Controller]
    CTRL --> USECASE[Application use case / service]
    CTRL --> READ[Read model / query]
    USECASE --> DOMAIN[Domain contract]
    READ --> DOMAIN
    USECASE --> DATA[(Tables owned by migrations)]
    READ --> DATA
    USECASE --> AUDIT[Privileged audit when required]
```

Use these authorities when tracing a feature:

- `docs/project/domain/product-user-journeys.json` — why the surface exists.
- `docs/project/domain/use-case-contracts.json` — executable application contracts.
- `routes/` — HTTP entrypoints.
- `app/Http/Controllers/` — transport orchestration only.
- `app/Application/` — use cases and read models.
- `docs/project/domain/domain-contracts.json` — canonical domain semantics.
- `database/migrations/` — schema evolution authority.
- `docs/project/domain/schema-ownership.json` — table/domain ownership.

## 3. Data and database

```mermaid
flowchart LR
    PROVIDER[Provider evidence] --> ASSERT[Metadata assertions / external identifiers]
    ASSERT --> REVIEW[Governed admission review]
    REVIEW -->|accept| CANON[SongChart canonical data]
    REVIEW -->|reject| HISTORY[Decision / audit history]
    CANON --> READS[Public & admin read models]
    READS --> UI[User-facing surfaces]

    MIG[Laravel migrations] --> DB[(PostgreSQL)]
    DB --> CANON
    DB --> ASSERT
    DB --> REVIEW
```

Development database modes:

```mermaid
flowchart TD
    DEV[Codespaces / Docker development] --> MODE{Database mode}
    MODE -->|remote preferred| NEON[(Neon PostgreSQL)]
    MODE -->|explicit fallback| LOCAL[(Docker PostgreSQL volume)]
    NEON --> ID[Runtime identity check]
    LOCAL --> ID
    ID --> MIGRATE[Laravel migrate]
    MIGRATE --> ADMIN[Ensure development Super Admin]
```

The remote path is preferred for continuity across Codespaces. `docs/project/engineering/development-database-contract.json` owns the exact rules; `docs/foundation/DEVELOPMENT_NEON_CODESPACES.md` explains setup without committing credentials.

## 4. AI, MCP and external systems

```mermaid
flowchart TD
    TASK[Development intent] --> AUTH[Read repository authority]
    AUTH --> NEED{External capability needed?}
    NEED -->|No| LOCAL[Use repository-native tooling]
    NEED -->|Source / PR / CI| GITHUB[GitHub connector]
    NEED -->|Durable dev DB| NEON[Neon capability when connected]
    NEED -->|Object storage| R2[Cloudflare / R2 capability when relevant]
    NEED -->|Design source| FIGMA[Figma when a UI task needs design evidence]

    GITHUB --> VERIFY[Return result to SongChart workflow]
    NEON --> VERIFY
    R2 --> VERIFY
    FIGMA --> VERIFY
    LOCAL --> VERIFY
    VERIFY --> IMPACT[Impact / quality / exact-head verification]
```

MCP is an adapter layer, not project authority. The current owners are:

- `docs/project/engineering/mcp-governance-contract.json`
- `docs/project/engineering/external-systems-registry.json`
- `docs/project/engineering/project-knowledge.json`

A tool should be used because an active task needs its capability, not because it happens to be installed.

## 5. Delivery and CI

```mermaid
flowchart LR
    CHANGE[Bounded branch change] --> PR[PR]
    PR --> PREP[PREPARE\ngenerate authority locally]
    PREP --> QUALITY[QUALITY\nfail fast]
    QUALITY -->|pass| PUSH[Push generated authority if needed]
    QUALITY -->|fail| STOP[Stop before expensive lanes]
    PUSH --> PG[PostgreSQL]
    PUSH --> BROWSER[Browser smoke]
    PUSH --> FRONT[Frontend build]
    PG --> CLASS[Exact-head classification]
    BROWSER --> CLASS
    FRONT --> CLASS
    CLASS --> CLOSE[Canonical CLOSE]
    CLOSE --> READY[Ready to promote]
    READY --> MERGE[Human merge]
    MERGE --> MAINCI[Accepted-main CI]
```

Why main CI still exists after a green PR: the merge commit is a new integration SHA. SongChart currently prefers re-verification over assuming SHA equivalence. A future optimization may reuse PR evidence only when tree-equivalence provenance is proven explicitly.

## 6. UI/UX rule of thumb

Product/admin UI should speak in task language first:

```text
User goal -> understandable information -> clear consequence -> action
```

Technical implementation detail belongs behind progressive disclosure:

```text
IDs / field names / raw provider payload / commands -> "Chi tiết kỹ thuật" or developer tooling
```

For example, the editorial review surface should say **“Đề xuất cần bạn xem xét”**, **“Giá trị đề xuất”**, **“Chấp nhận và cập nhật dữ liệu”** rather than leading with `canonical admission`, `assertion`, raw ULIDs or operational shell commands.

## 7. Where to start when something breaks

| Question | First place to look |
| --- | --- |
| What stage are we implementing? | `docs/project/engineering/stage-plan.json` |
| Why does a feature exist? | `docs/project/domain/product-user-journeys.json` |
| Which route/use case owns it? | `docs/project/domain/use-case-contracts.json` + `routes/` |
| Who owns a table/schema change? | `database/migrations/` + `schema-ownership.json` |
| Which database am I connected to? | `./songchart dev db status` |
| Why did CI fail? | Auto Closure `QUALITY` first, then classified runtime lane |
| Which MCP/tool should AI use? | `external-systems-registry.json` + `mcp-governance-contract.json` |
| Is generated project state current? | `./songchart ai status --json` / reconcile workflow |

## 8. Design boundary

This Atlas is for developers/operators understanding the system. It must **not** cause product UI to expose repository commands, infrastructure jargon, internal IDs or architecture vocabulary unless the user explicitly opens technical detail.
