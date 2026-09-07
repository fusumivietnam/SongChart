# Stage 20.0 Task Contract — Product Foundation & Development Continuity

Status: accepted; Stage 20 closure is recorded by authored progress plus exact-head Git/PR/CI evidence.

## Goal

Establish durable, AI-safe development continuity and a clean owner-based knowledge surface before expanding the SongChart product/domain model.

## Authority order

1. `PROJECT_AUTHORITY.md` and current machine/domain contracts.
2. `docs/project/engineering/stage-plan.json` for authored progress.
3. generated project context/state plus live Git/GitHub for volatile facts.
4. `project-knowledge.json`, `mcp-governance-contract.json`, `external-systems-registry.json`, `documentation-consolidation-contract.json` and `recheck-policy.json` for AI/external/documentation governance.
5. Laravel migrations for application schema evolution.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md` — repository workflow/architecture precedence.
- `docs/project/engineering/stage-plan.json` — authored Stage 20 progress and current task-contract pointer.
- `docs/project/generated/development-state.json` and `docs/project/generated/project-context.json` — generated projections; regenerate through `./songchart reconcile` rather than editing directly.
- `docs/project/engineering/project-knowledge.json` — compact AI/developer orientation.
- `docs/project/engineering/mcp-governance-contract.json` — MCP/agent authority, permission and lifecycle boundaries.
- `docs/project/engineering/external-systems-registry.json` — provider integration status and official-MCP evidence.
- `docs/project/engineering/recheck-policy.json` — mandatory freshness/re-verification triggers.
- `docs/project/engineering/documentation-consolidation-contract.json` — legacy stage-document migration/retirement authority.
- `docs/project/engineering/verification-topology.json` and `verification-command-surface.json` — verification ownership, public entrypoints and deduplication authority.
- `docs/project/stack/stack-manifest.json`, `composer.json`, `composer.lock`, `package.json` and `package-lock.json` — runtime/dependency evidence.
- Laravel migrations remain the application schema-evolution authority.

### Installed versions

Version claims must be resolved from repository/runtime evidence rather than copied from provider marketing pages. Current Stage 20 baseline is:

- PHP `8.5` from SongChart runtime/project authority.
- Laravel `13` from Composer/project authority.
- PostgreSQL major `18` as release/verification database authority.
- Node.js major `24` as the governed Node runtime baseline.
- Livewire `4` as the current frontend interaction layer.
- Redis `7.4` in the Docker development runtime; cache/queue state remains disposable.

Exact package patch versions are owned by `composer.lock` and `package-lock.json`; this contract must not duplicate lockfile detail that can drift independently.

### Official external sources

Official provider evidence reviewed for the Stage 20 integration/governance decisions is recorded in `external-systems-registry.json`. Primary official sources currently include:

- GitHub MCP Server: `https://github.com/github/github-mcp-server`.
- Neon MCP Server: `https://neon.com/docs/ai/neon-mcp-server`.
- Cloudflare MCP servers: `https://developers.cloudflare.com/agents/model-context-protocol/cloudflare/servers-for-cloudflare/`.
- Figma MCP: `https://help.figma.com/hc/en-us/articles/35280968300439-Figma-MCP-collection-What-is-the-Figma-MCP-server`.
- Grafana MCP: `https://grafana.com/docs/grafana/latest/developer-resources/mcp/introduction/`.

Provider availability is evidence, not automatic adoption. `external-systems-registry.json` records the review date, adoption/defer decision and bounded authority for each provider. Official-source evidence must be rechecked when the relevant integration changes version, auth model, permission model, tool/resource surface, deprecation state or material behavior.

### Native capability assessment

Stage 20 prefers existing SongChart/Laravel/platform capabilities before adding packages or custom infrastructure:

- Git/GitHub already owns source, branch/PR, CI and exact-head delivery evidence; an additional GitHub MCP is unnecessary when the connected GitHub surface already covers the active workflow.
- `./songchart`, existing repository contracts, Project Kernel, `ai status/doctor`, `reconcile`, impact resolution and stage/canonical verification already own project navigation/freshness/closure; no parallel recheck framework is needed.
- Existing verification topology already separates impact, focused, quality, stage and canonical lanes; duplicate tests/verifiers must be consolidated instead of preserved as historical compatibility clutter.
- Laravel migrations already own schema evolution; Neon MCP/CLI may inspect or operate provider-native development resources but cannot replace migration ownership.
- Laravel Filesystem is the future application abstraction for R2; Cloudflare MCP is development/operations evidence, not a second storage API authority inside the application.
- Figma/Code Connect is appropriate only when design-to-code work is active; it must not force a frontend stack migration.
- Existing Pulse/Horizon/logging remain the first observability layer; Grafana is deferred until operational evidence justifies external aggregation.
- Git history and current semantic owners can preserve historical evidence and durable rules, so a second archive hierarchy for all `STAGE_*` documents is unnecessary.

### Custom implementation justification

Custom Stage 20 code/contracts are permitted only where no existing native owner can enforce the SongChart-specific invariant with less complexity. The accepted custom surfaces are narrowly scoped:

- SongChart authority/governance contracts because provider tools cannot define SongChart architecture, canonical music semantics, lifecycle or delivery policy.
- Fail-closed development database identity/authority controls planned for 20.0B because silently switching database universes is a SongChart development-safety invariant, not a generic provider feature.
- Documentation-consolidation ownership because Git history alone does not tell active AI/developer consumers which durable semantics replaced a retired stage document.
- Recheck trigger policy because freshness depends on SongChart lifecycle boundaries across repository state and external integrations; it reuses existing verifier commands rather than creating another verification engine.
- Verification deduplication rules because one semantic invariant must have one executable owner while public/mobile wrappers remain thin delegates only.

No custom MCP server, model router, multi-agent runtime, second schema authority, second CI/promotion system, duplicate verifier/test owner or duplicate provider bridge is justified in Stage 20 without a separately evidenced use case and package/native capability review.

## 20.0A — Development continuity + AI/MCP/documentation governance

- preserve Project Kernel/repository authority above MCP/provider skills and agent memory;
- discover/evaluate an official provider MCP first when an active use case needs provider-native AI tooling, then compare overlap with native connector/CLI/API before adoption;
- keep developer MCP connections out of production application dependencies;
- record GitHub, Neon, Cloudflare, Figma and Grafana official MCP availability/evaluation in the external-system registry;
- govern MCP upgrades as reviewed capability/auth/permission changes instead of automatic `latest` churn;
- pin local/package MCP versions when the distribution supports deterministic pinning, while remote-managed MCPs track review/capability assumptions rather than fake version pins;
- deny newly exposed MCP mutation tools/scopes until explicitly admitted by SongChart governance;
- convert legacy stage-centric documentation toward current semantic owners instead of creating another archive hierarchy;
- use Development History for accepted chronology, ADR/current contracts for durable rules, regression ledger for guarded failures and Git/PR history for exact retired evidence;
- migrate active links/tests/verifiers before deleting each historical stage file;
- audit duplicate tests/verifier scripts/aliases against `verification-topology.json`; keep only one semantic owner per invariant, preserving thin delegating facades and distinct static-vs-runtime coverage where they protect different failure classes;
- delete redundant verification files and references once equivalence/consumer closure is proven instead of retaining compatibility clutter;
- keep GitHub as source/delivery authority and Docker/runtime state disposable.

## Recheck / re-verification discipline

`docs/project/engineering/recheck-policy.json` owns the recheck triggers. Stage 20 must not rely on developer or AI memory to remember them.

- on a resumed/new AI session, resolve live branch/head/upstream and current generated project state before substantial writes;
- after an authored authority changes, run `./songchart reconcile`, inspect the generated diff, then rerun impacted focused checks;
- before advancing a tranche, rerun repository-contract and stage verification on the current tree; an earlier green SHA is not reusable evidence for a changed SHA;
- before candidate/canonical closure, regenerate and verify the exact target head and do not mutate that SHA while closure is running;
- when an adopted/relevant MCP changes version, auth, permissions, tool/resource surface, deprecations or behavior, recheck official provider evidence and update the registry before expanding use;
- do not add another parallel `recheck` verifier command when existing `ai status/doctor`, `reconcile`, repository-contracts, stage and canonical surfaces already own these checks.

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

## Tests and verification

Stage 20 uses existing governed verification surfaces; successful evidence is SHA/tree-specific and must be rerun at the recheck boundaries declared in `recheck-policy.json`.

- `./songchart reconcile` after authored authority changes; inspect generated diffs before commit.
- `./songchart impact --diff` to resolve impacted authorities/focused checks for changed source.
- `./songchart composer repository-contracts:verify` for repository authority/consumer closure.
- `./songchart composer stage:verify` before tranche advancement.
- focused Pest/Architecture/runtime checks returned by impact resolution for implementation changes.
- candidate/canonical exact-head verification only after the tracked tree is reconciled and clean.
- official external integration evidence must be re-reviewed when an MCP/provider trigger in `recheck-policy.json` fires; newly exposed mutation capabilities remain denied until explicitly admitted.
- verification consolidation must use existing ownership/consumer graph evidence: duplicate semantic checks are removed only after the surviving owner preserves the required failure class; distinct static and behavioral checks may coexist when they prove different things.

## Verification and closure

Preserve `./songchart` facade; keep verification DB isolated; regenerate `docs/project/generated/*` only through repository tooling; commit source/contract changes before generated reconciliation; close Stage 20 only after exact-head candidate/canonical verification and clean tracked tree.
