# Stage 11.3 Laravel Feature Alignment Audit

Product: `SongChart 0.1.0-dev`  
Role: point-in-time audit.  
Audit baseline: Laravel 13 and Laravel Fortify 1.x.

## Decision rule

Use Laravel-native infrastructure when Laravel owns the generic application concern. Keep custom code only where it expresses SongChart domain rules, provider compliance, extension package governance or an explicit replaceable boundary with a real null/default implementation.

Classifications:

- **Aligned** — current implementation uses the intended Laravel boundary.
- **Accepted custom** — custom implementation owns SongChart-specific policy rather than duplicate framework infrastructure.
- **Deferred** — framework capability is configured but not yet required by a shipped use case.
- **Remediation candidate** — current code works but should move toward the documented Laravel boundary when that area is next modified.

## Alignment matrix

| Capability | Current implementation | Classification | Decision / next action |
|---|---|---|---|
| Authentication | Fortify actions, routes and `FortifyServiceProvider`; `TwoFactorAuthenticatable` user boundary | Aligned | Continue using Fortify for login, password reset, verification, password confirmation and 2FA. Do not duplicate Fortify routes/controllers. |
| Authorization | Named Gate `access-admin` and `can:` route middleware | Aligned | Keep Gate for the current bounded admin role. Add Policies when resource-specific authorization appears. |
| HTTP validation | Extension writes use dedicated Form Requests; Fortify actions use Laravel Validator | Aligned | Fortify action validation is package-standard and should remain. |
| Public search validation | `SearchController` uses `$request->validate()` inline | Remediation candidate | Introduce a `SearchRequest` when search behavior is next changed. Do not create it solely to inflate Stage 11.3 scope. |
| Database transactions | Extension install/upgrade invariants use `DB::transaction()` | Aligned | Retain transactions around multi-write lifecycle operations. |
| Queues | Database/Redis queue configuration exists; no current product job requires dispatch | Deferred | Use Laravel Jobs/queue middleware for provider sync, indexing, notifications and other slow/retryable work when implemented. |
| Events/listeners | No current cross-cutting secondary-effect workflow | Deferred | Emit completed facts and queue slow listeners when real secondary effects appear. |
| Scheduler | Console route exists; no recurring product operation is currently shipped | Deferred | Define schedules through Laravel scheduling when provider refresh, cleanup or sitemap jobs are introduced. |
| Cache | Laravel cache stores are configured; no business cache is currently necessary | Deferred | Cache derived projections only; never make cache business truth. |
| Rate limiting | Fortify login and two-factor limiters use `RateLimiter` | Aligned | Add named HTTP/job limiters for search/provider-trigger endpoints when those endpoints become externally expensive. |
| Notifications/mail | Laravel mail configuration exists; Fortify uses framework notification flows | Aligned / deferred | Use Notifications for future security and operational messages; do not scatter direct mail transport code through controllers. |
| HTTP client | No outbound provider client is implemented in current source | Deferred | Provider adapters must use Laravel HTTP Client with timeout, retry and test fakes. Raw cURL is prohibited. |
| Filesystem | Laravel filesystem config exists; extension subsystem also performs guarded local package operations | Accepted custom | Keep extension extraction, snapshots and atomic package lifecycle custom because they enforce SongChart package rules. Use Laravel Storage for ordinary application files and remote disks. |
| Encryption / secrets | Laravel application encryption and Fortify encrypted 2FA attributes | Aligned | Never expose encrypted/secret attributes to views or logs. |
| Logging / exception reporting | Extension UI reports exceptions and returns stable messages | Aligned | Continue framework reporting; do not reveal raw exception text to users. |
| Contracts and null adapters | Analytics, human verification, search and provider contracts have concrete default/null implementations | Accepted custom | These are replaceable application/provider boundaries with concrete implementations, not generic service-layer duplication. |
| Extension managers | Installer, upgrade, rollback, signature, preflight and runtime registries | Accepted custom | They implement extension governance and security policy. Do not replace them with generic framework wrappers. |
| Role storage | Bounded `users.role` and `User::isAdmin()` | Accepted current scope | Keep until a real permission matrix appears; do not add a permission package pre-emptively. |

## Source findings

### Confirmed aligned

- Admin access is enforced with Laravel Gate middleware.
- Extension write boundaries use Form Requests.
- Fortify owns authentication and 2FA mechanics.
- Login and two-factor throttling use Laravel RateLimiter.
- Extension lifecycle invariants use Laravel database transactions.
- Exceptions are reported while browser messages remain stable.
- Configuration reads environment values inside `config/`, not application classes.

### Accepted custom boundaries

- Extension package extraction, signature verification, preflight, migration, snapshot and rollback logic.
- Provider destination compliance policy.
- Demo/search catalog contract and null operational adapters.

These components encode SongChart behavior or provide a tested substitution boundary; they are not replacements for Laravel authentication, queues, cache, validation or storage APIs.

### Remediation backlog

1. Move public search query validation to a Form Request when search is next modified.
2. Require Laravel HTTP Client for the first real provider adapter.
3. Require idempotent Laravel Jobs for provider refresh/import and other slow operations.
4. Add resource Policies when admin CRUD expands beyond the current dashboard/extension boundary.
5. Add named rate limiters for public search or provider-trigger actions when real abuse/cost thresholds exist.

## Prohibited duplicate infrastructure

The repository verifier rejects:

- raw cURL usage in `app/`;
- `env()` calls in application code outside `config/`;
- restoration of custom admin middleware;
- inline validation in the extension admin controller;
- direct `Mail::` calls inside HTTP controllers;
- custom queue worker loops under `app/`.

## Verification command

```bash
composer alignment:verify
```

This is a source-level guardrail. Dependency-backed behavior remains covered by Pest, Pint, Larastan and the frontend build through `composer verify`.
