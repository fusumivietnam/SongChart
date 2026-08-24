# Stage 09 Task Contract — Authentication and Account Shell

## Goal

Deliver the complete public authentication entry points and one governed account shell using Laravel Fortify.

## Non-goals

- Social login/provider token synchronization.
- Passkeys.
- Phone/SMS OTP.
- Account deletion/export workflow.
- Cross-device session revocation UI.

## Acceptance criteria

- Login, registration, password reset, email verification, password confirmation and 2FA challenge views exist.
- Fortify actions create/update users safely.
- Public registration always assigns role `user` server-side.
- Inactive accounts cannot authenticate.
- Normal users land at `/account`, never `/admin` by default.
- Account routes require authentication and verified email.
- Profile, password, 2FA and logout surfaces use the shared account shell.
- Tests cover authorization boundaries and stable semantic markers.

## Security impact

Authentication changes are security-sensitive. Update `AUTH_2FA_OTP.md`, this task contract and tests whenever login eligibility, redirects, roles, verification or 2FA behavior changes.
