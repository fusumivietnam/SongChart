# SongChart AI Bootstrap

This file is intentionally thin. Repository authorities, not chat history, own project memory.

Before modifying source:
1. Read `PROJECT_AUTHORITY.md`.
2. Read `docs/project/generated/project-context.json` for generated repository facts.
3. Read `docs/project/engineering/project-knowledge.json` for compact product/domain knowledge.
4. Read `docs/project/engineering/consolidation-plan.json` before adding packages, abstractions, infrastructure or verifier surfaces.
5. Read `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`, the current task contract, and the owning domain/module authority.
6. Run `./songchart ai status`; use `./songchart impact <paths...>` when the planned change surface is known.

Default ownership order: Laravel first-party → mature documented package → genuinely SongChart-specific custom code. Package adoption must retire replaced custom code instead of preserving permanent dual implementations.

Every bounded tranche must review continuous optimization: reuse/package opportunity, custom code retirement, automation opportunity, documentation/knowledge impact, and active brand cleanup. Record deferred work in `consolidation-plan.json` rather than rediscovering it later.

PR closure is GitHub Auto Closure: PREPARE → CHECK → canonical CLOSE → Ready. `composer stage:verify` and `composer canonical:verify` remain explicit diagnostics; release packaging occurs only after canonical/provenance PASS.

For UI work also read `docs/ui/DESIGN_AUTHORITY.md`. For hand-off/promotion read `docs/project/engineering/DELIVERY_WORKFLOW.md`.
