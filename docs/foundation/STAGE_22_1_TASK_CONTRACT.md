# Stage 22.1 — System Intersection Map + Golden Vertical Contract

## Purpose

Shift SongChart from horizontal architecture expansion to production-oriented vertical closure. Stage 22.1 makes subsystem intersections first-class machine authority, defines the first golden provider-to-public-song flow, and identifies which critical edges are verified, partial, missing or unknown before implementation continues.

This stage must not create another architecture abstraction layer for its own sake. Its purpose is to expose whether already-built provider, canonical, chart, public, search, SEO, editorial and release capabilities actually compose into one end-to-end product outcome.

## Product owner

Primary outcome: a user can discover a real SongChart song/chart projection whose provider evidence and canonical identity are traceable, reproducible and correctable.

Operator outcome: a governed editorial correction propagates to dependent projections without manual cross-system database repair.

## Invariants

1. Component existence is not treated as flow closure.
2. Every critical intersection must have an explicit source, consumer, status and verification owner/evidence route.
3. Provider evidence remains provenance; it does not become canonical identity by ingestion success alone.
4. Canonical mutation remains governed by existing admission/authorization/audit owners.
5. No chart row may be considered golden without canonical identity and reproducible calculation/input provenance.
6. Public song, artist, chart, search and SEO projections must agree on canonical identity.
7. Golden input replay must be idempotent at observation/canonical projection boundaries.
8. A semantic correction must invalidate or recompute dependent projections through governed application/domain mechanisms, not direct manual repair.
9. Existing impact, quality, PostgreSQL, browser, frontend, candidate and canonical verification remain owners; Stage 22.1 must not invent a duplicate verifier when an existing lane can own the assertion.
10. Generated project/development state remains projection-only and is regenerated through existing Auto Closure authority.

## Repository authorities

- `PROJECT_AUTHORITY.md` — repository workflow and closure precedence.
- `docs/project/engineering/stage-plan.json` — authored stage/tranche progress authority.
- `docs/project/engineering/system-intersection-map.json` — critical product-flow edge status authority introduced by this stage.
- `docs/project/engineering/golden-flow-contract.json` — golden provider-to-public vertical contract introduced by this stage.
- `docs/project/domain/product-user-journeys.json` — existing product journey authority.
- `docs/project/domain/application-data-boundary.json` — application/controller data-access boundary.
- `docs/project/engineering/architecture-graph-contract.json` — existing architecture graph authority; Stage 22.1 complements it by owning cross-subsystem flow closure, not component topology.
- `docs/project/stack/impact-test-map.json` — impact-to-verification mapping.
- `docs/project/engineering/verification-consumer-graph.json` — verifier ownership/routing.
- `docs/project/engineering/verification-topology.json` and `verification-command-surface.json` — canonical verification orchestration.
- `docs/project/RELEASE_BASELINE_STATUS.md` — accepted source-to-release closure.

## Tranches

### 22.1A — Intersection audit and ownership binding

Status: active

Goals:
- Trace existing implementation for every critical intersection in `system-intersection-map.json`.
- Replace `unknown` with evidence-based `verified`, `partial` or `missing`.
- Bind each verified/partial edge to concrete code/tests/commands rather than prose assumptions.
- Identify the smallest missing edge set required for one complete provider-to-public-song flow.

Acceptance:
- No critical edge remains `unknown`.
- Every `verified` edge cites an existing concrete verification owner/evidence route.
- Every `partial`/`missing` edge has one bounded implementation owner and next action.
- No subsystem is rewritten merely to make the map visually complete.

### 22.1B — Golden provider/canonical fixtures

Status: planned

Goals:
- Establish representative golden cases for single-provider, multi-provider, ambiguous identity, rerelease/remaster and provider-conflict behavior.
- Reuse real domain/provider contracts while keeping verification deterministic.
- Prove provenance retention and idempotent replay through admission/canonical projection.

Acceptance:
- All required golden cases have deterministic fixtures/evidence.
- Replaying identical source evidence does not duplicate canonical/public identity.
- Ambiguous/conflicting evidence remains governed and does not auto-collapse incorrectly.

### 22.1C — Chart/public provenance contract closure

Status: planned

Goals:
- Bind canonical song identity to chart input/result provenance.
- Prove public song/artist/chart projections agree on canonical identity.
- Bind search/SEO/cache behavior to the same semantic source where already implemented; record missing production work otherwise.

Acceptance:
- Golden chart rows expose enough provenance to reproduce their ranking basis/version/input set.
- One golden song is consistently represented across chart and public projections.
- Critical missing chart/public/search/SEO edges are either implemented in this tranche or explicitly carried into the bounded next Stage 22 tranche.

### 22.1D — Stage closure and next vertical tranche

Status: planned

Goals:
- Run impact and canonical verification on exact head.
- Confirm authored intersection/golden-flow authorities agree with implementation evidence.
- Set the next Stage 22 tranche from measured missing edges rather than architecture speculation.

Acceptance:
- Critical intersection state contains no `unknown` values.
- Golden-flow acceptance state is evidence-based, not manually declared complete.
- Quality/runtime/browser/frontend lanes required by impact pass on exact head.
- Canonical verification passes without tracked-tree mutation.
- Next tranche is bounded to the smallest remaining production vertical gap.

## Explicit non-goals

- GitHub-to-GitLab migration.
- New CI platform or duplicate verification engine.
- Payment, legal-entity or monetization implementation.
- Public Stage 23 redesign unrelated to closing the golden vertical flow.
- Microservices/Kubernetes/data-warehouse work.
- New provider adapters unless the golden flow proves an existing-provider capability is insufficient.
- Direct production write/remediation control plane.

## Delivery rule

Stage 22.1 changes the default development question from “what subsystem should be built next?” to “which user-visible outcome lacks a verified end-to-end path?”. Future implementation work should be selected from missing/partial critical edges in `system-intersection-map.json` and validated against `golden-flow-contract.json`.
