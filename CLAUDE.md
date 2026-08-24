# Claude Bootstrap


This file is intentionally thin. Do not duplicate project workflow or verification rules here.

Mandatory read order before modifying SongChart:
1. `PROJECT_AUTHORITY.md`
2. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
3. the current stage task contract
4. the owning domain/module authority

Use `php scripts/resolve-repository-impact.php <changed-paths>` before implementation when the planned change surface is known.

Verification authority:
- focused iteration: impact-driven gates from the task
- candidate closure: `composer stage:verify`
- canonical closure: canonical Docker lane → `composer canonical:verify`
- packaging: `composer release:package` only after canonical/provenance PASS

For UI work also read `docs/ui/DESIGN_AUTHORITY.md` and the relevant UI contract.

Additional authorities:
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/foundation/CODE_GENERATION_RULES.md`
