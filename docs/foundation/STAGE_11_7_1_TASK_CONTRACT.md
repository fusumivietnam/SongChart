# Stage 11.7.1 — Verification Chain Regression Hotfix

## Objective

Keep architecture tests aligned with the layered Composer verification chain introduced in Stage 11.7.

## Scope

- Update documentation-governance architecture coverage to assert `docs:verify` is owned by `quality:verify`.
- Update Laravel-alignment architecture coverage to assert `alignment:verify` is owned by `quality:verify`.
- Preserve `verify -> quality:verify -> individual guardrails` as the executable chain.

## Out of scope

- No runtime behavior change.
- No schema, route, provider, authentication or authorization change.
- No quality gate is removed or bypassed.

## Acceptance criteria

- Architecture tests no longer require every guardrail to be duplicated directly in `verify`.
- Tests prove `verify` includes `@quality:verify`.
- Tests prove `quality:verify` includes `@docs:verify` and `@alignment:verify`.
