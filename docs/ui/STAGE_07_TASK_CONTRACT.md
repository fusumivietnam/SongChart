# Stage 07 Task Contract

- Goal: governed entity detail system for all canonical entity types.
- Non-goals: provider API calls, playback, database repository, edit/history UI.
- Acceptance: stable identity URL, explicit type/verification, facts, relationships, identifiers/missing state, provider disclosure, structured provenance, UI preview and Feature tests.
- Affected module: public UI and `SearchCatalog` demo adapter.
- Data/provider/policy impact: fixture-only relationship and identifier data; no provider API calls; no media storage.
- Tests: `EntityDetailSystemTest`, existing Search Flow regression suite.
