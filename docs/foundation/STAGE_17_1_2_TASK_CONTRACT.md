# Stage 17.1.2 — MusicBrainz Test Taxonomy Corrective

Status: corrective implementation contract.

## Problem

`tests/Unit/Providers/MusicBrainzProviderCatalogAdapterTest.php` exercises Laravel configuration, cache locks, and HTTP fakes. The repository Unit taxonomy intentionally forbids those framework-dependent facilities in `tests/Unit`.

## Scope

- move the MusicBrainz adapter integration test to `tests/Feature/Providers`;
- keep `scripts/verify-test-taxonomy.php` strict and unchanged;
- preserve the Stage 17.1 MusicBrainz runtime contract and adapter behavior;
- publish both incremental changeset and full Laragon-ready source.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `composer.json`
- `scripts/verify-test-taxonomy.php`

### Installed versions

No dependency or runtime version changes were introduced by this corrective. Installed PHP, Laravel, Composer, Node, PostgreSQL, Redis, and package versions remain authoritative from the accepted lockfiles and Docker definitions of the Stage 17.1 baseline.

### Official external sources

None required. This corrective only reclassifies an existing repository test between test lanes and does not introduce or change external API, framework, or provider behavior.

### Native capability assessment

- Capability owner: repository test taxonomy and Laravel/Pest test execution.
- Native/first-party capability available: yes.
- Selected primitive: place Laravel framework-dependent tests in the framework-backed Feature lane while retaining pure Unit tests in `tests/Unit`.
- Why it satisfies the requirement: the existing taxonomy verifier already distinguishes framework-dependent signals such as `config()`, `Cache::`, and `Http::`; no new test framework or custom execution lane is required.

### Custom implementation justification

- Custom code required: none for runtime behavior.
- Repository change required: move the existing MusicBrainz adapter test to the correct test lane and update stage evidence.
- Narrow custom boundary: documentation and file placement only.
- Non-goals: weakening taxonomy rules, introducing exceptions, or changing the MusicBrainz adapter contract.

## Tests and verification

- `php scripts/verify-test-taxonomy.php`
- `php scripts/verify-official-sources.php`
- `php scripts/verify-documentation.php`
- `php scripts/verify-repository-state.php`
- Docker stage verification during iteration when required
- canonical closure through `verify-songchart.bat` / `songchart.bat verify`

## Acceptance

- `composer test-taxonomy:verify` passes;
- the moved test remains executable in the framework-backed Feature lane;
- no duplicate copy remains in `tests/Unit/Providers`;
- stage and canonical verification remain Docker-first.
