# Identity Conflict Review

Stage 15.2 introduces an auditable, provider-neutral review boundary for deterministic identity conflicts.

A review snapshots the provider entity, canonical candidate IDs and resolver evidence. Decisions are append-only and record before/after snapshots. Supported actions are approve match, reject candidate, keep separate, defer merge and reopen.

Approved matches supersede the remaining candidates. Keep-separate rejects unresolved candidates. Merge execution is intentionally deferred; Stage 15.2 records intent but does not merge canonical entities.
