# Stage 11.4 Authentication and Authorization Model

## Authentication ownership

Laravel Fortify owns login, registration, password reset, email verification, password confirmation and two-factor authentication. SongChart only supplies actions, views, eligibility checks and redirects.

## Roles

`App\Enums\UserRole` is the canonical role vocabulary: `user`, `reviewer`, `editor`, `provider_manager`, `system_operator`, and `super_admin`.

## Gates

- `access-admin`: any active non-user operational role.
- `manage-extensions`: active `system_operator` or `super_admin` only.

The admin shell uses `auth`, `verified`, and `can:access-admin`. Extension read routes inherit that boundary. Extension mutation routes additionally require `can:manage-extensions`.

## View boundary

Blade may use `@can` to hide controls, but route middleware is the enforcement boundary. Hiding a control never grants or revokes permission by itself.

## Deferred

Database-backed role/permission tables remain deferred until permissions need runtime administration. Laravel Gates remain the final enforcement API even if storage changes later.
