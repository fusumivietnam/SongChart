# Product Roadmap

Status: future-looking product roadmap. This file does not declare repository current-stage state. `README.md` owns the current-stage pointer; `docs/project/DEVELOPMENT_HISTORY.md` owns delivered chronology.

## Foundation closure

Foundation work through provider mutation/recovery controls and Stage 17.0 reconciliation is delivered repository history, not future roadmap scope. New foundation verifiers or infrastructure require a concrete product/release risk rather than stage-by-stage expansion.

## Provider integration sequence

Stage 17.1 is delivered: MusicBrainz is the first live provider contract, limited to Artist lookup/search and disabled by default until policy/contact configuration is explicit.

### 17.2 — Artist import vertical slice

Implementation candidate: Provider request → immutable raw ledger → normalization → validation/quarantine → exact identity resolution → canonical mutation → admin inspection → frontend discovery. Closure evidence is owned by the current-stage records, not this roadmap.

### 17.3 — Provider import recovery and operational hardening

Delivered implementation: explicit terminal/transient provider request semantics, bounded Laravel queue retry, persisted retry context, recovery visibility, and operator controls on the proven Artist slice. Closure evidence is owned by current-stage records.

### 17.3.1 — Canonical Artist admin editing and development-status corrective

Delivered corrective: governed canonical Artist editing in Admin plus accurate pipeline health/error classification discovered during live MusicBrainz testing.

### 17.3.2 — Admin provider import workbench and public catalog indexes

Delivered corrective/product-surface bridge: MusicBrainz Artist search/import is available from Admin → Providers, while `/artists`, `/releases`, and `/collections` resolve to canonical public indexes with public-only Collection visibility and honest Release empty state.

### 17.3.3 — Provider rate policy and global request gate

Delivered implementation: provider-neutral operation-aware rate policy, Redis-backed global request gate, MusicBrainz minimum-interval/cooldown enforcement, and Admin operational visibility. MusicBrainz is the first proof; future providers declare their own policy semantics.

### 17.4 — MusicBrainz Release Group + Release vertical slice

Delivered implementation: expands the proven Artist pipeline to release groups/releases while preserving distinct MusicBrainz semantics. Preserve MusicBrainz release-group vs release semantics, edition/country/date/label/barcode/media data, Cover Art Archive references, admin import inspection, `/releases`, and `/release/{slug}`. Keep requests paginated/bounded and governed by the shared provider gate.

### 17.5 — MusicBrainz Recording + ISRC vertical slice

Delivered implementation: add recordings, artist credits, durations, ISRCs and release appearances. This stage creates the canonical recording identity needed before attaching playable media destinations. Establish focused PostgreSQL query/queue evidence while importing representative data instead of pausing product work for a separate broad performance-only stage.

### 17.6.1 — Admin Catalog Data Boundary Corrective

Corrective only: preserve Stage 17.6 behavior while restoring the Stage 17.0 controller/read-model/write-service boundary for Admin canonical Artist edits.

### 17.6 — Essential MusicBrainz relationships

Current implementation candidate: complete the minimum pre-YouTube graph with Artist↔Artist group membership, Recording→Work, Artist Credit credited-name/join-phrase evidence, aliases, selected URL relationships, direct Work import and `/works`. Defer long-tail entities/events/places/series/instruments until product demand exists.

### 17.7 — YouTube provider foundation + video destination

Delivered candidate: separate YouTube destination-discovery boundary with server-side API key, operation-aware quota guard, `search.list` candidate discovery, `videos.list` verification, deterministic metadata scoring, mandatory admin approval, provider-neutral Recording destinations, and verification Compose project isolation. IFrame playback remains 17.8 scope; SongChart does not store/rehost media.

### 17.8 — Canonical Data Fusion Foundation + Public URL Canonicalization

Delivered candidate: reuse SongChart provenance assertions/conflicts as the provider-neutral evidence fabric, establish deterministic field-level authority/confidence/freshness resolution, expose Entity Passport data-quality state, and standardize public detail URLs on plural type-specific routes without redirects while the catalog is still local/unindexed.

### 17.9 — Identity Bridge & Enrichment Planning

Future: cross-provider identifier bridge, coverage-driven missing-data planning, enrichment recipes, quota/cost awareness and freshness policies.

### 18.0 — YouTube media experience

Future: verified YouTube iframe playback, outbound fallback, destination freshness jobs, quota observability and recording/artist media presentation.

## Frontend discovery sequence

### 18.x

- PostgreSQL-first production search and ranking;
- canonical artist/release/recording/work pages;
- provider destination chooser with explicit availability/freshness;
- canonical URLs, structured metadata, and SEO closure.

## User value sequence

### 19.x

- follows/saves/private collections;
- rule-based discovery from explicit user actions;
- provider account connections only where official APIs and policy permit.

## Production sequence

### 20.x

- deployment/queue/scheduler baseline;
- observability and provider operational metrics;
- security/header/backup closure;
- SEO/sitemap/structured-data production validation.

## Roadmap rules

- Delivered work belongs in `docs/project/DEVELOPMENT_HISTORY.md`, not here.
- Every implementation stage starts from an accepted use case and executable contract.
- New provider fields/states must be declared in contract authorities before migrations or application code consume them.
- A feature is not release-closed until required PostgreSQL/quality/release evidence exists; SQLite compatibility is non-authoritative.

- PostgreSQL 18 Docker persistence uses the 18+ parent mount `/var/lib/postgresql`; the legacy `/var/lib/postgresql/data` mount is forbidden for the dev stack.
