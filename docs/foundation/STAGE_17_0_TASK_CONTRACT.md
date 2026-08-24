# Stage 17.0 — Foundation Closure & Product-State Reconciliation

Status: implementation candidate.

## Goal

Close the foundation era by reconciling delivered provider mutation capability with roadmap state, reducing composition/presentation entropy, consolidating the development design-system entry surface, and freezing non-critical extension infrastructure before live provider work begins.

## Non-goals

- no database migration;
- no provider API integration;
- no new package;
- no new authorization capability;
- no extension marketplace/update feature;
- no new standalone verifier;
- no removal of compatibility preview URLs;
- no deletion of historical Stage 11/12 evidence that is still referenced by repository contracts;
- no product search/ranking behavior change.

## Acceptance criteria

- Stage 16.8 provider mutation/recovery work is treated as delivered history, not future roadmap scope;
- README points to Stage 17.0 and roadmap starts future provider integration at 17.1;
- application-wide bindings remain in `AppServiceProvider`, while authorization/discovery/provider/search composition is owned by focused Laravel service providers;
- admin operation Blade templates do not resolve `AdminOperationsPresentation` from the service container;
- provider chooser Blade does not resolve `ProviderDestinationPolicy` from the service container;
- provider destination policy is injected through a Laravel class-based component;
- `/development/design-system` becomes the canonical internal design-system entry surface while legacy preview URLs remain compatibility aliases;
- known ExtensionInstaller formatting anomaly is normalized;
- extension/plugin runtime remains supported but feature expansion is frozen until live provider/catalog product value is proven;
- no new verification script is introduced;
- historical `scripts/*.bat` delivery/bootstrap shims are retained unless an explicit repository contract deprecates them; Docker-first `.bat` entrypoints remain part of the supported Windows workflow;
- generated Blade compiled-view cache files are not retained as source;
- repository ignore rules exclude local secrets, dependencies, generated frontend output, runtime caches and IDE/OS noise from source control.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `bootstrap/providers.php`
- `composer.json` and `composer.lock`
- `compose.dev.yml` and `compose.verify.yml`
- `docs/project/security/authorization-contract.json`
- `docs/project/governance/authority-dependencies.json`
- `docs/project/engineering/verification-topology.json`

### Installed versions

Stage 17.0 preserves the locked Laravel 13 / PHP 8.5 target, Node 22 toolchain, PostgreSQL 18.4 Docker authority, Redis 7.4, and the package versions recorded in `composer.lock` and `package-lock.json`.

### Official external sources

No new external package or API is introduced. Docker Official PostgreSQL 18 image layout, Laravel service providers, controller dependency injection, and class-based Blade components are the external primitives used by this reconciliation stage.

### Native capability assessment

Laravel service providers, controller dependency injection, class-based Blade components, route aliases, and framework test/container facilities cover the required composition and presentation cleanup.

### Custom implementation justification

Custom code is limited to SongChart-specific composition ownership, presentation data preparation, Docker setup orchestration, and repository verification contracts. No replacement DI, template, authorization, or container framework is introduced.

## Security and data impact

No schema/data mutation semantics or authorization grants change. Provider destination policy remains fail-closed and continues to own HTTPS/host/compliance checks.

## Tests and verification

Focused checks:
- PHP syntax for changed PHP files;
- route listing for canonical and compatibility design-system routes;
- provider chooser feature tests;
- UI preview/design lab feature tests;
- architecture/static analysis when target PHP extensions/version are available.

Candidate closure remains `composer stage:verify`; canonical closure remains `songchart verify` on the authoritative environment.

## Rollback

File-only rollback. No database or dependency rollback is required.
