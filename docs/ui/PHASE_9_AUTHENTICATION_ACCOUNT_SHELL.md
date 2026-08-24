# Phase 9 — Authentication and Account Shell

Status: implemented for `0.1.0-dev`.

## Authority

Read together with:

- `docs/operations/AUTH_2FA_OTP.md`
- `docs/operations/PRIVACY_CONSENT.md`
- `docs/project/docs/SECURITY.md`
- `docs/setup/FEATURE_TEST_ASSERTIONS.md`
- `docs/ui/SONGCHART_FRONTEND_DESIGN_CONTRACT.md`

## Architecture

Laravel Fortify owns authentication endpoints and security behavior. SongChart owns the Blade views, account shell and Fortify action implementations.

Required actions:

- `CreateNewUser`
- `UpdateUserProfileInformation`
- `UpdateUserPassword`
- `ResetUserPassword`

Do not create parallel custom login/register endpoints while Fortify is enabled.

## Redirect and authorization rules

- Fortify home is `/account`.
- Admin authorization is a separate server-side decision after authentication.
- Public registration always forces `role=user` and `is_active=true`; request payloads may not assign roles.
- `is_active=false` users must fail authentication with a generic response.
- `/account/*` requires `auth` and `verified`.
- `/admin/*` continues to require `auth`, `verified` and `admin`.

The previous `/admin` default redirect was a serious boundary defect because ordinary users were sent into a forbidden area after successful login. Do not restore it.

## Account shell

Routes:

- `/account`
- `/account/profile`
- `/account/security`

Shared components:

- `components/account/shell`
- `components/account/nav`

Stable markers:

- `data-account-section`
- `data-security-section`
- `data-two-factor-confirmed`

## 2FA rules

TOTP and recovery codes use Fortify. A configured secret is not equivalent to confirmed 2FA; UI and policy must use `two_factor_confirmed_at`.

- Super admin/operator enforcement remains a later sensitive-action middleware task.
- SMS must not replace strong admin TOTP.
- Recovery codes are shown only to the authenticated owner and must never enter logs or analytics.

## Testing rules

Tests must verify:

- least-privilege role assignment;
- inactive account rejection;
- `/account` redirect target;
- auth and email-verification boundaries;
- semantic account/security markers.

Avoid testing decorative classes or flattened sentences across nested markup.

## Controller foundation regression rule

All HTTP controllers that import `App\Http\Controllers\Controller` require the shared base file at `app/Http/Controllers/Controller.php` to exist and autoload successfully.

The missing base controller discovered after Stage 09 was a serious source-completeness defect: route registration succeeded, but account requests crashed only when Laravel attempted to load `AccountController`.

Required safeguards:

- do not add a controller extending `App\Http\Controllers\Controller` unless the base class exists;
- keep `ControllerFoundationTest` in the Feature suite;
- source packaging checks must verify framework foundation files, not only newly added feature files;
- do not work around a missing base class by removing inheritance from a single controller, because later controllers may require shared middleware or authorization helpers.


## Patch 02 — Fortify route ownership and safe model hydration

Two regressions were found after the account shell was exercised through the real Fortify middleware stack:

1. The verification notice route is owned by Fortify and currently resolves to `/email/verify`. Tests and application links must use the named route `verification.notice`; do not hard-code a guessed path such as `/verify-email`.
2. Newly created or partially selected Eloquent models may not contain nullable 2FA columns in their in-memory attribute array. Security helpers must treat missing optional attributes as an unconfigured state instead of triggering `MissingAttributeException` through direct property access.

Required safeguards:

- use `route('verification.notice')` and other Fortify route names in tests and views;
- do not create duplicate custom verification routes to satisfy a brittle test;
- 2FA helpers must read optional state through `getAttributes()` with explicit null fallbacks when only presence/non-presence is required;
- keep a regression test using a partially hydrated `User` model;
- do not disable Laravel missing-attribute protection globally to hide unsafe model access.


## Patch 03 — 2FA view boundary

Blade views must never read `two_factor_secret`, `two_factor_confirmed_at`, or recovery-code persistence attributes directly. These fields may be absent on partially hydrated models and are encrypted implementation details. Views must use state helpers on `User`: `hasTwoFactorAuthenticationConfigured()`, `hasPendingTwoFactorAuthentication()`, and `hasConfirmedTwoFactorAuthentication()`.

Direct persistence-field access in account views is a regression and must be covered by a source-boundary test.
