# Stage 15.5 Task Contract — AI Development Workflow & Change-Surface Governance

Status: task contract.

## Goal

Make focused AI development enforceable through a state-machine workflow, reusable implementation/debug/validation templates, bounded-context skills, impact-test mapping and executable quality gates.

## Non-goals

- No application runtime, route, schema, provider or authentication behavior changes.
- No external agent framework or knowledge-graph dependency.
- No automatic code mutation.

## Acceptance criteria

- Canonical engineering workflow defines ordered states and two review gates.
- Templates require expected files, impact preflight, root-cause evidence and validation matrix.
- Repository-local skills route agents to bounded-context authorities.
- Machine-readable impact-test map exists.
- Workflow and no-placeholder verifiers run in `quality:verify`.
- Change-surface verifier supports plan-only and git-base enforcement modes.
- Architecture tests protect the governance boundary.

## Expected files

- `AGENTS.md`
- `README.md`
- `STAGE_12_CHANGE_MANIFEST.md`
- `composer.json`
- `docs/START_HERE.md`
- `docs/DOCUMENTATION_INDEX.md`
- `docs/project/docs/ENGINEERING_WORKFLOW.md`
- `docs/project/stack/impact-test-map.json`
- `docs/templates/IMPLEMENTATION_PLAN_TEMPLATE.md`
- `docs/templates/DEBUGGING_REPORT_TEMPLATE.md`
- `docs/templates/VALIDATION_REPORT_TEMPLATE.md`
- `.agents/skills/authentication/SKILL.md`
- `.agents/skills/extensions/SKILL.md`
- `.agents/skills/providers/SKILL.md`
- `.agents/skills/identity/SKILL.md`
- `.agents/skills/governance/SKILL.md`
- `scripts/verify-ai-workflow.php`
- `scripts/verify-change-surface.php`
- `scripts/verify-no-placeholders.php`
- `tests/Architecture/AIWorkflowGovernanceTest.php`
- `docs/foundation/STAGE_15_5_TASK_CONTRACT.md`
- `docs/foundation/STAGE_15_5_VALIDATION_REPORT.md`

## Allowed incidental files

- Formatter-only changes to files above.

## Scope deviations

None.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/project/docs/OFFICIAL_SOURCE_POLICY.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/project/docs/WORKFLOW.md`

### Installed versions

Composer and PHP constraints remain authoritative in `composer.json` and `composer.lock`; no dependency version changes occur.

### Official external sources

- Composer script schema and PHP CLI/runtime behavior are the only external primitives used.
- Workflow concepts were evaluated from public agent-methodology and code-graph projects, but repository policy and custom verifiers remain SongChart-owned and dependency-free.

### Native capability assessment

Composer already owns deterministic quality orchestration. Git provides changed-file enumeration. PHP provides portable repository verification. These native primitives are selected instead of an agent framework dependency.

### Custom implementation justification

Laravel and Composer do not define SongChart task states, expected change surfaces, impact-test ownership or evidence wording. Custom code is limited to static verifiers, templates and routing skills; it does not implement an AI runtime or code graph.

## Security, authorization, and data impact

No runtime security, authorization or data impact.

## Tests and verification

PHP syntax, workflow verifier, no-placeholder verifier, architecture test, Pint, Larastan and full release matrix on the target environment.

## Rollback

Remove Stage 15.5 files and Composer commands, then restore modified authority/index files.
