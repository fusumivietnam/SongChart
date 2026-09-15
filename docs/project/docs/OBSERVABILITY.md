# Observability

## Logs

Structured fields:
- request_id
- user_id when appropriate
- module
- action
- provider
- external_request_id
- job_id
- duration_ms
- result/error_category

Never log tokens, authorization headers or full personal payloads.

## Metrics

- HTTP latency and error rate
- queue depth/failures
- database slow queries and bounded database probe latency
- cache hit rate
- provider latency/status/quota and health freshness
- import failures/quarantine backlog
- search zero-result rate and external search visibility when available
- merge/duplicate rate
- broken outbound link rate

## Operational intelligence

Stage 24 composes existing evidence rather than replacing its owners:

- Pulse owns application/runtime telemetry;
- Horizon owns Redis queue supervision;
- PostgreSQL owns database runtime behavior;
- provider sync/import/quarantine/identity records own data-pipeline evidence;
- `config/songchart.php` owns SongChart-specific scorecard thresholds;
- `./songchart artisan operations:intelligence --json` exposes the bounded read-only operator scorecard.

Missing telemetry is `unavailable`, never zero. Scaling decisions require the configured runtime, queue, database, cache and provider dimensions; incomplete evidence produces `insufficient_evidence`. See `docs/operations/operational-intelligence.md`.

External APM/Grafana-style adoption is deferred until a measured gap cannot be answered by current owners and the expected improvement plus data-export boundary are documented.

## Alerts

Production alerts must be actionable and linked to a runbook. Stage 24 defines thresholds and classifications but does not introduce automatic infrastructure mutation or remediation.
