# Stage 17.1.3 — Official-Source Contract & Verification Workflow Corrective

Status: corrective implementation contract.

## Goal

Close the Stage 17.1.2 governance gap by restoring the mandatory official-source evidence structure and clarify the relationship between the stage and canonical Docker verification entrypoints so developers do not run the same stage closure twice unnecessarily.

## Non-goals

- no MusicBrainz runtime behavior change;
- no provider, schema, route, authentication, authorization, or public UI change;
- no weakening of `official-sources:verify`;
- no removal of `stage-verify.bat` or `verify-songchart.bat`;
- no change to the fact that canonical verification is independently reproducible and includes stage closure.

## Acceptance criteria

- the current-stage task contract contains every official-source evidence section required by repository governance;
- `composer official-sources:verify` passes;
- documentation states that `stage-verify.bat` is the iterative candidate gate and `verify-songchart.bat` is the self-contained canonical closure;
- canonical closure continues to execute `composer canonical:verify`, which includes `@stage:verify`;
- developers are not instructed to run `stage-verify.bat` immediately before `verify-songchart.bat` as a mandatory two-command sequence;
- both Windows wrappers remain thin compatibility entrypoints over the Docker-first `songchart` CLI.

## Authority and official sources

### Repository authorities

- `PROJECT_AUTHORITY.md`
- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/templates/TASK_CONTRACT_TEMPLATE.md`
- `docs/project/engineering/DELIVERY_WORKFLOW.md`
- `docs/project/engineering/verification-command-surface.json`
- `composer.json`
- `scripts/songchart.ps1`
- `scripts/verify-canonical.ps1`

### Installed versions

No dependency or runtime version changes. PHP, Laravel, Composer, Node, PostgreSQL and Redis versions remain authoritative from the accepted lockfiles and Docker verification definitions.

### Official external sources

None required for this corrective. The change reconciles repository-local governance and Docker orchestration semantics only; it introduces no new external API or framework behavior.

### Native capability assessment

- Capability owner: repository verification orchestration.
- Native/first-party capability available: yes.
- Selected primitive: Composer script composition plus Docker Compose and PowerShell/batch compatibility wrappers already owned by the repository.
- Why it satisfies the requirement: `canonical:verify` can include `stage:verify` directly, allowing canonical verification to remain independently reproducible without a second host-side stage command.

### Custom implementation justification

- Custom code required: minimal.
- Missing official behavior: Composer/Docker do not define SongChart's stage-versus-canonical developer workflow or task-contract evidence rules.
- Narrow custom boundary: repository Markdown authorities, current-stage evidence, thin wrapper messaging, and static verification of command semantics.
- Framework primitives reused: existing Composer scripts, Docker Compose profiles, and repository verifiers.
- Non-goals: introducing another verification engine or duplicating quality/test commands.

## Security, authorization, and data impact

None. This corrective changes verification/documentation surfaces only.

## Tests and verification

- `php scripts/verify-official-sources.php`
- `php scripts/verify-verification-command-surface.php`
- `php scripts/verify-documentation.php`
- `php scripts/verify-repository-state.php`
- `php scripts/verify-repository-contract-compiler.php`
- Docker stage verification during iteration when required
- canonical closure with `verify-songchart.bat` / `songchart.bat verify`; canonical already includes `@stage:verify`

## Rollback

Restore the Stage 17.1.2 documentation/metadata and prior wrapper text. No runtime or database rollback is required.
