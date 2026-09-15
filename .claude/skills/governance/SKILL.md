---
name: governance
description: Use when changing project authorities, verification contracts, workflow rules, repository state, skills, dependency policy, memory routing, or delivery governance.
---

# SongChart governance skill

Start from repository state, not conversation memory. Resolve the live work lease and current stage, then route the task through `docs/project/engineering/ai-control-plane-contract.json` before creating new surfaces.

For task-scoped orientation, run `php scripts/ai-brief.php --intent "<task>" --json` (add `--hygiene` for repository-maintenance work). Read the returned semantic authorities and preferred paths before editing source.

Apply the new-surface ladder in order: prove the capability is required, extend an existing SongChart owner, prefer framework/platform/native capability, reuse an approved installed package, extend an existing class/file, and only then create the minimum SongChart-specific surface. Majority legacy code never overrides a current machine/domain authority.

Keep one authored owner for durable facts. Generated state, installed-version views, architecture snapshots and client-specific skill mirrors are projections. Do not hand-maintain duplicate facts across those projections.

Treat small fixes as small changes: source plus focused test is the normal shape. Change governance files only when semantic ownership or a durable architectural invariant changes. If a bounded capability exceeds the hand-written fan-out budget in the control-plane contract, review the design for duplicated ownership before adding more files.

When a skill owned under `.agents/skills` changes, use `php scripts/sync-agent-skills.php --write` to refresh declared client projections instead of editing equivalent copies independently.

Preserve Docker-first impact-driven verification and exact-head Auto Closure. Do not add a verifier, memory database, orchestration framework, archive hierarchy or dependency updater when an existing SongChart authority/tool can own the requirement.
