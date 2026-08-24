# Queue Infrastructure — Stage 16.3

## Runtime authority

SongChart uses Laravel queues with Redis as the asynchronous runtime baseline. Queue names are workload contracts, not feature-specific worker implementations.

| Queue | Workload | Expected profile |
|---|---|---|
| `critical` | future privileged/critical work | lowest wait threshold |
| `discovery-projections` | bounded Discovery projection rebuilds | CPU/DB bounded, 120s job timeout |
| `provider-health` | provider health probes | short network-bound jobs |
| `provider-imports` | import page/finalization orchestration | network/DB orchestration |
| `provider-normalization` | normalization + canonical mutation | CPU/DB work |
| `notifications` | future notifications | normal priority |
| `default` | uncategorized Laravel jobs | fallback only |

`REDIS_QUEUE_RETRY_AFTER` is 180 seconds, deliberately greater than the longest current job timeout (120 seconds) to avoid duplicate processing after worker termination.

## Windows / Laragon

Laravel's Redis queue works on Windows when Redis and a supported Redis PHP client are available. Horizon itself is not installed as a mandatory Composer dependency because Horizon 5.x requires `ext-pcntl` and `ext-posix`, which are unavailable on native Windows PHP. This repository therefore keeps Horizon integration conditional.

Use native queue workers on Laragon:

```bash
php artisan queue:work redis --queue=critical,discovery-projections,provider-health,provider-imports,provider-normalization,notifications,default --tries=3 --timeout=150
```

## Linux / WSL deployment profile

On Linux/WSL with `pcntl` and `posix` enabled:

```bash
composer require laravel/horizon:^5.47
php artisan horizon
```

The repository already contains `config/horizon.php`. Horizon's package service provider is auto-discovered when installed, while SongChart pre-defines the standard `viewHorizon` Gate in `AppServiceProvider`; access is limited to active `system_operator` or `super_admin` users.

Horizon metrics snapshots are scheduled every five minutes only when Horizon is installed.

## Deployment

Run `php artisan horizon:terminate` during Linux deployment so the process monitor restarts Horizon with new code. Keep a host-level process monitor around `php artisan horizon`; Horizon is the Laravel worker supervisor, not the OS daemon manager.

## Boundaries

- Do not build a queue dashboard inside the business Admin Dashboard.
- Do not introduce custom worker loops.
- Do not use `--ignore-platform-reqs` to force Horizon onto Windows.
- Queue jobs should declare bounded retries/timeouts/backoff and useful tags where operationally meaningful.
