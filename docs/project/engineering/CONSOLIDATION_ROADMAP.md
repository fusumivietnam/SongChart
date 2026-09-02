# SongChart Consolidation Roadmap

Status: active engineering roadmap for reducing custom infrastructure, legacy branding and duplicated documentation surfaces.

## Goal

Keep SongChart product-specific where it must be, conventional everywhere else. Prefer documented Laravel and mature package ownership, retire replaced custom code quickly, and keep one repository-owned machine context that AI and future documentation generators can consume without conversational memory.

## Sequence

### C1 — Knowledge + brand authority

- Canonical active brand is `SongChart`.
- `SongChartWeb` is a legacy alias to remove from active source/config/UI/docs; immutable historical records may retain it when chronology would otherwise be damaged.
- `project-knowledge.json` is the compact product/domain/documentation knowledge source.
- `consolidation-plan.json` owns native/package/custom decisions and retirement work.
- Root `AGENTS.md` points AI agents to those repository authorities before chat context.

### C2 — Runtime/observability consolidation

- Adopt Laravel Horizon for Redis worker supervision and queue operational visibility.
- Expand existing Laravel Pulse rather than creating a SongChart monitoring dashboard.
- Remove or reduce custom worker/dashboard glue that becomes redundant.
- Keep SongChart-owned job taxonomy, retry/admission semantics and provider reason codes.

### C3 — Provider integration consolidation

- Inventory provider HTTP clients and transport helpers.
- Evaluate `saloonphp/laravel-plugin` as the standard connector/request/auth/retry/testing layer.
- Migrate eligible providers in bounded tranches; do not rewrite canonical identity/taxonomy semantics into Saloon.
- Delete provider-specific HTTP boilerplate once migrated.

### C4 — Data/API contract consolidation

- Inventory request DTOs, response transformers/resources and repeated cross-boundary shapes.
- Evaluate `spatie/laravel-data` as the typed data/validation/transformation owner before API v1 expansion.
- Generate TypeScript/API schemas from typed data where practical instead of maintaining duplicate definitions.
- Keep domain invariants in domain code; do not turn data objects into business services.

### C5 — Verification/source reduction

- Stop adding one-off verifier scripts by default.
- Move behavioral assertions into Pest/Architecture tests.
- Move declarative ownership/static rules into repository contracts/compiler inputs.
- Retire redundant verifier scripts only after equivalent consumers are proven.
- Reduce `songchart-mobile.yml` to diagnostic fallback or retire it after Auto Closure is proven across multiple tranches.

### C6 — Backup/recovery consolidation

- Adopt `spatie/laravel-backup` for backup orchestration, retention and notifications.
- Keep one SongChart-owned isolated restore drill that proves a usable application state.
- Remove redundant backup/rotation scripts after package-backed flow is accepted.

### C7 — Security consolidation

- Keep Fortify, Laravel Gates/middleware/rate limiting/signed URL primitives and Caddy/TLS as generic security owners.
- Evaluate extra packages only where they replace a real custom surface with stronger documented ownership.
- Keep custom security only for SongChart-specific trust boundaries such as outbound provider destination validation, provider credential policy and canonical mutation rules.

### C8 — Documentation generation

Use `project-knowledge.json`, generated project context, route authority and typed data contracts as sources for:

- API reference;
- developer setup/architecture reference;
- operator documentation;
- FAQ/product capability reference.

Generated documentation is a projection. The typed/machine source remains authoritative.

## Package admission test

A package is preferred when it has active maintenance, compatible Laravel/PHP support, clear upstream documentation, testability, bounded migration cost and replaces more custom surface than it introduces. A package is rejected when SongChart would still need to maintain an equally complex parallel abstraction around it.

## Source-size rule

Every consolidation tranche records both additions and removals. A package adoption is incomplete until the replaced custom files/classes/configuration are removed or explicitly scheduled in the next bounded tranche.

## Automation targets

- Auto Closure for PREPARE/CHECK/CLOSE/Ready: implemented.
- Protect `main` with GitHub-native PR/check rules.
- Classify CI failures from existing owner/gate/risk registries.
- Automate safe dependency update PRs.
- Generate docs from machine/typed authorities.
- Build accepted-main release package/tag automatically after human promotion.
- Run isolated scheduled restore drills with bounded evidence.
