# Operational Provider Landscape

| Category | Preferred | Alternatives | Initial decision |
|---|---|---|---|
| Product analytics | PostHog | Mixpanel, Amplitude | PostHog |
| Privacy web analytics | PostHog Web Analytics | Plausible, Matomo, GA4 | Keep one analytics pipeline initially |
| Error monitoring/APM | Sentry | Bugsnag, Rollbar, Datadog | Sentry |
| Laravel visibility | Pulse | — | Enable production-safe views |
| Local debug | Telescope | Debugbar | Local/staging only |
| CAPTCHA/anti-bot | Cloudflare Turnstile | hCaptcha, reCAPTCHA | Turnstile |
| Transactional email | Resend | Postmark, SES, Mailgun, SendGrid | Resend adapter |
| Local email testing | Mailpit | MailHog | Mailpit |
| Auth backend | Laravel Fortify | Auth0, Clerk, Keycloak | Fortify |
| TOTP 2FA | Fortify | Auth0/Keycloak managed MFA | Fortify |
| Phone/email OTP | Twilio Verify | Vonage Verify, AWS End User Messaging | Deferred |
| Social login | Laravel Socialite | Managed identity provider | Google/Apple only when needed |
| Uptime monitoring | Better Stack | UptimeRobot, Uptime Kuma | Select before production |
| Status page | Better Stack | Statuspage, Instatus | Deferred until production |
| Logs | Sentry + structured logs | Better Stack, Datadog, Grafana Loki | Start minimal |
| Metrics/traces | Sentry + Pulse | Grafana Cloud, Datadog, New Relic | Start minimal |
| Feature flags | PostHog | Laravel Pennant, Unleash | PostHog for experiments; Pennant for local release gates |
| Support/helpdesk | Email initially | Zendesk, Freshdesk, Intercom | Deferred |
| Push notifications | None initially | Firebase Cloud Messaging, OneSignal | Deferred |
| Object storage/CDN | S3-compatible | Cloudflare R2, AWS S3, Backblaze B2 | Infrastructure decision |
| Backups | Provider-native + offsite | managed backup services | Required before production |
