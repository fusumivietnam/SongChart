# Stage 11.4.2 Task Contract — Larastan Cast Boundary Normalization

## Goal

Resolve the remaining PHPStan 2/Larastan findings caused by Eloquent cast certainty without weakening static analysis or changing runtime behavior.

## Acceptance criteria

- Extension manifest values are normalized from the Eloquent attribute boundary before nested access.
- Rollback registry writes receive `array<string, mixed>` data.
- Provider status uses the enum cast directly.
- No `ignoreErrors`, lower analysis level, schema change, route change or authorization change is introduced.
- Pint and Larastan pass on the dependency-backed development machine.

## Non-goals

- Changing extension lifecycle behavior.
- Changing stored manifest format.
- Adding migration or provider functionality.
