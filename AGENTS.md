# SongChart AI Bootstrap

This file is intentionally thin. Do not duplicate project workflow or verification rules here. Before modifying source, run `./songchart ai status`; it is read-only and never starts Docker.

Mandatory read order before modifying SongChart:
1. `PROJECT_AUTHORITY.md`
2. `docs/project/generated/project-context.json` for current generated repository facts
3. `docs/project/engineering/project-knowledge.json` for compact product/domain/documentation knowledge
4. `docs/project/engineering/consolidation-plan.json` before adding infrastructure, packages, abstractions or new verifier surfaces
5. `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
6. the current stage task contract
7. `docs/project/DEVELOPMENT_STATE.md` for the non-authoritative operational checkpoint
8. the owning domain/module authority
9. `docs/project/engineering/DELIVERY_WORKFLOW.md` before promotion or hand-off

The repository is the memory authority. Do not rely on chat history for product identity, architecture, package ownership, active retirement work, provider semantics or verification state when these repository authorities provide the answer.

Use `php scripts/resolve-repository-impact.php <changed-paths>` before implementation when the planned change surface is known.

Dependency/default ownership rule:
- prefer Laravel first-party capability;
- then prefer a mature, documented package recorded by the consolidation/package authorities;
- keep custom SongChart code only for genuinely product-specific domain or trust-boundary semantics;
- when a package replaces custom code, schedule removal of the replaced surface rather than keeping permanent dual implementations.

Verification authority:
- focused iteration: impact-driven gates from the task
- PR closure: GitHub Auto Closure owns PREPARE → CHECK → canonical CLOSE → Ready
- candidate diagnostic: `composer stage:verify`
- canonical diagnostic: canonical Docker lane → `composer canonical:verify`
- packaging: `composer release:package` only after canonical/provenance PASS

For UI work also read `docs/ui/DESIGN_AUTHORITY.md` and the relevant UI contract.

Additional authorities:
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/foundation/CODE_GENERATION_RULES.md`

Development-state maintenance:
- `docs/project/DEVELOPMENT_STATE.md` is a resume/checkpoint aid, never a replacement for repository knowledge, candidate evidence, history, roadmap or task contracts.
- Update it in the same changeset whenever the current blocker, accepted baseline, latest focused evidence or next required action changes.
- Never mark a stage accepted there unless canonical Docker verification has passed for the exact target tree.
