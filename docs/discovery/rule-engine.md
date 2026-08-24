# Discovery Rule Engine — Stage 16.1

Stage 16.1 implements the executable rule engine defined by the Stage 16.0 contracts. It remains provider-neutral and evaluates typed canonical snapshots only.

## Execution boundary

`DiscoveryRuleSet -> DiscoveryRuleEngine -> CompiledDiscoveryRule -> DiscoveryEntitySnapshot`

The compiler produces an in-process predicate. It does **not** generate SQL, accept SQL fragments, inspect provider payloads, mutate canonical entities, or execute on the public request path.

## Canonical field registry

The first registry implementation exposes only fields that exist on SongChart canonical entities today. Stage 17 chart-intelligence metrics are intentionally absent until their read models and metric contracts exist.

- Artist: `id`, `slug`, `verification_state`, timestamps, `name`, `sort_name`, `artist_type`, `country_code`.
- Recording: `id`, `slug`, `verification_state`, timestamps, `title`, `duration_ms`, `is_explicit`.
- Release: `id`, `slug`, `verification_state`, timestamps, `title`, `release_type`, `released_on`, `country_code`.
- Collection: `id`, `slug`, `verification_state`, timestamps, `title`, `visibility`.

Each field declares its data type, allowed operators, filterability and sortability. Unknown fields fail closed.

## Operator semantics

`eq`, `not_eq`, `in`, `not_in`, `exists`, and `not_exists` are available only where the registry allows them. Ordered comparisons (`gt`, `gte`, `lt`, `lte`) are limited to ordered field types declared by the registry. `in/not_in` accept 1–100 typed values. `exists/not_exists` require a null rule value.

## Sorting

Configured sorts are validated against the same registry and are limited to three. Execution appends `id ASC` as an implicit deterministic tie-breaker when `id` is not explicitly present.

## Cross-entity mapping

`CanonicalDiscoveryEntityMapper` maps supported Eloquent catalog entities to `DiscoveryEntitySnapshot`. The rule engine never receives Eloquent models directly.

## Deferred work

Stage 16.1 does not implement database query push-down, asynchronous projections, channel materialization, public APIs, admin UI, personalization, velocity, momentum, or other Stage 17 metrics.
