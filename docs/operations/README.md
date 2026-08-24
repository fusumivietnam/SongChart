# SongChart Operational Provider Rules v1

Research snapshot: 2026-08-02.

This pack governs non-music providers used for analytics, security, authentication,
communications, debugging, observability and operations.

## Recommended starting stack

- Product analytics, funnels, experiments and feature flags: PostHog.
- Privacy-safe public traffic analytics: choose PostHog Web Analytics initially;
  add Plausible or Matomo only when a separate reporting need is proven.
- Application errors and performance: Sentry.
- Internal Laravel operational visibility: Laravel Pulse.
- Local debugging only: Laravel Telescope.
- Bot protection: Cloudflare Turnstile plus server-side rate limiting.
- Transactional email: Resend through Laravel Mail contracts.
- Local email: Mailpit.
- Authentication: Laravel Fortify.
- Two-factor authentication: TOTP and recovery codes through Laravel Fortify.
- SMS/WhatsApp OTP: Twilio Verify only for a justified phone-verification workflow.
- Uptime: Better Stack, UptimeRobot or self-hosted Uptime Kuma; choose one.
- Status page: Better Stack or Atlassian Statuspage after production launch.
- Logs: structured application logs; centralize only when operational volume requires it.
- Backups: infrastructure-level encrypted database/object-storage backups with restore tests.

## Core rule

Operational providers are replaceable infrastructure. Business logic must depend on
SongChart contracts, not vendor SDK classes.
