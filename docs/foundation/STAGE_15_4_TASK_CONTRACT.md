# Stage 15.4 Task Contract — Official-First Engineering Governance

Status: task contract.

## Goal

Make official-first engineering enforceable through a single authority document, a reusable task-contract template, and a CI verifier in the Composer quality chain.

## Non-goals

- No application runtime behavior change.
- No dependency, schema, route, provider, or authentication change.
- No retroactive rewrite of every historical task contract.

## Acceptance criteria

- One canonical official-source policy exists.
- Root `AGENTS.md` points agents to the policy.
- `docs/project/AGENTS.md` is pointer-only and cannot compete as authority.
- A reusable task-contract template contains official-source and native-capability evidence sections.
- `composer official-sources:verify` validates the governance boundary.
- `quality:verify` executes the verifier.
- Architecture tests protect the command and authority files.

## Affected modules and boundaries

- Repository governance, documentation, Composer quality scripts, and architecture tests only.

## Authority and official sources

### Repository authorities

- `AGENTS.md`
- `docs/DOCUMENTATION_GOVERNANCE.md`
- `docs/project/docs/WORKFLOW.md`
- `docs/project/stack/FRAMEWORK_BASELINE.md`
- `docs/project/stack/PACKAGE_ADOPTION_POLICY.md`

### Installed versions

| Capability | Version or constraint | Version authority |
|---|---|---|
| PHP runtime | Existing project constraint | `composer.json` and `composer.lock` |
| Laravel | Existing resolved version | `composer.lock` |
| Composer scripts | Repository-defined | `composer.json` |

### Official external sources

No external runtime API is implemented in this governance-only stage. The policy defines how future tasks must consult official Laravel, dependency, provider, standards, PHP, and PostgreSQL sources matching installed versions.

### Native capability assessment

- Capability owner: SongChart repository governance.
- Native/first-party capability available: partial.
- Selected official primitive: Composer scripts and executable PHP verification, consistent with the existing project quality chain.
- Why it satisfies the requirement: Composer already owns deterministic local and CI command orchestration for this repository.

### Custom implementation justification

- Custom code required: yes.
- Missing official behavior: neither Laravel nor Composer defines this repository's evidence policy or task-contract requirements.
- Narrow custom boundary: one documentation policy, one Markdown template, one static verifier, and one architecture test.
- Framework primitives reused: Composer script orchestration and the existing Pest architecture suite.
- Non-goals: no custom package manager, documentation engine, or external link crawler.

## Security, authorization, and data impact

No runtime authentication, authorization, user data, provider data, or database impact.

## Tests and verification

- PHP syntax for the verifier.
- Static official-source verifier.
- Architecture governance test.
- Pint and Larastan through the existing release gate on the target environment.

## Documentation impact

Update `AGENTS.md`, documentation governance/index/start guide, README current stage, and Stage 12 change manifest.

## Rollback

Restore the modified authority/index files, remove the policy/template/verifier/test/stage documents, and remove the Composer command from `quality:verify`.
