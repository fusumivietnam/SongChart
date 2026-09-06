# Stage 20.0 Task Contract — Product Foundation & Development Continuity

Status: implementing on the Stage 20 umbrella branch.

## Goal

Establish durable, AI-safe development continuity and a clean owner-based knowledge surface before expanding the SongChart product/domain model.

## Authority order

1. `PROJECT_AUTHORITY.md` and current machine/domain contracts.
2. `docs/project/engineering/stage-plan.json` for authored progress.
3. generated project context/state plus live Git/GitHub for volatile facts.
4. `project-knowledge.json`, `mcp-governance-contract.json`, `external-systems-registry.json` and `documentation-consolidation-contract.json` for AI/external/documentation governance.
5. Laravel migrations for application schema evolution.

## 20.0A — Development continuity + AI/MCP/documentation governance

- preserve Project Kernel/repository authority above MCP/provider skills and agent memory;
- discover/evaluate an official provider MCP first when an active use case needs provider-native AI tooling, then compare overlap with native connector/CLI/API before adoption;
- keep developer MCP connections out of production application dependencies;
- record GitHub, Neon, Cloudflare, Figma and Grafana official MCP availability/evaluation in the external-system registry;
- convert legacy stage-centric documentation toward current semantic owners instead of creating another archive hierarchy;
- use Development History for accepted chronology, ADR/current contracts for durable rules, regression ledger for guarded failures and Git/PR history for exact retired evidence;
- migrate active links/tests/verifiers before deleting each historical stage file;
- keep GitHub as source/delivery authority and Docker/runtime state disposable.

## 20.0B — Durable development database authority

- support durable remote PostgreSQL suitable for Codespaces/mobile continuity, with Neon as preferred provider;
- local PostgreSQL remains an explicit fallback mode only;
- fail closed when configured remote DB is unreachable/wrong environment;
- verification/test PostgreSQL remains isolated and disposable;
- backup/restore and diagnostics must not assume Compose PostgreSQL;
- APP_KEY and development secrets remain stable outside source.

## 20.0C — Durable object storage + diagnostics

- Cloudflare R2 through Laravel Filesystem is preferred durable media/storage candidate;
- Redis/cache/Horizon remain disposable unless evidence says otherwise;
- diagnostics expose DB/storage/environment identity without leaking secrets;
- never silently fall back to empty DB/local filesystem after durable authority is configured.

## 20.1–20.7

20.1 Product/User Journey Authority → 20.2 Provider Reference Matrix → 20.3 Domain Gap Map → 20.4 Canonical Model Proposal → 20.5 Schema & Migration Implementation → 20.6 Read Models/Application Contracts → 20.7 exact-head acceptance/closure.

## External AI/tooling

GitHub/Neon/Cloudflare/Figma/Grafana MCP capabilities are development/operations tools only within declared scope. Hermes Agent, OpenCode, OpenRouter, 9Router and custom multi-agent orchestration are not Stage 20 application dependencies. Visitor AI is a separate future product capability requiring evidence, retrieval/read-model boundaries, cost controls and read-only/citation-aware behavior first.

## Infrastructure/scale policy

Workers, Hyperdrive, replicas, external APM or proxy/service-mesh technology require measured latency/saturation/queue/cache/database/search evidence. Stage 20 does not pre-scale.

## Verification and closure

Preserve `./songchart` facade; keep verification DB isolated; regenerate `docs/project/generated/*` only through repository tooling; commit source/contract changes before generated reconciliation; close Stage 20 only after exact-head candidate/canonical verification and clean tracked tree.
