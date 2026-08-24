# AI Operational Provider Rules

## Mandatory behavior

Before adding a provider, inspect:
- official API/SDK documentation;
- privacy/data-processing terms;
- pricing/quota model;
- supported regions;
- retention and deletion controls;
- webhook signing;
- incident/status history;
- current Laravel/PHP compatibility.

## Prohibitions

- Do not add two vendors for the same responsibility without an approved migration reason.
- Do not send secrets, passwords, access tokens or full provider payloads to analytics.
- Do not enable session replay without masking rules and consent review.
- Do not log OTP codes, recovery codes, reset tokens or CAPTCHA tokens.
- Do not place vendor SDK calls in domain code.
- Do not treat analytics delivery as transaction-critical.
- Do not block a core request because analytics is unavailable.
- Do not use CAPTCHA as the only abuse defense.
- Do not use SMS OTP as the default 2FA method.
- Do not enable debug tools publicly.
- Do not expose stack traces, SQL, environment values or queue internals to business admins.

## Fail-open / fail-closed

- Analytics: fail open.
- Error reporting: fail open.
- Email notification: queue and retry; business mutation remains committed when appropriate.
- CAPTCHA on protected action: fail closed with understandable retry path.
- Authentication/2FA: fail closed.
- Feature flag service: use safe local default.
- Uptime/status provider: no request-path dependency.

## SDK isolation

Wrap every provider behind an application contract. Configuration selects the provider.
Tests use fakes provided by SongChart, not live vendor calls.
