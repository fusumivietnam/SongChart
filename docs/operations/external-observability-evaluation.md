# External Observability Evaluation

Status: Stage 25.0D evaluation authority.

## Decision

External APM remains **deferred**. Stage 24/25 repository-owned evidence currently covers the accepted operational baseline through Laravel Pulse, Horizon, runtime/database probes, provider health, the operational-intelligence scorecard, exact-head verification, and GitHub workflow evidence. No measured visibility gap currently justifies adding Sentry, Grafana Cloud, Datadog, New Relic, or another external APM as a production dependency.

This decision is evidence-gated rather than permanent. A future adoption proposal must identify a concrete visibility gap that cannot be answered reliably by the existing owners.

## Adoption triggers

An external APM evaluation may be reopened only when at least one accepted production signal demonstrates a missing capability such as:

- cross-request or cross-service trace correlation that current request/runtime evidence cannot provide;
- error aggregation or release-regression attribution that cannot be reconstructed from current application and workflow evidence;
- long-horizon production performance analysis whose retention requirement exceeds the accepted internal evidence window;
- multi-region or multi-service topology where existing bounded health and runtime evidence cannot localize failures;
- an operational incident whose root cause remains unresolved specifically because required telemetry was unavailable.

Traffic growth, vendor availability, or a desire for a richer dashboard alone is not an adoption trigger.

## Value test

Any proposal must state the unanswered operational question, the exact telemetry needed to answer it, the expected reduction in mean-time-to-detect or mean-time-to-recover, and why the repository-owned baseline cannot close that gap. The proposal must define a measurable success criterion before production enablement.

## Privacy and retention

External telemetry must minimize personal and content-sensitive data. Authentication secrets, session credentials, request bodies containing private/admin data, provider credentials, and unnecessary user identifiers must not be exported. Any candidate must document data residency, subprocessors, retention controls, deletion behavior, sampling, and access-control boundaries before adoption.

Retention must be purpose-limited. A longer vendor default is not authority to retain SongChart telemetry longer than the operational use case requires.

## Cost and cardinality

A candidate must provide an expected monthly cost envelope using measured event/span/error volume and explicit sampling/cardinality assumptions. High-cardinality labels, full-body capture, and unrestricted trace retention are prohibited defaults. Cost growth must be observable before the service can become an operational dependency.

## Exit and fallback

External APM is always an optimization/visibility layer, never the runtime or domain authority. Disabling or removing it must leave the accepted application topology operational. Direct DNS-to-Caddy, PostgreSQL primary authority, Redis runtime-state boundaries, Pulse/Horizon, repository verification, and the Stage 24/25 scorecard remain valid without the vendor.

Instrumentation introduced for a future vendor should prefer provider-neutral interfaces when practical. Vendor failure must not fail application requests, queue work, canonical writes, health checks, or deployment verification.

## Stage 25 conclusion

For Stage 25.0D the decision is `deferred_no_demonstrated_gap`. No external APM package, agent, sidecar, proxy, credential, network dependency, or automatic infrastructure mutation is authorized. Stage 25 closure therefore validates the existing evidence-gated scaling policies and supported fallback topology rather than introducing a speculative observability dependency.
