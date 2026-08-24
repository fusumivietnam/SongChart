# Uptime and Status Rules

## Candidates

- Better Stack.
- UptimeRobot.
- Uptime Kuma for self-hosted monitoring.
- Atlassian Statuspage or Instatus for a dedicated public status page.

## Monitor

- public homepage;
- search smoke test;
- login page;
- provider redirect endpoint;
- queue heartbeat;
- scheduled-job heartbeat;
- email provider health;
- database backup freshness;
- external provider health separately.

## Health endpoints

Separate:
- liveness;
- readiness;
- dependency status;
- private diagnostics.

A public health endpoint must not disclose versions, credentials, hostnames or database details.

## Alerts

Every alert needs:
- owner;
- severity;
- user impact;
- runbook;
- deduplication;
- escalation;
- recovery notification.
