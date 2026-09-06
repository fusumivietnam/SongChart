# ADR-004: Documentation Governance

Status: Accepted

## Context

SongChart accumulated extensive project/module documentation plus many stage-specific task contracts, validation reports, baselines and change manifests. Keeping every historical stage file in the active tree makes AI orientation noisy and leaves obsolete instructions discoverable beside current authority. Simply moving the same files to another archive directory would preserve the duplication problem.

## Decision

Organize active documentation by semantic owner rather than chronology. Authored current-stage semantics live in `stage-plan.json`; generated current state is repository-derived; accepted chronology lives in `DEVELOPMENT_HISTORY.md`; durable architectural decisions live in ADRs; guarded failures live in the regression ledger; exact retired stage source remains recoverable from Git/PR history.

`docs/project/engineering/documentation-consolidation-contract.json` governs migration of legacy `STAGE_*` material. Before deletion, durable semantics and all active path consumers must be migrated. Standalone historical files remain only when a legal, compliance, release or external-reference requirement cannot be satisfied by structured owners plus Git history.

New documentation requires a distinct semantic owner, lifecycle or audience. Compatibility pointers may route to an owner but must not duplicate it.

## Consequences

Positive:

- AI and contributors start from a smaller current authority graph;
- stale stage instructions stop competing with active contracts;
- chronology remains readable without carrying full historical payloads;
- exact historical evidence remains available through Git/PR provenance;
- documentation cleanup becomes a bounded consumer-migration problem rather than subjective deletion.

Trade-offs:

- historical cleanup must be incremental because tests/verifiers/links may still depend on old paths;
- readers needing exact retired text may need Git history;
- retained historical exceptions require explicit justification.
