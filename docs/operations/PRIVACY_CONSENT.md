# Privacy and Consent Rules

## Consent categories

- strictly necessary;
- preferences;
- analytics;
- session replay;
- marketing.

Do not classify product analytics or replay as strictly necessary without legal review.

## Data minimization

- Use internal opaque user IDs.
- Do not send email, phone or provider tokens to analytics by default.
- Mask sensitive fields in replay.
- Respect deletion/export requests.
- Document subprocessors.
- Configure regional processing where required.
- Define retention per event/tool.

Consent state must be available before optional scripts initialize.
