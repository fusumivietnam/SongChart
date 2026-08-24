# Authentication, 2FA and OTP Rules

## Baseline

Use Laravel Fortify for:
- registration;
- login;
- password reset;
- email verification;
- TOTP 2FA;
- recovery codes.

## Role policy

- Super admin: 2FA required.
- System operator/provider manager: 2FA required.
- Editor/reviewer: 2FA strongly encouraged, enforce before sensitive actions if needed.
- Public user: optional 2FA.

## Preferred factor order

1. Passkeys when introduced.
2. TOTP authenticator application.
3. Recovery codes.
4. Email verification/link for low-risk account confirmation.
5. SMS/WhatsApp OTP only when phone possession is a product requirement.

SMS OTP must not replace strong admin 2FA.

## OTP provider candidates

- Twilio Verify: managed SMS, WhatsApp, voice, email, TOTP and additional channels.
- Vonage Verify: alternative managed verification.
- AWS End User Messaging/SNS: lower-level messaging, higher operational/regulatory burden.
- Auth0/Clerk/Keycloak: consider only when outsourcing identity becomes a deliberate strategy.

## OTP rules

- Store hashes or provider verification state, never plaintext codes.
- Short expiry.
- Single use.
- Attempt limits.
- Resend cooldown.
- Per-user, destination and IP limits.
- Generic responses preventing account enumeration.
- Audit success/failure without code content.
- Recovery flow requires equivalent assurance.

## Stage 09 implementation safeguards

- Laravel Fortify is the only authentication route owner.
- Public registration must assign `role=user` server-side and ignore any submitted privilege field.
- Inactive accounts (`is_active=false`) must not authenticate.
- The default post-authentication destination is `/account`, not `/admin`.
- A non-null `two_factor_secret` is only setup-in-progress; confirmed 2FA requires `two_factor_confirmed_at`.
- Account routes require verified email. Admin routes additionally require the admin authorization middleware.


## Route ownership and model hydration safeguards

- Fortify owns the email verification notice route. Resolve it by the route name `verification.notice`; never duplicate the endpoint or hard-code an assumed URI.
- Nullable 2FA columns may be absent from a newly created or deliberately partial Eloquent model instance even though the database table contains those columns.
- Read optional 2FA state defensively. Missing `two_factor_secret` or `two_factor_confirmed_at` attributes mean setup is not confirmed.
- Do not disable `Model::preventAccessingMissingAttributes()` or equivalent protection to suppress this defect. Fix the helper or query contract instead.


## Presentation boundary

UI code must consume explicit 2FA state helpers and must not inspect encrypted database attributes directly. A partially selected `User` model can legitimately omit those attributes; omission means the UI must treat the state as not configured, not throw an exception.
