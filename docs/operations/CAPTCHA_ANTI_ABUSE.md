# CAPTCHA and Anti-Abuse Rules

## Preferred provider

Cloudflare Turnstile.

## Use only where risk exists

- registration after suspicious behavior;
- password-reset request abuse;
- login after repeated failure;
- contact/report forms;
- public contribution forms;
- expensive search/import actions.

Do not show a CAPTCHA to every visitor by default.

## Required controls

CAPTCHA complements:
- rate limiting;
- IP/account/device risk signals;
- honeypots;
- request velocity;
- email verification;
- moderation;
- abuse audit logs.

## Turnstile implementation

- Render the official widget.
- Validate every token server-side.
- Treat tokens as short-lived and single-use.
- Verify expected hostname/action where supported.
- Do not log the token.
- Provide accessible retry/error text.
- Use official test keys in automated tests.
- Configure Content Security Policy.
