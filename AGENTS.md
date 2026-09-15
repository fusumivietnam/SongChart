# SongChart AI Bootstrap

This file is intentionally thin. Repository authorities, not chat history, own project memory.

Before modifying source:
1. Read `PROJECT_AUTHORITY.md`.
2. Read `docs/project/generated/development-state.json` and `docs/project/generated/project-context.json` before any authored progress prose.
3. Resolve the live work lease with `./songchart ai status --json`; resume an existing branch/PR for the same semantic owner instead of creating duplicate work.
4. Read `docs/project/engineering/project-knowledge.json`, `docs/project/engineering/consolidation-plan.json`, and the Project Intelligence authority paths exposed by generated `project-context.json`.
5. When architecture/use-case impact matters, inspect `./songchart artisan project:intelligence --json`; refresh with `--write` only explicitly. Never rebuild graph data from an HTTP request.
6. Read `docs/project/docs/OFFICIAL_SOURCE_POLICY.md` and `docs/foundation/CODE_GENERATION_RULES.md` before package/framework/code-generation decisions.
7. Read `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`, the current task contract, and the owning domain/module authority.
8. Use `./songchart impact <paths...>` when the planned change surface is known.

Default ownership order: Laravel first-party → mature documented package → genuinely SongChart-specific custom code. Package adoption must retire replaced custom code instead of preserving permanent dual implementations.

Implementation posture is Ponytail/full: stop at the first solution that fully holds — skip speculative work (YAGNI), reuse existing SongChart code/contracts, prefer language/framework/platform-native capability, then an already-approved dependency, and only then write the minimum SongChart-specific code. Minimal must never mean weaker security, trust-boundary validation, data integrity, accessibility, canonical authority, or required verification.

Every bounded tranche must review continuous optimization: reuse/package opportunity, custom code retirement, automation opportunity, documentation/knowledge impact, active brand cleanup, release weight, mobile/remote operability, and technology SWOT/ROI freshness. Record deferred work in repository machine authority rather than rediscovering it later.

PR closure is GitHub Auto Closure: PREPARE → CHECK → canonical CLOSE → Ready. Any new commit invalidates exact-head evidence. Human promotion remains Merge/Release.

For UI work also read `docs/ui/DESIGN_AUTHORITY.md`. For hand-off/promotion read `docs/project/engineering/DELIVERY_WORKFLOW.md`.
