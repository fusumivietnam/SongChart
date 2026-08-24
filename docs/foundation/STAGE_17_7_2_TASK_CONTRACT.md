# Stage 17.7.2 Task Contract

## Scope

Close the final verification-project isolation architecture-test regression without changing runtime verification isolation, YouTube provider behavior, persistence schema, quota policy, or public destination semantics.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/foundation/STAGE_17_7_TASK_CONTRACT.md`
- `docs/foundation/STAGE_17_7_1_TASK_CONTRACT.md`
- `scripts/verify-canonical.ps1`
- `tests/Architecture/VerificationProjectIsolationTest.php`

### Installed versions

- Laravel 13.x from the repository lockfile.
- PHP 8.5 is the canonical Docker verification runtime.
- Pest/PHPUnit versions are owned by the repository lockfile.
- PostgreSQL 18 remains the canonical database authority.

### Official external sources

No external API or framework behavior changes are introduced by this corrective. Stage 17.7 official YouTube sources remain authoritative for the provider foundation.

### Native capability assessment

- Use normal PHP string escaping so the architecture test asserts the literal PowerShell variable name `$ProjectName`.
- Keep the existing Pest expectation and PowerShell verification implementation unchanged.

### Custom implementation justification

No new custom abstraction is required. The failure is in the test literal itself: a PHP double-quoted string interpolates `$ProjectName` before Pest evaluates the assertion. Escaping the dollar sign preserves the intended architecture assertion.

## Required corrections

- Assert the literal PowerShell assignment `\$ProjectName = 'songchart-verify'` without triggering PHP variable interpolation.
- Keep the `songchart-verify` Compose project namespace unchanged.
- Keep the minimum usage-count assertion for `-p $ProjectName -f $Compose` unchanged.

## Non-goals

- No migration.
- No YouTube API behavior changes.
- No changes to Compose runtime behavior.
- No PHPStan policy changes.

## Tests and verification

- Run PHP syntax on the corrected architecture test.
- Run architecture/documentation/repository-state/official-source/candidate/test-taxonomy/verification-surface gates.
- Run canonical Docker verification on the target repository with `verify-songchart.bat`.

## Acceptance criteria

- `VerificationProjectIsolationTest` no longer raises `Undefined variable $ProjectName`.
- The test still proves canonical verification uses the dedicated `songchart-verify` project namespace.
- Canonical closure remains `verify-songchart.bat` in Docker.
