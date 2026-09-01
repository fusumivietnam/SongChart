# Stage 19.0 Task Contract — Production Readiness & First Release

Status: active task contract.

## Goal

Turn the accepted SongChart product tree into a reproducible, observable, recoverable and security-reviewed first production release without weakening existing canonical, provider, authorization, audit, migration or verification authorities.

## Accepted baseline

- Stage 18.6 exact sealed head: `0ba79a89994b0a2722f5f6c18a84af0045e88cc6`.
- Stage 18.6 candidate verification: PASS.
- Stage 18.6 canonical verification: PASS.
- GitHub Actions PR run #161: PASS on the exact sealed head.
- PR #15 accepted Stage 18.6 into `main` as merge commit `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Stage 19 branch starts from that exact accepted merge commit.

## Non-goals

- No new product/provider breadth merely because production readiness work exposes future opportunities.
- No migration rewrite or historical migration mutation.
- No secret values committed to source control.
- No platform-specific deployment framework before topology inventory proves it is needed.
- No replacement of PostgreSQL 18 release authority.
- No bypass of Laravel Gate, password confirmation, privileged audit, provider credential/rate controls or canonical identity boundaries.
- No network/provider smoke check promoted to canonical deterministic verification.
- No opaque AI-operated production mutation surface.
- No universal JSON schema or generic integration framework that duplicates typed domain/provider owners.
- No permanent compatibility vocabulary solely to keep obsolete test/fixture/source spellings alive when a current typed contract owns the same meaning.
- No custom CI orchestration framework when GitHub-native checks, concurrency, artifacts, protected branches or releases own the required integration behavior.

## Acceptance criteria

- A supported first-release deployment topology is explicit, minimal and derived from current runtime authority.
- Cross-boundary data representation rules are explicit for identifiers, timestamps, null/collection semantics, URLs, machine reason codes and provider evidence.
- Provider identity, category, operational role and capability are distinct concepts with one typed taxonomy owner.
- Unknown provider category/capability values fail fast instead of silently becoming new formats.
- Legacy source/tests/fixtures/seeders exposed by current work converge to current typed/data contracts instead of causing the current contracts to accumulate obsolete synonyms.
- Overview documentation does not contradict current typed/machine authorities; duplicated older conventions are corrected or reduced to pointers.
- Provider operational readiness is derived consistently from taxonomy, enablement, policy status, configuration/credentials and health evidence; unknown health is not treated as healthy.
- Production environment/secrets ownership is documented and fail closed for required values without tracked real secrets.
- Web, queue worker and scheduler lifecycle are production-operable with clear restart/failure behavior.
- PostgreSQL 18 production persistence has a documented and verified backup/restore path.
- Redis/queue/cache/runtime dependencies have explicit production ownership and health expectations.
- Observability covers actionable request/job/database/cache/runtime failure classes without exposing secrets.
- GitHub CI preserves bounded non-secret failure evidence needed for rapid mobile/remote diagnosis without making runtime logs tracked source authority.
- Production security review covers public/admin/auth/provider/secrets surfaces and preserves existing authorization/audit semantics.
- Production smoke verification checks the deployed application end to end while keeping live external-provider checks non-canonical.
- Candidate and canonical verification pass on the exact final Stage 19 tree.
- First release package/tag is created only from an accepted exact `main` tree after PR CI targets the same closed SHA.

## Planned slices

### 19.0.1 — Production topology + environment inventory

- Inventory Docker/Caddy/PHP/PostgreSQL/Redis/queue/scheduler/runtime assumptions.
- Inventory production-relevant environment variables and current owners.
- Identify supported process topology and explicit gaps.
- Do not add deployment implementation until topology is explicit.

### 19.0.1.1 — Data Contract + Provider Taxonomy Convergence

- Define one cross-boundary representation authority for recurring data shapes instead of allowing controller/view/integration-specific formatting.
- Define typed provider category, operational role and capability codes over the existing provider registry schema.
- Classify current providers as data, destination or service integrations without changing canonical identity semantics.
- Seed current known capabilities from one taxonomy registry and reject unknown category/capability strings at the persistence boundary.
- Define stable provider operational state and runtime reason codes for later Admin/health/config consumers.
- Keep existing persisted category values compatible where they are genuine categories; no schema migration is required for this convergence slice.
- Converge touched/exposed legacy fixtures, seeders and source to the current taxonomy/data vocabulary. A legacy role-like category or provider-specific capability spelling is migrated to its current owner rather than added as an enum alias.
- Converge overview documentation such as `DATA_MODEL.md` to the canonical `DATA_CONTRACT.md` when older wording is ambiguous.
- Prefer canonical provider factory/state helpers and typed values for recurring test fixtures so future tests do not reconstruct provider vocabulary from raw strings.
- Add/extend permanent architecture/static guards for recurring vocabulary drift when the affected owner can be checked deterministically.
- Keep GitHub-native failure evidence transport bounded: PostgreSQL CI may upload the existing redacted failure log on failure, named by exact SHA/run attempt, with short retention.

### 19.0.2 — Secrets/environment hardening

- Define production environment contract and secret injection boundaries.
- Add fail-closed configuration validation where justified.
- Use provider taxonomy/capabilities to validate enabled service/provider configuration without inventing per-integration formats.
- Never commit real secret values.

### 19.0.3 — Queue/scheduler production runtime

- Define independent web/worker/scheduler lifecycle where required.
- Preserve current queue taxonomy/rate/admission semantics.
- Add restart/failure/health evidence.

### 19.0.4 — Observability + alerting

- Reuse existing Laravel/runtime observability primitives where possible.
- Define actionable production health/failure signals using stable machine reason codes.
- Avoid dashboards/alerts without an operator use case.

### 19.0.5 — Backup/recovery

- Define PostgreSQL backup process, retention ownership and restore procedure.
- Require verified restore evidence before closure.

### 19.0.6 — Security review + production smoke

- Review auth/Admin/provider credentials/secrets/public exposure/TLS/security headers as impacted.
- Run deterministic production smoke where possible.
- Treat external provider/network checks as release-confidence evidence only.

### Final — First release package/tag

- Final cumulative QA.
- `./songchart impact --verify` PASS.
- Candidate PASS.
- Canonical PASS.
- Clean exact pushed HEAD.
- PR CI on the exact SHA.
- Merge to `main`.
- Create release package/tag from the accepted exact `main` tree only.

## Affected modules and boundaries

Inventory determines exact paths. Expected areas include:

- `app/Domain/Providers` typed taxonomy/readiness semantics and existing provider models/seed data where required;
- shared domain/provider documentation for cross-boundary data shape and provider classification;
- recurring provider test fixtures/factories where raw legacy vocabulary creates drift;
- Docker/Compose and Caddy runtime configuration;
- environment templates/config validation;
- Laravel queue/scheduler/runtime entrypoints;
- PostgreSQL/Redis operational configuration;
- health/observability surfaces;
- GitHub Actions evidence transport and integration checks when governed by the existing CI authority;
- deployment/release/backup scripts and docs where already owned or explicitly introduced;
- engineering/release authorities only when their semantics actually change.

Product feature behavior remains out of scope unless a concrete production blocker requires a bounded corrective.

## Security and secrets

- Real secrets never enter tracked source, logs, generated authority or test fixtures.
- Production secrets must be injected through the selected deployment environment/runtime secret boundary.
- Existing provider credential ownership remains authoritative.
- Existing authorization and privileged audit remain mandatory for privileged mutations.
- Any production diagnostics must redact credentials, tokens, cookies and private configuration values.
- Service providers do not gain catalog import/canonical mutation capability merely because they share the provider registry.
- CI failure artifacts are diagnostic evidence only, short-retained, non-secret and never a substitute for tracked authority or canonical evidence.

## Data and recovery

- PostgreSQL 18 remains release-authoritative.
- Historical migrations remain immutable.
- Cross-boundary representation follows `docs/project/domain/DATA_CONTRACT.md`.
- Provider classification follows `docs/providers/PROVIDER_TAXONOMY.md` and the typed `ProviderTaxonomy` owner.
- Active legacy consumers converge toward those current authorities when touched; compatibility is explicit rather than inferred.
- Backup/recovery must prove restore to a usable application state rather than only prove backup file creation.
- Destructive verification must use isolated test/recovery targets, never the development or production database.

## Verification plan

For each bounded slice:

```text
INVENTORY / PLANNED PATHS
        ↓
./songchart impact <planned-paths...>
        ↓
SOURCE / CONTRACT / TEST CHANGE
        ↓
PINT WRITE + --TEST ON CHANGED PHP (when applicable)
        ↓
FOCUSED TESTS / PHPSTAN / STATIC RUNTIME CHECKS
        ↓
SOURCE COMMIT
        ↓
./songchart reconcile
        ↓
GENERATED-ONLY COMMIT (when needed)
        ↓
./songchart impact --verify
```

When a broad gate reports several failures caused by one legacy vocabulary owner, inventory the affected values/fixtures first, converge them in one bounded corrective, run focused coverage, and only then repeat the expensive lane.

Stage closure:

```text
./songchart candidate
./songchart verify
exact HEAD + clean tree + upstream sync
PR CI on exact SHA
accepted main merge
release package/tag from accepted main
```

## Workflow mechanism rule

No new workflow orchestration framework is planned. If Stage 19 proves a new verification/deployment mechanism is necessary, the same logical change must update:

- the owning Markdown engineering authority;
- machine contract/routing when applicable;
- a permanent regression/consumer owner.

GitHub-native capabilities are preferred for integration concerns that GitHub already owns: PR checks, concurrency cancellation, branch protection, failure artifacts and accepted-main releases. Do not add a convenience script that becomes an unowned second verification authority.

## AI-assisted operations boundary

AI may assist development by reading deterministic verification evidence and recommending/implementing bounded changes. Production AI operations, if introduced later, begin read-only with `observe → explain → recommend`. Any mutation must pass through the same governed application action, authorization and audit surfaces as human Admin operations. AI providers remain service providers unless a separately approved capability contract explicitly grants a bounded data operation; they never gain canonical mutation implicitly.

For development AI, current authority beats historical spelling. AI must identify the semantic owner before changing code, prefer typed/generated repository facts over guessed literals, converge legacy consumers toward the current owner, and avoid broadening contracts simply to silence old fixtures. When deterministic GitHub failure evidence exists, use that exact SHA/run evidence before reconstructing a failure from prose.

## Documentation impact

- `docs/project/DEVELOPMENT_STATE.md` owns operational current state.
- This task contract owns Stage 19 scope/acceptance.
- `docs/project/domain/DATA_CONTRACT.md` owns recurring cross-boundary representation rules.
- `docs/providers/PROVIDER_TAXONOMY.md` owns provider classification vocabulary.
- `docs/project/engineering/DELIVERY_WORKFLOW.md` owns repository convergence during delivery, mobile/GitHub-native handoff and failure-evidence transport semantics.
- `docs/project/docs/ROADMAP.md` stays active/future-only.
- `docs/project/DEVELOPMENT_HISTORY.md` records accepted Stage 18.6 chronology and later Stage 19 acceptance.
- Validation evidence for Stage 19 belongs in `docs/foundation/STAGE_19_0_VALIDATION_REPORT.md`.

## Delivery and handoff

- Current writer/owner: `stage-19.0-production-readiness-first-release`.
- Base: accepted `main` merge `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Every handoff requires exact pushed commit state and no unresolved upstream divergence.
- Mobile operation should use GitHub as the control/evidence plane and keep terminal interaction short; source changes are committed/pushed by the active writer rather than pasted across devices.
- Any tracked change after canonical PASS invalidates closure evidence for that exact head.

## Rollback

Production-readiness changes should be bounded and independently revertible where possible. Do not roll back accepted canonical/provider identity history or remove immutable audit evidence as part of infrastructure rollback.
