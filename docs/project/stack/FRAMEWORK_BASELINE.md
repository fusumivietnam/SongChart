# Framework Baseline

## Required application boundaries

Route -> Controller or Livewire -> Form Request and Policy -> Action -> Domain rules -> Eloquent or approved service -> Event or Job -> Response.

## Capability baseline

- Authentication: Laravel Fortify.
- Authorization: Laravel Gates and Policies.
- Validation: Form Requests.
- Application orchestration: Actions.
- Persistence: Eloquent and query builder.
- Transactions: Laravel database transactions.
- Async work: Laravel Jobs and Queue.
- Scheduling: Laravel Scheduler.
- Events: Laravel Events and Listeners.
- HTTP integrations: Laravel HTTP client.
- Cache: Laravel Cache; derived data only.
- Filesystem: Laravel Filesystem.
- Logging: Laravel logging channels.
- Encryption: Laravel encryption facilities.
- Rate limiting: Laravel RateLimiter and route middleware.

## Forbidden parallel infrastructure

Do not implement custom authentication, a custom queue runtime, direct cURL provider clients, controller-inline complex validation, provider orchestration in Eloquent models, a second catalog identity system or repository classes by default.

A new abstraction requires an explicit owner, at least two real implementations or a documented boundary need, tests, an exit strategy and an ADR when it changes architecture.
