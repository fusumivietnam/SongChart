# Stage 13.3 Task Contract

## Goal

Provide queue-based import orchestration with state transitions, cursor checkpoints, cooperative cancellation, retry/backoff and auditable terminal failure recovery.

## Required outputs

- orchestrator service;
- page fetch, payload processing and finalization jobs;
- run state transition authority;
- resume from committed checkpoint;
- cancellation intent and heartbeat fields;
- SQLite and PostgreSQL-compatible tests;
- executable verifier and documentation.

## Exclusions

No provider-specific live adapter, canonical mutation, fuzzy matching, admin monitor or retention scheduler.
