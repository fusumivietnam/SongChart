# Stage 11.4.4 Task Contract — Enum Presentation and Command Regression Hotfix

## Scope

- Normalize `UserRole` presentation through the enum instead of converting enum objects in Blade.
- Correct the Symfony Console regression assertion so the forbidden global `--version` collision remains absent.
- Add regression coverage for stable role labels.

## Acceptance criteria

- `/account` renders for verified users without enum-to-string conversion errors.
- `extension:rollback` defines `--release` and does not define a custom `--version` option.
- Pint, Larastan and Pest pass on the dependency-backed working tree.
- No route, schema, provider, authentication flow or authorization rule changes.
