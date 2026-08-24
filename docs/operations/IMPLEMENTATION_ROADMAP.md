# Operational Integration Roadmap

## Phase 0 — contracts and governance

Create:
- Analytics contract and event dictionary.
- Mail transport contract.
- Human-verification contract.
- OTP verification contract.
- Error-reporting interface.
- Health-check registry.
- Operational provider registry.
- Consent categories.
- Data retention policy.
- Secret rotation process.
- Environment capability matrix.

## Phase 1 — development baseline

- Laravel Fortify authentication.
- TOTP 2FA for admins.
- Mailpit locally.
- Laravel Telescope locally only.
- Structured logs.
- Health endpoint protected from leaking internals.

## Phase 2 — first production

- Resend transactional email.
- Cloudflare Turnstile on risk-triggered forms.
- Sentry error and performance monitoring.
- PostHog product analytics with a small event dictionary.
- Laravel Pulse for internal operational views.
- External uptime monitor.
- Automated database backups and restore test.

## Phase 3 — growth

- Feature flags and experiments.
- Session replay with masking and sampling.
- Email delivery webhooks.
- Status page.
- Support ticketing.
- Data warehouse/export only after reporting needs justify it.

## Phase 4 — high-risk authentication

- Phone verification.
- SMS/WhatsApp OTP.
- Passkeys.
- Risk-based authentication.
- Managed identity provider only if enterprise requirements emerge.
