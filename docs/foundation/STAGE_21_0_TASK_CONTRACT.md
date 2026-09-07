# Stage 21.0 — Editorial Admin UX

## Purpose

Turn the existing governed editorial/admin surfaces into a clear, safe operator workflow without weakening canonical-admission, provenance, authorization, audit, development continuity, or data-safety boundaries.

Stage 21 is primarily a UX/application-surface stage. It reuses Stage 20 domain/application contracts and must not invent provider-specific canonical identity, bypass canonical admission, or introduce schema changes unless a separately accepted gap proves they are required.

The Stage 21 continuity corrective also closes two development-experience gaps discovered while exercising the editorial surface in Codespaces: a missing development Super Admin could not be recreated non-interactively, and a disposable Codespace could silently fall back to local PostgreSQL unless the durable remote authority was reconstructed manually.

## Product owner

`editor.ingest_and_admit` and `operator.observe_and_recover` from `docs/project/domain/product-user-journeys.json`.

## Invariants

1. Canonical mutation remains owned by the existing governed admission service.
2. Editorial UX must distinguish evidence, queued decisions, applied decisions, and rejected decisions.
3. High-impact actions must state what will happen before submission and remain auditable.
4. Read-only/detail surfaces may improve presentation but must not duplicate mutation semantics.
5. Existing authorization remains authoritative; UI visibility is never treated as authorization.
6. Provider evidence remains provenance/context, not canonical identity.
7. No Stage 21 tranche may silently add schema/domain concepts to solve a presentation problem.
8. Codespaces compute and local Docker containers are disposable; configured durable PostgreSQL identity must survive environment recreation through external secret-backed authority.
9. Development privileged-account recovery must preserve existing credentials and two-factor state; a missing account may be recreated only from explicit secret-backed bootstrap credentials.
10. Verification ownership remains non-duplicative: cheap governance/static checks run before expensive runtime lanes, but accepted-main provenance remains exact-SHA specific.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md` — repository workflow, architecture and closure precedence.
- `docs/project/engineering/stage-plan.json` — authored Stage 21 progress/tranche authority.
- `docs/project/domain/product-user-journeys.json` — product journey authority for editorial/operator work.
- `docs/project/engineering/development-database-contract.json` — local/remote PostgreSQL, Neon Codespaces continuity, database identity, backup/restore and development-admin continuity authority.
- `docs/project/stack/docker-development-contract.json` — Docker/Codespaces runtime ownership and disposable-container policy.
- `.env.docker.example`, `scripts/setup-docker-dev.sh`, `songchart` and `compose.codespaces.yml` — development configuration/bootstrap implementation surfaces; real secrets remain outside source.
- `app/Console/Commands/EnsureLocalAdminCommand.php` — idempotent development administrator recovery owner.
- `app/Support/Development/DevelopmentDatabaseAuthority.php` and `DevelopmentDatabaseStatusCommand.php` — fail-closed configuration and runtime identity diagnostics.
- `app/Application/Admin/Queries/CanonicalAdmissionReviewConsole.php`, `CanonicalAdmissionController.php`, admin Blade views, existing authorization/audit services and route authority — current editorial UX/application ownership.
- `.github/workflows/auto-closure.yml`, `.github/workflows/tests.yml`, `scripts/verify-ci-configuration.php`, `docs/project/engineering/verification-topology.json` and `verification-command-surface.json` — CI/verification orchestration and deduplication authority.
- `composer.json`, `composer.lock`, `package.json`, `package-lock.json` — installed package/runtime evidence; exact package versions are lockfile authority.

### Installed versions

Stage 21 reuses the accepted runtime baseline rather than introducing a new stack:

- PHP major `8.5`.
- Laravel major `13`.
- PostgreSQL major `18` for governed verification/runtime compatibility.
- Node.js major `24` for GitHub application/browser/frontend lanes.
- Livewire major `4` as the current frontend interaction layer.
- Redis `7.4` in Docker development; cache/queue state remains disposable.

Exact package patch versions remain owned by `composer.lock` and `package-lock.json` and must not be duplicated here.

### Official external sources

No new frontend/admin framework or package is adopted by Stage 21. Editorial UX continues to use the installed Laravel/Fortify/Blade/Application-layer surfaces already governed by the repository.

The Codespaces continuity corrective uses Neon as the preferred durable development PostgreSQL provider, consistent with the Stage 20 database contract. Official Neon evidence reviewed for this change includes:

- Neon connection guidance and pooled connections: `https://neon.com/docs/connect/connection-string` and `https://neon.com/docs/connect/connection-pooling`.
- Neon secure PostgreSQL connection guidance using TLS/`sslmode=require`: `https://neon.com/docs/connect/connect-securely`.
- Neon restore / point-in-time recovery and branching guidance: `https://neon.com/docs/introduction/branching` and `https://neon.com/docs/manage/backups`.

Provider recovery capabilities are an additional provider-owned safety layer. They do not replace SongChart migrations, expected-database identity checks, portable `songchart dev db backup/restore`, or GitHub/Codespaces secret boundaries.

### Native capability assessment

Stage 21 prefers existing native/project capabilities before custom work:

- Laravel Fortify remains login/authentication owner; the login flow is not replaced.
- Existing `admin:ensure-local` remains the privileged development bootstrap owner. It is extended for explicit secret-backed recreation instead of adding a second bootstrap command.
- Existing canonical-admission service, review query, authorization matrix and audit owner remain authoritative; UX changes only improve information hierarchy and action clarity.
- `.env.docker`, `./songchart dev setup`, development database status, backup/restore, and Docker/Codespaces adapters remain the development runtime control plane. Neon is connected through those surfaces rather than introducing a second application database abstraction.
- GitHub Codespaces repository secrets are used as the durable secret source; secret values are never committed.
- Existing `quality:verify`, reusable tests workflow and canonical close remain verification owners. Stage 21 changes their ordering/deduplication rather than creating a parallel verifier.
- Main-branch integration verification remains enabled because a merge commit is a new exact SHA; pre-merge evidence is not reused until an explicit trusted tree-equivalence provenance mechanism exists.

### Custom implementation justification

Custom Stage 21 changes are bounded to SongChart-specific gaps that native components do not solve automatically:

- Editorial information hierarchy is application-specific and must reflect SongChart evidence/admission states, while mutation semantics remain in the existing governed service.
- A missing development Super Admin previously forced interactive `admin:create`, which breaks unattended `dev ready`/Codespaces recovery. `admin:ensure-local` therefore accepts `SONGCHART_DEV_ADMIN_PASSWORD` only for missing-account creation while preserving existing password/2FA state.
- `.env.docker` is intentionally gitignored and can disappear with a Codespace. `scripts/setup-docker-dev.sh` therefore rehydrates the Neon URL from `SONGCHART_DEV_NEON_DATABASE_URL`, derives the expected database name, selects remote mode and enforces TLS before migrations.
- Auto Closure previously discovered cheap authority/quality failures only after generated commits were pushed and runtime jobs were started. Quality is therefore moved into PREPARE before generated push/runtime lanes, and the reusable workflow can mark that same exact SHA as `quality_preverified` to avoid running quality twice.
- No second CI system, second database abstraction, new authentication framework, new schema owner, or new canonical entity is justified by these changes.

## Tranches

### 21.0A — Admission queue information hierarchy

Status: accepted

Goals:
- Make pending/applied/rejected state immediately legible.
- Separate queued decisions from unstaged evidence.
- Improve value/source/entity presentation without changing read-model ownership.
- Make empty/unavailable states actionable and operator-oriented.
- Restore development continuity when the SA account or Codespace-local DB configuration is missing.
- Keep CI cheap failures in the quality preflight before runtime lanes.

Acceptance:
- Admission index has explicit workflow guidance and state labels.
- Pending work and unstaged evidence are visually/semantically distinct.
- Existing stage/show routes remain the only actions from the index.
- Regression coverage protects the UX contract.
- A missing development administrator can be recreated non-interactively only when an explicit password secret is configured.
- Existing development administrator password and two-factor state remain unchanged.
- Codespaces can reconstruct the same Neon remote database authority from repository secret configuration without committing the connection string.
- Remote PostgreSQL requires TLS and expected database identity before mutation/migration.
- Auto Closure proves quality on the prepared exact tree before pushing generated authority or starting PostgreSQL/browser/frontend lanes.

### 21.0B — Decision safety and review context

Status: implementing

Goals:
- Improve review context and decision consequences.
- Make apply/reject actions unambiguous.
- Preserve rationale requirement and audit semantics.

### 21.0C — Editorial navigation and accessibility

Status: planned

Goals:
- Normalize editorial navigation vocabulary.
- Improve keyboard/focus/semantic labeling on high-frequency admin flows.
- Keep responsive layouts usable on constrained/mobile operator sessions.

### 21.0D — Stage acceptance and closure

Status: planned

Goals:
- Run impact/canonical verification on exact head.
- Confirm no domain/schema drift was introduced by UX work.
- Close Stage 21.0 only from clean, verified exact-head evidence.

## Tests and verification

Stage 21 reuses existing governed surfaces; no separate Stage-21-only verification engine is introduced.

- Auto Closure PREPARE regenerates project/generated authority and runs `composer validate --strict` plus `composer quality:verify` before any generated push/runtime lanes.
- `tests/Feature/Development/LocalAdminErgonomicsTest.php` protects existing-password/2FA preservation and secret-backed missing-account recreation.
- `tests/Unit/Support/Development/DevelopmentDatabaseAuthorityTest.php` protects remote URL, non-local host, expected database identity and TLS requirements.
- `composer local-data-safety:verify` and `composer docker-local:verify` remain the semantic owners for development database/admin/Codespaces safety.
- Reusable PostgreSQL, browser-smoke and frontend-build lanes execute only after quality preflight passes on the exact prepared SHA.
- `./songchart verify` remains canonical closure owner and must preserve the exact tracked tree.
- Main push CI remains the accepted-main provenance owner for the merge SHA until an explicitly governed tree-equivalence reuse mechanism is implemented.

## Explicit non-goals

- Public Stage 23 UX redesign.
- Recommendation/social features.
- New provider adapters.
- New canonical entity types.
- Schema migrations for presentation-only needs.
- Replacing existing authorization, admission, provenance, audit or Fortify authentication owners.
- Committing Neon or administrator secret values.
- Treating local Docker PostgreSQL as a silent fallback after remote authority has been configured.
- Skipping main-branch verification merely because a PR head was previously green.
