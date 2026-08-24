# Discovery Rule Schema

Rules are structured data and never arbitrary SQL, Eloquent column paths, scripts or regular expressions.

Schema version 1 supports boolean `and` / `or` grouping and the operators `eq`, `not_eq`, `in`, `not_in`, `gt`, `gte`, `lt`, `lte`, `exists`, and `not_exists`.

Every rule field must be resolved by `DiscoveryFieldRegistry`. A field definition declares its logical type, supported discoverable entity types, allowed operators, filterability, sortability and any required capability.

Stage 16.0 defined the schema contract. Stage 16.1 implements typed in-process predicate compilation and evaluation; SQL/query-builder compilation remains intentionally out of scope.

Deterministic sorting is mandatory. Evaluators must append a canonical-identity tie breaker when user-defined sorts do not produce a total order.
