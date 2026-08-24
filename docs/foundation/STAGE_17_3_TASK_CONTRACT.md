# Stage 17.3 — Provider Import Recovery & Operational Hardening

Status: implementation candidate.

## Goal

Harden the proven MusicBrainz Artist import slice so provider-side throttling, temporary outages, transport failures, terminal misses, duplicate delivery, cancellation, resume, and operator recovery are represented explicitly instead of collapsing into generic job failures.

## Non-goals

- no Release/Recording/Work ingestion;
- no YouTube integration;
- no provider bulk crawler;
- no new persistence tables or migration;
- no replacement of Laravel queue retry primitives;
- no fuzzy identity matching.

## Acceptance criteria

- provider request failures carry explicit kind, retryability, HTTP status, and optional Retry-After delay;
- MusicBrainz 404 is terminal and does not enter automatic retry;
- MusicBrainz 503/rate-limit, 429 defensive handling, 5xx, transport, and request-slot failures are retryable;
- retryable request failures persist one failure record per attempt with cursor/attempt/retry-after context;
- import runs enter `retrying` with `resume_after` while delayed;
- exhausted retryable work becomes a terminal failed run through the existing queue failure callback;
- immutable raw payload and item idempotency remain unchanged;
- cancellation remains cooperative and audit history is retained;
- admin import detail exposes retry scheduling and HTTP/retry context;
- existing run-level retry/resume/cancel operations remain privileged, idempotent, audited, and password-confirmed.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/domain/operational-contracts.json`
- `app/Support/Providers/Ingestion/ProviderImportOrchestrator.php`
- `app/Jobs/Providers/Ingestion/FetchProviderImportPage.php`
- `app/Support/Providers/Operations/ProviderMutationService.php`

### Installed versions

No dependency changes. Runtime remains PHP 8.5, Laravel 13, PostgreSQL 18.4, Redis 7.4, and the existing Docker development/canonical verification profile.

### Official external sources

- MusicBrainz API rate limiting: https://musicbrainz.org/doc/MusicBrainz_API/Rate_Limiting
- Laravel 13 queues: https://laravel.com/docs/13.x/queues
- Laravel 13 queue API: https://api.laravel.com/docs/13.x/Illuminate/Queue/Jobs/RedisJob.html

MusicBrainz documents an average one-request-per-second limit and HTTP 503 responses when request rate is too high. Laravel provides native per-job attempts, backoff, release, failed callbacks, uniqueness, and overlap middleware; Stage 17.3 composes those primitives instead of adding a custom retry engine.

### Native capability assessment

Laravel queue jobs already expose `tries`, `backoff()`, `attempts()`, `release()`, `failed()`, `ShouldBeUnique`, and `WithoutOverlapping`. These are sufficient for bounded provider retry and delayed retry scheduling. Existing SongChart run statuses, failure ledger, checkpoints, operation audit, and recovery service already cover durable operational state.

### Custom implementation justification

Provider HTTP semantics cannot be represented by a generic `RuntimeException` because SongChart needs to distinguish terminal provider misses from transient failures and preserve provider status/retry metadata in the import ledger. A small provider-domain exception carries only normalized request-failure semantics; retry execution remains owned by Laravel queues.

## Security, authorization, and data impact

No new public mutation surface. Run recovery remains guarded by `manage-providers`, password confirmation, idempotency keys, business audit, and privileged audit. No secrets are persisted in failure context. Raw provider payload immutability remains unchanged.

## Tests and verification

- `tests/Feature/Providers/MusicBrainzProviderCatalogAdapterTest.php`;
- `tests/Feature/Providers/ProviderImportOrchestrationTest.php`;
- `tests/Feature/ProviderMutationRecoveryControlsTest.php`;
- provider import ledger/orchestration architecture verifiers;
- official-source, repository-state, documentation, taxonomy, repository-contract compiler gates;
- target live MusicBrainz smoke: one valid Artist MBID, one missing MBID, and queue/web observation;
- final target closure via `verify-songchart.bat`.

## Rollback

Restore Stage 17.2.3 application files. No schema or data rollback is required; Stage 17.3 adds no migration.
