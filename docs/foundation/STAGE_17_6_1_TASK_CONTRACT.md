# Stage 17.6.1 — Admin Catalog Data Boundary Corrective

Status: implementation candidate.

## Goal

Restore the Stage 17.0 Application Data Boundary after canonical Artist editing introduced persistence into a registered read model and a direct model query into the Admin controller.

## Non-goals

- no MusicBrainz behavior changes;
- no schema migration;
- no route or authorization changes;
- no new package or runtime dependency.

## Acceptance criteria

- `CatalogController` performs no direct model query or persistence;
- `CatalogAdministration` is read-only;
- canonical Artist writes, transaction and privileged audit are owned by `CanonicalArtistAdministration`;
- existing catalog edit behavior and audit event remain unchanged;
- `verify-architecture-conformance.php` passes.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/domain/application-data-boundary.json`

### Installed versions

No package/runtime version changes.

### Official external sources

No new external API behavior is introduced by this corrective.

### Native capability assessment

Laravel container injection, Eloquent transactions and the existing privileged audit contract are sufficient.

### Custom implementation justification

A small application write service is required to preserve the repository-owned controller/read-model/write-side boundary.

## Tests and verification

- architecture conformance;
- existing catalog administration feature tests;
- repository-state/documentation/candidate/repository-contract verification;
- canonical Docker verification on target.

## Rollback

Restore Stage 17.6 files if necessary. No migration rollback is required.
