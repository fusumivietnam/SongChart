# Stage 18.2 Task Contract — Public Search & Canonical Surfaces

Status: current-stage task contract.

## Goal

Turn the accepted canonical/provider foundation into a production-oriented public search and canonical browsing experience. Search must remain a read-only canonical concern, use PostgreSQL-backed deterministic ranking, preserve stable facets/pagination/canonical URLs, and never call provider APIs or mutate canonical data in the public request path.

## Non-goals

- New provider ingestion pipelines or provider breadth expansion.
- Provider API calls from public search/detail requests.
- Canonical mutation from search or browse actions.
- Personalized recommendations, follows, saved content or private collections.
- Production SEO rollout, sitemap publication or OpenGraph expansion; those remain Stage 18.3.
- Destination/media preference redesign beyond preserving the existing governed availability/freshness/provenance boundary.

## Acceptance criteria

- Production `SearchCatalog` resolves through the Eloquent/PostgreSQL read model outside the explicit local/testing demo profile.
- Relevance ranking is explicit and deterministic: exact title match before prefix match before substring match, followed by stable canonical tie-breaks.
- Search remains case-insensitive without raw user-controlled SQL identifiers.
- Entity facets report counts for the full query result set even when one entity type is selected for display.
- Type filtering scopes displayed results without changing query-wide `total_all` semantics.
- Pagination is deterministic and rejects out-of-range pages through the existing action/request boundary.
- Public search results link only to canonical SongChart URLs, never provider identities.
- Artist group/person taxonomy remains separated through canonical routing.
- Existing Artist/Group, Release, Recording and Work detail surfaces remain provider-neutral and preserve governed destination disclosure.
- Public search/detail request paths perform no provider HTTP requests and no canonical writes.

## Affected modules and boundaries

- `app/Contracts/Search`
- `app/Actions/Search`
- `app/Support/Search`
- public Search HTTP surface and Blade views when needed
- public canonical catalog routes/read models
- focused Search/PublicCatalog tests

## Expected files

- `docs/foundation/STAGE_18_2_TASK_CONTRACT.md`
- `docs/foundation/STAGE_18_2_VALIDATION_REPORT.md`
- `docs/project/DEVELOPMENT_STATE.md`
- `candidate-verification.json`
- `app/Support/Search/EloquentSearchCatalog.php`
- focused Feature/Architecture tests for production search ranking/facets/canonical URLs
- generated repository authority outputs required by governed candidate preparation

## Allowed incidental files

- Pint-only formatting changes.
- `docs/project/generated/*` repository compiler outputs.
- `candidate-verification.json` when produced/reset by the Stage 18.2 candidate/canonical workflow.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/project/DEVELOPMENT_STATE.md`
- `docs/project/docs/ROADMAP.md`
- `docs/project/domain/route-authority.json`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| Laravel | ^13.0 | `composer.lock` |
| PHP | ^8.5 | `composer.json` / lock/runtime authority |
| PostgreSQL | 18.x | repository runtime authority |

### Official external sources

No new external package, provider API or framework capability is introduced by this stage slice. Search uses existing Laravel query-builder/Eloquent primitives over the repository PostgreSQL authority.

### Native capability assessment

- Capability owner: SongChart public search/read-model boundary.
- Native/first-party capability available: yes for query construction, routing, validation, pagination primitives and dependency injection.
- Selected primitives: Eloquent/query builder, Form Request validation, service-container binding, Blade and existing canonical URL helper.
- Why: the missing behavior is SongChart-specific cross-entity relevance/facet semantics, not missing infrastructure.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: cross-entity canonical ranking/facet semantics and SongChart entity taxonomy.
- Narrow custom boundary: the `SearchCatalog` production read model and focused projection logic.
- Framework primitives reused: parameter binding, Eloquent models, request validation, routing and views.

## Domain contract and use-case data surface

- Actor: anonymous or authenticated public visitor.
- Input: bounded query text, supported canonical entity type, supported sort mode and positive page number.
- Reads: canonical catalog models and approved/fresh provider-destination projections already admitted to SongChart persistence.
- Writes: none.
- Provider HTTP/network calls: none.
- Output: deterministic search page projection and canonical entity detail projections.
- Canonical identity: SongChart entity ULID/slug and type-specific canonical route only.
- Null/unknown semantics: absent metadata remains explicit in existing public presentation; no fabricated popularity/chart/listen data.

## Security, authorization, and data impact

- Public read-only surface; no new permission capability.
- Search query values remain bound parameters.
- SQL identifiers used for display/date/slug fields come only from repository-owned domain contract mappings, never request input.
- No schema migration is required for the initial Stage 18.2 ranking slice.
- No provider secrets, raw payloads or credentials are exposed.

## Verification plan

- Impact lane: Search read model, public catalog Feature tests, route/repository governance, PHPStan.
- Focused gates: PostgreSQL-backed search tests, existing `SearchFlowTest`, `SearchResultsTest`, `PublicCatalogBrowseTest`, PHPStan.
- Stage closure owner: `composer stage:verify`.
- Canonical closure owner: `composer canonical:verify`.

## Tests and verification

- existing `tests/Feature/SearchFlowTest.php`
- existing `tests/Feature/SearchResultsTest.php`
- existing `tests/Feature/PublicCatalog/PublicCatalogBrowseTest.php`
- new PostgreSQL-backed production search ranking/facet coverage
- PHPStan/Larastan analysis
- repository authority/candidate/canonical verification

## Documentation impact

- `DEVELOPMENT_STATE.md` declares Stage 18.2 as current operational work.
- Roadmap retains Stage 18.2 until governed acceptance, then chronology moves to Development History.
- README remains durable project/bootstrap documentation and does not own current-stage state.

## Rollback

Revert the Stage 18.2 search read-model/query/test/documentation changes together. No schema rollback is required for this slice. Existing Stage 18.1 canonical/provider data remains intact.
