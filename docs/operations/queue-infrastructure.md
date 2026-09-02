# Queue Infrastructure — Production Runtime

## Runtime authority

SongChart uses Laravel queues with Redis as the asynchronous runtime baseline. Queue names are workload contracts, not feature-specific worker implementations.

| Queue | Workload | Expected profile |
|---|---|---|
| `critical` | privileged/critical work | lowest wait threshold |
| `discovery-projections` | bounded Discovery projection rebuilds | CPU/DB bounded, 120s job timeout |
| `provider-health` | provider health probes | short network-bound jobs |
| `provider-imports` | import page/finalization orchestration | network/DB orchestration |
| `provider-normalization` | normalization + canonical mutation | CPU/DB work |
| `notifications` | notifications | normal priority |
| `default` | uncategorized Laravel jobs | fallback only |

`REDIS_QUEUE_RETRY_AFTER` defaults to 180 seconds and must remain greater than the production worker timeout. The accepted production timeout default is 150 seconds; current jobs remain individually bounded and may use shorter timeouts/backoff.

## Production worker lifecycle

Production uses Laravel native `queue:work`; Horizon remains optional and is not required for the first release. The production process manager starts the same immutable application artifact with:

```bash
sh scripts/production/queue-worker.sh
```

The wrapper delegates directly to Laravel and contains no custom worker loop. Defaults are controlled by the production environment contract:

- queues: `critical,discovery-projections,provider-health,provider-imports,provider-normalization,notifications,default`;
- sleep: 1 second;
- tries: 3;
- timeout: 150 seconds;
- worker backoff: 5 seconds when a job does not provide its own backoff;
- max time: 3600 seconds;
- max jobs: 1000;
- memory: 256 MB.

`--max-time` and `--max-jobs` intentionally recycle long-running workers. The host/container process manager must restart a clean worker when the Laravel worker exits. A non-zero unexpected exit is a process failure and must not be treated as healthy.

Before a code deployment replaces the running artifact, issue Laravel's `queue:restart` against the shared Redis/cache control plane and then let the process manager start workers from the new accepted artifact. SIGTERM must be allowed to reach the PHP worker so Laravel can finish the current job and exit gracefully where possible.

## Production scheduler lifecycle

Production runs one independently managed scheduler process from the same immutable application artifact:

```bash
sh scripts/production/scheduler.sh
```

The wrapper delegates directly to `php artisan schedule:work`. The process manager owns restart on exit and must not run multiple scheduler lifecycle units for the same deployment. Scheduled commands that can mutate shared state retain `onOneServer()` and `withoutOverlapping()` Redis-backed locks as an additional safety boundary.

## Health and failure evidence

The scheduler runs Laravel `queue:monitor` every minute across all governed queues. `SONGCHART_QUEUE_MONITOR_MAX` controls the queued-job threshold and defaults to 100. Queue pressure produces Laravel `QueueBusy` evidence; Stage 19.0.4 owns alert routing, operator ownership and escalation policy.

Failed jobs remain persisted through Laravel's `failed_jobs` authority and are inspected/retried through standard Laravel queue commands. Process liveness is owned by the deployment process manager; application-level queue pressure is owned by `queue:monitor`. Unknown/missing process state must not be presented as healthy.

Scheduler boot/readiness can be checked deterministically with `php artisan schedule:list`. Redis/PostgreSQL dependency readiness remains part of the production runtime/environment authority rather than being reimplemented by worker wrappers.

## Optional Horizon profile

`config/horizon.php` remains a compatibility/profile surface for Linux deployments that intentionally install Horizon later. Horizon is not installed as a mandatory Composer dependency and is not the first-release worker supervisor. If installed, SongChart keeps the existing `viewHorizon` authorization boundary and schedules Horizon snapshots conditionally.

## Boundaries

- Do not build a queue dashboard inside the business Admin Dashboard.
- Do not introduce custom worker loops.
- Do not make Horizon a first-release runtime dependency without a separate accepted change.
- Queue jobs should declare bounded retries/timeouts/backoff and useful tags where operationally meaningful.
- Worker/scheduler wrappers must not contain secret values or deployment-vendor assumptions.
- Queue/scheduler lifecycle is independent from the web process; restarting one must not require restarting all application processes.
