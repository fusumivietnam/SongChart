# Framework Baseline

Status: current implementation strategy behind stable SongChart capability boundaries. Framework/package versions are lifecycle-managed targets, not permanent domain architecture.

## Required application boundaries

Route -> Controller or Livewire -> Form Request and Policy -> Application use-case boundary -> Domain rules -> Eloquent or approved service -> Event or Job -> Response.

`Action` is the current naming/convention for application orchestration; the architectural invariant is the use-case boundary, not a class suffix or folder name.

## Capability baseline

- Authentication: capability boundary implemented by Laravel Fortify.
- Authorization: capability boundary implemented by Laravel Gates and Policies.
- Validation: Form Requests.
- Application orchestration: Actions/use-case services.
- Persistence: Eloquent and query builder inside approved data boundaries.
- Transactions: Laravel database transactions.
- Async work: Laravel Jobs and Queue.
- Scheduling: Laravel Scheduler.
- Events: Laravel Events and Listeners.
- HTTP integrations: Laravel HTTP client behind provider/integration adapters.
- Cache: Laravel Cache; derived data only.
- Filesystem: Laravel Filesystem.
- Logging: Laravel logging channels.
- Encryption: Laravel encryption facilities.
- Rate limiting: Laravel RateLimiter and route middleware.
- Server-rendered presentation: Blade by default for public SEO-capable surfaces.
- Bounded interaction: Livewire/Alpine may implement interaction but do not own domain/application semantics.

## Framework-light domain rule

Domain meaning must not depend on HTTP requests, Blade/Livewire components, Auth/Cache facades, arbitrary Eloquent queries or framework presentation conventions. Framework-native infrastructure is preferred in application/infrastructure layers, while domain rules, enums and value semantics remain portable enough to survive framework major upgrades.

## Forbidden parallel infrastructure

Do not implement custom authentication, a custom queue runtime, direct cURL provider clients, controller-inline complex validation, provider orchestration in Eloquent models, a second catalog identity system or repository classes by default.

A new abstraction requires an explicit owner, tests and an exit strategy. It requires either multiple real implementations or a documented volatility/boundary need. Architecture-changing abstractions require an ADR. Do not manufacture a fake second implementation merely to justify an interface.

## Upgrade policy

Framework/runtime major upgrades are governed lifecycle operations. Evaluate compatibility across PHP/runtime extensions, Fortify, Horizon, Pulse, Livewire, activity logging, static analysis, testing, queue serialization, migrations, browser behavior and frontend build. Upgrade before the current supported line becomes a security liability, but never upgrade solely because a newer major exists.

The approved current versions are resolved from Composer/npm lock and stack/runtime authorities. Verification must compare execution surfaces with those current targets instead of embedding historical Stage version literals.

## Replacement boundary

SongChart locks capability semantics, not vendor implementations. A future framework/runtime replacement must preserve active capability, compatibility, persistence, security and delivery contracts or migrate them explicitly under the system compatibility policy.
