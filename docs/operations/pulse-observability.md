# Laravel Pulse operational observability

Stage 16.4 adopts first-party Laravel Pulse for technical observability. `/admin` remains a business operations surface; `/pulse` is the engineering/operations surface and is authorized by the `viewPulse` Gate through the `User::viewPulse` Laravel Gate capability.

## Development

Pulse uses the primary PostgreSQL connection unless `PULSE_DB_CONNECTION` names a dedicated connection. Run migrations through the governed SongChart development/runtime workflow, then visit `/pulse` as an active `system_operator` or `super_admin`.

The default ingest driver is `storage`, which avoids a second Redis stream worker during development.

## Production profile

For high traffic, use a dedicated Pulse database connection and optionally Redis ingest. If `PULSE_INGEST_DRIVER=redis`, `PULSE_REDIS_CONNECTION` must point to a Redis connection separate from the Redis queue connection. Run `php artisan pulse:work` under the governed production process supervisor and `php artisan pulse:check` on each application server.

## SongChart thresholds

- slow PostgreSQL query: 500 ms
- slow request: 1000 ms
- slow job: 1000 ms
- slow outgoing Laravel HTTP request: 1000 ms
- retention: 7 days by default

These are observability thresholds, not performance SLOs. Tighten them only after production baselines exist.

## Boundary

Domain and Application code must not depend on `Laravel\Pulse` or custom Pulse recording. Stage 16.4 observes framework events only. Product metrics and analytics remain separate from operational telemetry.
