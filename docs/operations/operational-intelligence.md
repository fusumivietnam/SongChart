# Operational Intelligence

Stage 24 composes existing SongChart operational evidence; it does not introduce a parallel monitoring stack.

## Ownership

- Laravel Pulse owns application request/job/query/cache event collection and the `/pulse` engineering surface.
- Laravel Horizon owns Redis queue supervision and the `/horizon` queue-operations surface.
- PostgreSQL owns database runtime behavior; SongChart may run bounded read-only probes for operator diagnostics.
- Provider sync/import/quarantine/identity tables remain the source for data-pipeline operational evidence.
- `config/songchart.php` owns SongChart-specific scorecard vocabulary and thresholds.
- `App\Support\Operations\OperationalIntelligenceSnapshot` owns classification only; it does not collect or persist telemetry.
- `App\Application\Operations\Queries\OperationalIntelligenceReadModel` owns the bounded on-demand evidence read.

## Operator surface

Run:

```bash
./songchart artisan operations:intelligence --json
```

The command is read-only. It may probe PostgreSQL latency, current queue depth and recent Pulse slow-event evidence, then combines those observations with persisted provider/data-pipeline health. Missing evidence is emitted as `unavailable`; it is never converted to zero.

The Admin dashboard uses only already-loaded provider/data-pipeline evidence and therefore does not perform live runtime probes during an HTTP request. Full runtime evidence remains an explicit operator action.

## Scale decision semantics

The scorecard requires runtime, queue, database, cache and provider dimensions before it can state that the observed system is within baseline or recommend an investigation. If one or more required dimensions are unavailable, the decision is `insufficient_evidence`.

A critical metric produces `stabilize_before_scaling`: capacity expansion must not be used to hide a known failure. The scorecard never performs infrastructure mutation.

Search visibility is supplementary evidence and does not block the core runtime scorecard because it depends on external search telemetry that is not an application-correctness prerequisite.

## External observability decision

Stage 24 closes with external APM/Grafana-style adoption **deferred**. The accepted baseline already has Pulse, Horizon, provider health, repository diagnostics and exact-head CI evidence. No measured gap currently justifies another production dependency or telemetry export path.

Reopen the decision only when all of the following are recorded:

1. a concrete operational question cannot be answered by current owners;
2. a metric and threshold show the gap;
3. expected improvement is measurable;
4. privacy/data-export and credential boundaries are documented;
5. failure or unavailability of the external service does not affect SongChart correctness.

## Security

Operational outputs contain aggregate counts, statuses, timestamps and latency/queue observations only. They must not contain provider credentials, API keys, connection strings, request headers or raw exception payloads that may contain secrets.
