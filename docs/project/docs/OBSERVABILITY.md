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
- database slow queries
- cache hit rate
- provider latency/status/quota
- search zero-result rate
- merge/duplicate rate
- broken outbound link rate

## Alerts

Production alerts must be actionable and linked to a runbook.
