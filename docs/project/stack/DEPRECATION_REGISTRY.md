# Deprecation Registry

| Component or pattern | Status | Replacement |
|---|---|---|
| Public role input | forbidden | Server-owned `UserRole` and authorization flows |
| Legacy role magic strings | deprecated | `App\Enums\UserRole` |
| Duplicate admin middleware | forbidden | `access-admin` Gate |
| Controller inline complex validation | deprecated | Form Requests |
| Direct provider orchestration in controllers/models | forbidden | Adapter + Action/Job boundaries |
| Direct cURL provider calls | forbidden | Laravel HTTP client inside integrations |
| Provider ID as canonical primary key | forbidden | Canonical ULID + provider mapping |
| Second catalog identity system | forbidden | Stage 12 canonical catalog domain |
| Custom queue/scheduler runtime | forbidden | Laravel Queue and Scheduler |
| New repository layer by default | restricted | Eloquent/query builder or approved service |
| `npm install` in CI/deployment | deprecated | `npm ci` with committed lockfile |

Historical exceptions must be documented with owner, removal stage and replacement. New code may not expand deprecated usage.
