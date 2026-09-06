# Stage 20.0 Task Contract — Product Foundation & Development Continuity

Status: implementing on the Stage 20 umbrella branch.

## Goal

Stage 20 establishes a durable, AI-safe development foundation before expanding the SongChart product/domain model. The stage must reduce environment loss and agent guesswork without adding duplicate runtime authorities or speculative AI/product dependencies.

## Authority order

1. `PROJECT_AUTHORITY.md` and current repository machine contracts own SongChart architecture and workflow.
2. `docs/project/engineering/stage-plan.json` owns authored current-stage progress.
3. `docs/project/engineering/project-knowledge.json` owns compact repository knowledge for AI/developer consumers.
4. `docs/project/engineering/mcp-governance-contract.json` owns external agent/MCP boundaries.
5. `docs/project/engineering/external-systems-registry.json` owns the intended role/status of external services; live resource identity remains an environment/runtime fact.
6. Laravel migrations own application schema evolution. Provider/MCP tools may inspect live state but must not replace migrations or canonical domain contracts.

## Stage 20 roadmap

### 20.0A — Development continuity + AI/MCP governance

- make external-system roles, authority scopes and mutation limits machine-readable;
- preserve the Project Kernel as the highest AI authority;
- distinguish development-plane MCP/tooling from production application dependencies;
- keep GitHub as source/delivery authority;
- record technology deferrals instead of installing tools because they are available;
- treat container/runtime state as disposable and canonical development data as durable authority.

### 20.0B — Durable development database authority

- support a durable remote PostgreSQL development authority suitable for Codespaces/mobile continuity, with Neon as the current preferred provider;
- keep local PostgreSQL as an explicit fallback mode rather than a silent universe switch;
- fail closed when a configured remote development DB is unreachable or has the wrong environment identity;
- preserve verification/test PostgreSQL as isolated and disposable;
- update backup/restore and diagnostics so they do not assume the development database is always a Compose container;
- keep `APP_KEY` and development secrets stable outside source.

### 20.0C — Durable object storage + development diagnostics

- use Cloudflare R2 as the preferred durable development/media object-storage candidate through Laravel Filesystem;
- keep Redis/cache/Horizon development state disposable unless product evidence requires otherwise;
- extend `./songchart dev doctor`/AI diagnostics with database authority, storage connectivity and environment-identity evidence;
- never silently fall back to an empty database or local filesystem when a durable authority was explicitly configured.

### 20.1 — Product/User Journey Authority

Define visitor/editor/admin journeys before adding schema.

### 20.2 — Provider Reference Matrix

Use providers as evidence/reference, never as canonical schema authority.

### 20.3 — Domain Gap Map

Compare current domain contracts against product journeys and classify each gap as sufficient, missing, derived, provider-only, UI-hidden or retirement candidate.

### 20.4 — Canonical Model Proposal

Define accepted entities, relationships, invariants, identity rules, provenance boundaries and chart-specific product semantics.

### 20.5 — Schema & Migration Implementation

Implement only accepted model changes using guarded forward Laravel migrations and existing migration lifecycle policy.

### 20.6 — Read Models / Application Contracts

Expose task-oriented product read models without allowing controllers/views to become ad-hoc data-access authorities.

### 20.7 — Stage 20 Acceptance & Closure

Reconcile generated authority, run focused verification, candidate verification and canonical closure on the exact head before one final Stage 20 PR is merged.

## External AI/tooling policy for Stage 20

The following are development-plane capabilities, not production dependencies: GitHub connector/MCP, Neon Skills/MCP, Cloudflare Docs/API MCP and Figma/Code Connect where applicable. They may improve evidence and reduce guessing but do not own SongChart architecture.

Hermes Agent, OpenCode, OpenRouter, 9Router and custom multi-agent orchestration are not Stage 20 application dependencies. They may be evaluated as optional developer clients/gateways only when they reduce an evidenced workflow cost. A visitor-facing AI assistant is a separate future product capability and requires product evidence, retrieval/read-model boundaries, cost controls and read-only/citation-aware behavior before adoption.

## Infrastructure/scale policy

Stage 20 records observability and scale decision requirements but does not pre-scale. Later adoption of Workers, Hyperdrive, additional app replicas, external APM or proxy/service-mesh technology must be justified by measured latency, saturation, queue, cache, database or search-visibility evidence.

## Verification and closure

- preserve `./songchart` as the supported workflow facade;
- verification/test data must remain isolated from shared development data;
- generated files under `docs/project/generated/*` are regenerated only by repository tooling;
- source/contract changes are committed before generated reconciliation;
- Stage 20 closes only after exact-head candidate and canonical verification pass and the tracked tree remains clean.
