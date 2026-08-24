# Capability Ownership

| Capability | Canonical owner | Rule |
|---|---|---|
| Application framework | Laravel | No parallel framework layer. |
| Authentication and 2FA | Fortify | Do not create a second authentication implementation. |
| Authorization | Gates and Policies | Route/UI hiding never replaces server authorization. |
| Request validation | Form Requests | Avoid complex inline controller validation. |
| Application use cases | Actions | Controllers coordinate only. |
| Persistence | Eloquent/query builder | Repository classes are not the default. |
| Transactions | Laravel DB transactions | Wrap multi-write invariants. |
| Queue and retry | Laravel Queue/Jobs | Jobs must be idempotent and retry-safe. |
| Scheduling | Laravel Scheduler | Do not create a custom cron registry. |
| Provider transport | Laravel HTTP client | Provider adapters own request mapping; no direct cURL. |
| Provider health | Existing provider infrastructure | Extend the registry, jobs and sync runs. |
| Canonical catalog identity | SongChart catalog domain | Provider identifiers remain external mappings. |
| Metadata provenance | SongChart catalog provenance model | Never overwrite verified truth without policy. |
| Search | Existing `SearchCatalog` boundary | Do not query providers directly from public controllers. |
| Authentication UI | Fortify + approved Blade/Livewire shell | Preserve route names and state helpers. |
| Frontend build | Vite | No second bundler. |
| Styling | Tailwind + semantic tokens | No parallel design system. |
| Local UI state | Alpine.js | Use Livewire when server state is meaningful. |
| Testing | Pest/PHPUnit | Tests are grouped into Unit, Architecture and Feature suites. |
| Formatting | Pint | Project formatting is not editor-specific. |
| Static analysis | Larastan/PHPStan | Do not suppress errors without a documented reason. |

Before creating a package, service or abstraction, identify the row that owns the capability and extend that owner unless an ADR explicitly replaces it.
