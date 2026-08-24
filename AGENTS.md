# SongChart AI Bootstrap

This file is intentionally thin. Do not duplicate project workflow or verification rules here.

Mandatory read order before modifying SongChart:
1. `PROJECT_AUTHORITY.md`
2. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
3. the current stage task contract
4. `docs/project/DEVELOPMENT_STATE.md` for the non-authoritative operational checkpoint
5. the owning domain/module authority
6. `docs/project/engineering/DELIVERY_WORKFLOW.md` before packaging/hand-off

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

Development-state maintenance:
- `docs/project/DEVELOPMENT_STATE.md` is a resume/checkpoint aid, never a replacement for README, candidate evidence, history, roadmap or task contracts.
- Update it in the same changeset whenever the current blocker, accepted baseline, latest focused evidence or next required action changes.
- Never mark a stage accepted there unless canonical Docker verification has passed for the exact target tree.
