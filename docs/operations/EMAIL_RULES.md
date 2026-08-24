# Email Provider Rules

## Preferred provider

Resend through Laravel Mail.

## Alternatives

- Postmark: transactional email focus.
- Amazon SES: cost-efficient at scale but more operational setup.
- Mailgun: API/SMTP and routing capabilities.
- SendGrid: broad ecosystem and marketing/transactional features.
- Mailpit: local testing only.

## Email classes

- Security: verification, password reset, login/security alerts.
- Transactional: account and workflow confirmations.
- Operational: admin/provider alerts.
- Editorial: review assignments.
- Marketing: separate consent and unsubscribe system; deferred.

## Required implementation

- Queue all non-blocking mail.
- Idempotency key for critical messages.
- Delivery, bounce, complaint and suppression webhooks.
- Signed webhook verification.
- SPF, DKIM and DMARC.
- Separate sending subdomain.
- Template versioning.
- Text and HTML parts.
- Accessible content.
- Localization.
- No secrets or sensitive payloads in email.

Provider-specific message IDs remain in delivery records, not domain entities.
