# Stage 11 Foundation Baseline

Status: current foundation baseline for SongChart `0.1.0-dev` after Stage 11.9.

## Purpose

This document summarizes the implemented Stage 11 foundation. Stable rules remain owned by the canonical project and module authorities linked below; this baseline does not replace them.

## Implemented boundaries

### Documentation governance

- `AGENTS.md` defines repository-wide agent rules.
- `docs/START_HERE.md` routes work to the owning documents.
- `docs/DOCUMENTATION_GOVERNANCE.md` defines authority, lifecycle and reconciliation rules.
- `docs/DOCUMENTATION_INDEX.md` maps ownership.
- `composer docs:verify` validates required authorities, internal Markdown links and current-stage uniqueness.

### Laravel-native application boundaries

- Fortify owns authentication, password reset, email verification and TOTP flows.
- Laravel Gates own admin and extension-mutation authorization.
- Form Requests own HTTP validation at normalized write/search boundaries.
- Application Actions own use-case orchestration; controllers remain HTTP adapters.
- Laravel Queue and Scheduler own provider operational health execution.
- Eloquent/database transactions remain the persistence boundary for current foundation features.

See:

- `docs/project/docs/adr/ADR-003-laravel-native-application-boundaries.md`;
- `docs/foundation/STAGE_11_3_LARAVEL_FEATURE_ALIGNMENT_AUDIT.md`;
- `docs/foundation/STAGE_11_4_AUTHORIZATION_MODEL.md`;
- `docs/foundation/STAGE_11_5_REQUEST_ACTION_MODEL.md`;
- `docs/foundation/STAGE_11_6_ASYNC_PROVIDER_MODEL.md`.

### Quality and CI gates

The authoritative local gate is:

```bash
composer verify
```

It clears Laravel caches, proves routes are discoverable, runs static quality gates, executes Unit/Architecture/Feature suites and builds Vite assets.

Focused commands:

```bash
composer quality:normalize
composer quality:verify
composer test:unit
composer test:architecture
composer test:feature
composer test:all
composer delivery:verify
```

CI separates:

- PHP quality;
- SQLite tests on PHP 8.3 and 8.4;
- PostgreSQL 17 tests;
- frontend production build.

See `docs/foundation/STAGE_11_7_TESTING_CI_MODEL.md`.

## Runtime operations introduced in Stage 11

Provider health infrastructure requires:

```bash
php artisan queue:work --queue=providers,default
php artisan schedule:run
```

The scheduler dispatches health checks only when enabled by configuration. No live provider adapter or credential was introduced by Stage 11.

## Closure audit

Stage 11.9 adds two explicit modes:

```bash
composer foundation:audit
composer foundation:release
```

The audit mode validates structural coherence and reports known blockers. The release mode fails while mandatory lockfiles are absent. See `docs/foundation/STAGE_11_9_FOUNDATION_CLOSURE_AUDIT.md`.

## Current limitations and blockers

- `composer.lock` and `package-lock.json` are not present in the packaged baseline. Reproducible release remains blocked until they are generated, reviewed and committed on the development machine.
- Full dependency-backed verification must run on the target working copy because delivery archives intentionally exclude `vendor/` and `node_modules/`.
- Provider health infrastructure has no live provider adapter in this stage.
- Stage 11 does not complete canonical catalog identity, production provider ingestion or deployment readiness.

## Historical traceability

Detailed implementation scope remains in:

- `docs/foundation/STAGE_11_*_TASK_CONTRACT.md`;
- `docs/foundation/STAGE_11_SOURCE_AUDIT.md`;
- `STAGE_11_CHANGE_MANIFEST.md`.

Historical task contracts are evidence of delivered scope, not current authorities.

## Next development boundary

The next feature stage must begin only after target-machine verification and release-blocker resolution. It must begin from this baseline, create a new task contract, and update the owning project/module authority rather than extending this document into a general changelog.

## Release closure

Stage 11.10 adds reproducible dependency closure. The authoritative local release command is:

```bash
composer release:verify
```

The command requires valid `composer.lock` and `package-lock.json`, complete quality/test/build gates and `foundation:release`. Lockfiles must be generated from the current manifests on the target development machine; they must never be fabricated in delivery tooling.
