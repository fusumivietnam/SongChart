# Raw Ingestion Ledger Authority

Stage 13.2 introduces append-oriented audit persistence between provider transport and normalization.

## Tables

- `provider_import_runs`: one governed import execution and its configuration hash.
- `provider_import_requests`: redacted request/response metadata and latency.
- `provider_import_payloads`: immutable raw provider payloads identified by SHA-256.
- `provider_import_items`: per-provider-entity processing state.
- `provider_import_failures`: structured retryable or terminal failures.
- `provider_import_checkpoints`: resumable cursors and committed state.

## Rules

1. Secrets, cookies and authorization values must be redacted before persistence.
2. Raw payload rows are immutable. Corrections create a new payload with a new hash.
3. Adapters do not mutate canonical catalog models.
4. Payload identity is scoped to run, provider entity type, provider entity ID and payload hash.
5. Constraint tests must use nested transactions so PostgreSQL can roll back to a savepoint.
6. Deleting a run is an explicit retention operation; individual payload deletion is forbidden.
7. Stage 13.2 stores evidence only. Normalization, matching and canonical mutation remain later stages.
