# Stage 16.8.1 — Admin Operations UX & Information Architecture

## Authority and official sources

### Repository authorities

`docs/ui/admin/SONGCHART_ADMIN_DASHBOARD_DESIGN_CONTRACT.md`, the Stage 16.8 provider mutation contract, executable use-case contracts, authorization gates, and existing admin read models remain authoritative. This stage changes administrator-facing information architecture and presentation; it does not introduce a parallel mutation path.

### Installed versions

Laravel 13 / PHP 8.3 repository baseline.

### Official external sources

- Laravel 13 authorization documentation: Blade / application authorization should use the framework authorization layer rather than presentation-only security.
- Laravel 13 authentication documentation: sensitive actions may retain `password.confirm` middleware protection.
- Laravel 13 validation documentation: mutation forms continue to rely on server-side validation.

### Native capability assessment

Blade, authorization gates, named routes, validation, password-confirmation middleware, and existing read/mutation services are sufficient. No new UI framework or admin package is required.

### Custom implementation justification

SongChart needs domain-specific operational vocabulary and task-oriented administrator presentation. A small centralized presentation service is justified to prevent raw provider/import enum terminology from leaking inconsistently across Blade views.

## Scope

- Reframe the admin dashboard as an attention center.
- Rename primary navigation around administrator tasks: Content, Data Sources, Data Jobs, Review work, Users & Access, System Health.
- Make navigation role-aware using existing authorization gates.
- Present provider/import states and recovery actions in human-readable language.
- Keep generated idempotency keys hidden from administrators.
- Move checkpoints, HTTP request ledgers, sync internals, and similar diagnostics behind explicit technical-detail disclosure.
- Preserve routes, mutation semantics, audit evidence, authorization, password confirmation, idempotency, concurrency guards, and database schema.

## Non-goals

- No provider mutation behavior changes.
- No new provider/import tables or migrations.
- No replacement admin framework.
- No observability implementation; Stage 16.9 owns operational telemetry.
- No attempt to hide technical data from authorized operators; it becomes secondary, not deleted.

## Expected files

- `app/Support/Admin/AdminOperationsPresentation.php`
- `app/Support/Admin/AdminDashboardSnapshot.php`
- `app/Support/Admin/ProviderOperationsConsole.php`
- `app/Support/Admin/IdentityConflictReviewConsole.php`
- `resources/views/components/admin/sidebar.blade.php`
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/operations/**`
- `resources/views/admin/identity-conflicts/index.blade.php`
- `tests/Feature/AdminOperationsUxTest.php`
- `tests/Feature/SharedShellsTest.php`
- `scripts/verify-admin-operations-ux.php`
- `docs/project/stack/impact-test-map.json`
- `composer.json`

## Allowed incidental files

- `README.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/DEVELOPMENT_HISTORY.md`

## Tests and verification

Run the admin operations UX verifier, documentation/repository governance, impact-map verification, PHP syntax, Pint, Larastan, isolated SQLite/PostgreSQL tests, and full release verification. Package-time validation must not claim target-only gates passed when dependencies or PostgreSQL runtime are unavailable.
