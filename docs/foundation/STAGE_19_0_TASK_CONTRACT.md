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

## Acceptance criteria

- A supported first-release deployment topology is explicit, minimal and derived from current runtime authority.
- Production environment/secrets ownership is documented and fail closed for required values without tracked real secrets.
- Web, queue worker and scheduler lifecycle are production-operable with clear restart/failure behavior.
- PostgreSQL 18 production persistence has a documented and verified backup/restore path.
- Redis/queue/cache/runtime dependencies have explicit production ownership and health expectations.
- Observability covers actionable request/job/database/cache/runtime failure classes without exposing secrets.
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

### 19.0.2 — Secrets/environment hardening

- Define production environment contract and secret injection boundaries.
- Add fail-closed configuration validation where justified.
- Never commit real secret values.

### 19.0.3 — Queue/scheduler production runtime

- Define independent web/worker/scheduler lifecycle where required.
- Preserve current queue taxonomy/rate/admission semantics.
- Add restart/failure/health evidence.

### 19.0.4 — Observability + alerting

- Reuse existing Laravel/runtime observability primitives where possible.
- Define actionable production health/failure signals.
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

- Docker/Compose and Caddy runtime configuration;
- environment templates/config validation;
- Laravel queue/scheduler/runtime entrypoints;
- PostgreSQL/Redis operational configuration;
- health/observability surfaces;
- deployment/release/backup scripts and docs where already owned or explicitly introduced;
- engineering/release authorities only when their semantics actually change.

Product domain/application code is out of scope unless a concrete production blocker requires a bounded corrective.

## Security and secrets

- Real secrets never enter tracked source, logs, generated authority or test fixtures.
- Production secrets must be injected through the selected deployment environment/runtime secret boundary.
- Existing provider credential ownership remains authoritative.
- Existing authorization and privileged audit remain mandatory for privileged mutations.
- Any production diagnostics must redact credentials, tokens, cookies and private configuration values.

## Data and recovery

- PostgreSQL 18 remains release-authoritative.
- Historical migrations remain immutable.
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

No new workflow command is planned by default. If Stage 19 proves a new verification/deployment mechanism is necessary, the same logical change must update:

- the owning Markdown engineering authority;
- machine contract/routing;
- a permanent regression/consumer owner.

Do not add a convenience script that becomes an unowned second verification authority.

## AI-assisted operations boundary

AI may assist development by reading deterministic verification evidence and recommending/implementing bounded changes. Production AI operations, if introduced later, begin read-only with `observe → explain → recommend`. Any mutation must pass through the same governed application action, authorization and audit surfaces as human Admin operations.

## Documentation impact

- `docs/project/DEVELOPMENT_STATE.md` owns operational current state.
- This task contract owns Stage 19 scope/acceptance.
- `docs/project/docs/ROADMAP.md` stays active/future-only.
- `docs/project/DEVELOPMENT_HISTORY.md` records accepted Stage 18.6 chronology and later Stage 19 acceptance.
- Validation evidence for Stage 19 belongs in `docs/foundation/STAGE_19_0_VALIDATION_REPORT.md`.

## Delivery and handoff

- Current writer/owner: `stage-19.0-production-readiness-first-release`.
- Base: accepted `main` merge `40eba85e36bed1d3a5604975e45ad3234aca6e25`.
- Every handoff requires exact pushed commit state and no unresolved upstream divergence.
- Any tracked change after canonical PASS invalidates closure evidence for that exact head.

## Rollback

Production-readiness changes should be bounded and independently revertible where possible. Do not roll back accepted canonical/provider identity history or remove immutable audit evidence as part of infrastructure rollback.
