# Stage 11.6.1 Task Contract — Provider Command Type Normalization

## Goal

Resolve the remaining Larastan finding in the provider health dispatch command without changing command behavior, queue semantics or provider policy.

## Acceptance criteria

- `providers:health-check --provider=*` continues to accept zero or more provider slugs.
- The command relies on Symfony Console's documented array return type for array options.
- Empty and null option entries are filtered before dispatch.
- Larastan no longer reports an always-true `is_array()` check.
- No schema, route, queue, scheduler, provider adapter or compliance behavior changes.

## Data impact

None.

## Operational impact

None. Existing queue workers and scheduler configuration remain unchanged.
