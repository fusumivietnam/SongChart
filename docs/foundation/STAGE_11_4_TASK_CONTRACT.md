# Stage 11.4 Task Contract — Authentication and Authorization Normalization

## Goal

Normalize authentication and authorization around Laravel Fortify, enum-backed roles, Gates and route middleware without introducing a parallel permission system.

## Acceptance criteria

- Fortify remains the owner of authentication, verification, password and 2FA flows.
- Public registration assigns `UserRole::User` server-side.
- Inactive users cannot authenticate or exercise admin abilities.
- `access-admin` remains the shared admin boundary.
- Extension read access and mutation access are separated.
- Extension writes require the `manage-extensions` Gate.
- Blade hides extension mutation controls from read-only admin roles.
- Tests cover inactive, normal, read-only admin and system-operator boundaries.

## Non-goals

- Database-driven roles and permissions.
- Social login, passkeys or external identity providers.
- User-management UI.
- Changing Fortify-owned routes.
