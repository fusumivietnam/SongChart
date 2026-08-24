# Provider Import Orchestration

Stage 13.3 turns the Stage 13.2 ledger into a resumable queue workflow without applying canonical catalog mutations.

## Pipeline

`ProviderImportOrchestrator::start()` creates an immutable run configuration and queues `FetchProviderImportPage`. Each fetched page stores payload evidence and processing items, commits the `page` checkpoint, queues `ProcessProviderImportPayload`, then queues the next cursor or finalization.

## State authority

Run transitions are owned by `ProviderImportRunStatus`. Terminal runs cannot resume. Cancellation is cooperative: `cancellation_requested_at` preserves history and is observed by page and item jobs.

## Recovery

- page jobs are unique per run and cursor;
- a per-run `WithoutOverlapping` lock prevents concurrent page mutation;
- retry backoff is explicit;
- exhausted jobs create ledger failures;
- resume starts from the last committed `page` checkpoint;
- raw payload uniqueness makes page replay idempotent.

## Boundary

Jobs may fetch, archive and normalize provider data. They must not write canonical artist, work, recording, release, version or collection models. Canonical mutation begins in Stage 14.
