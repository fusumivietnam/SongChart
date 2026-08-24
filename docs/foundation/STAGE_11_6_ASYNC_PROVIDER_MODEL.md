# Stage 11.6 Async and Provider Infrastructure Model

## Ownership

Laravel owns queue transport, retries, failed-job handling, scheduling, command discovery and dependency injection. SongChart owns provider capability policy, adapter behavior, health semantics and sync-run audit records.

## Runtime flow

```text
Laravel Scheduler
  -> providers:health-check command
  -> DispatchProviderHealthChecks action
  -> provider_sync_runs(status=queued)
  -> CheckProviderHealth queued job
  -> ProviderAdapterRegistry
  -> ProviderAdapter::healthCheck()
  -> provider_sync_runs terminal status
```

Public HTTP requests do not enter this flow.

## Adapter registration

Provider integrations register concrete adapters with Laravel's service container and tag them as:

```php
$this->app->tag([
    MusicBrainzAdapter::class,
    CoverArtArchiveAdapter::class,
], 'songchart.provider-adapters');
```

`ProviderAdapterRegistry` rejects duplicate provider slugs. Domain/application code resolves adapters by canonical provider slug and never imports provider SDK classes.

## Queue contract

`CheckProviderHealth`:

- implements `ShouldQueue` and `ShouldBeUnique`;
- is unique per provider health-check operation;
- uses bounded retries and backoff;
- has a finite timeout;
- records `queued`, `running`, `retrying`, `succeeded`, `failed` or `skipped`;
- never stores raw provider responses;
- reports exceptions through Laravel exception reporting;
- does not modify provider compliance status.

A missing adapter or disabled provider is a deterministic `skipped` result, not a worker exception.

## Dispatch deduplication

The application action skips providers with a recent `queued`, `running` or `retrying` health run. The staleness window is configuration-driven. Operators may use `--force` for an explicit repeat.

## Scheduler

The default schedule is every fifteen minutes with `withoutOverlapping()` and `onOneServer()`. Production must run Laravel's scheduler and a queue worker for the configured queue.

## Configuration

```text
SONGCHART_PROVIDER_HEALTH_QUEUE=providers
SONGCHART_PROVIDER_HEALTH_SCHEDULE_ENABLED=true
SONGCHART_PROVIDER_HEALTH_STALE_AFTER_MINUTES=15
```

These settings control transport and cadence only. They do not enable a provider adapter or bypass capability/compliance policy.

## Future extension points

Future provider import jobs should follow the same rules:

- capability-gated;
- idempotent;
- retry-safe;
- auditable through sync runs;
- bounded by timeout/backoff;
- independent from public page rendering;
- fixture-tested before live credentials are introduced.
