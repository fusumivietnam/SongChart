# Stage 17.7.1 Task Contract

## Scope

Close the PHPStan/Larastan type-contract regressions exposed by Stage 17.7 YouTube destination discovery without changing provider behavior, persistence schema, quota policy, or public destination semantics.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/foundation/STAGE_17_7_TASK_CONTRACT.md`
- `docs/providers/STAGE_17_7_YOUTUBE_DESTINATION_CONTRACT.md`
- `phpstan.neon`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- PHPStan/Larastan versions are owned by the repository lockfile.
- PostgreSQL 18 remains the canonical database authority.

### Official external sources

No new external API behavior is introduced. Stage 17.7 official YouTube sources remain authoritative:

- YouTube Data API `search.list`: https://developers.google.com/youtube/v3/docs/search/list
- YouTube Data API `videos.list`: https://developers.google.com/youtube/v3/docs/videos/list
- PHPStan iterable value types: https://phpstan.org/blog/solving-phpstan-no-value-type-specified-in-iterable-type
- PHPStan undefined properties: https://phpstan.org/blog/solving-phpstan-access-to-undefined-property

### Native capability assessment

- Use Eloquent `getRelation()` / `getAttribute()` to expose dynamic relation and cast values through explicit runtime APIs that static analysis can narrow safely.
- Use PHPDoc collection value types where the method contract already returns a typed list.
- Keep Laravel model casts and relationship ownership unchanged.

### Custom implementation justification

No new custom abstraction is added. The corrective removes reliance on Eloquent magic-property inference at the two affected projection boundaries rather than adding PHPStan suppressions or weakening repository analysis policy.

## Required corrections

- Keep `verifiedCandidates()` explicitly typed as `list<VideoDestinationCandidate>`.
- Read Artist Credit pivot metadata through the Eloquent relation API instead of relying on an undeclared dynamic `$pivot` property.
- Project approved provider destinations through explicit relation and datetime attribute normalization instead of assuming Larastan magic-property cast inference.
- Do not add PHPStan ignores, baselines, suppressions, or weaken `treatPhpDocTypesAsCertain`.

## Non-goals

- No migration.
- No YouTube API behavior changes.
- No scoring or approval policy changes.
- No changes to verification Compose isolation introduced in Stage 17.7.

## Tests and verification

- Run changed-file PHP syntax checks.
- Run architecture, documentation, repository-state, official-source, contract compiler, test-taxonomy and verification-surface gates.
- Run canonical Docker PHPStan/Pest/PostgreSQL verification on the target repository with `verify-songchart.bat`.

## Acceptance criteria

- The 13 reported Stage 17.7 PHPStan errors are structurally removed.
- Architecture and repository authority gates remain green.
- Canonical closure remains `verify-songchart.bat` in Docker.
