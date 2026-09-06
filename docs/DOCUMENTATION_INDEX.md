# Documentation Index

This index lists active documentation ownership. Historical stage records are intentionally not enumerated here.

## Repository and development

- `PROJECT_AUTHORITY.md` — repository engineering authority.
- `docs/START_HERE.md` — reading and routing entry point.
- `docs/DOCUMENTATION_GOVERNANCE.md` — documentation lifecycle and ownership rules.
- `docs/project/DEVELOPMENT_STATE.md` — compatibility pointer to generated current development state.
- `docs/project/DEVELOPMENT_HISTORY.md` — accepted development chronology.
- `docs/project/RELEASE_BASELINE_STATUS.md` — release baseline requirements.
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md` — development and AI workflow.
- `docs/project/engineering/ENGINEERING_GRAPH.md` — human-readable graph-of-graphs traversal for authority, impact, verification, regression and current-stage topology; not a parallel machine authority.
- `docs/project/engineering/AI_LEARNING_LEDGER.md` — provisional evidence/history for reusable use-case/debug/failure learnings; guarded durable regressions are promoted to `regression-ledger.json` and their owning authority.
- `docs/project/engineering/DELIVERY_WORKFLOW.md` — Git handoff, candidate, canonical, and release flow.
- `docs/project/engineering/PROJECT_CONTEXT_AUTHORITY.md` — generated project-context contract.

## Product

- `docs/project/docs/PRODUCT.md` — product direction.
- `docs/project/docs/SCOPE.md` — product scope.
- `docs/project/docs/ROADMAP.md` — future direction.
- `docs/project/docs/ARCHITECTURE.md` — application architecture.
- `docs/project/docs/DATA_MODEL.md` — canonical data model.
- `docs/project/docs/SECURITY.md` — security rules.
- `docs/project/docs/URL_SEO.md` — URL and SEO rules.
- `docs/project/docs/OBSERVABILITY.md` — observability.
- `docs/project/docs/DECISIONS.md` — ADR registry.
- `docs/project/docs/TESTING.md` — testing strategy.
- `docs/project/docs/ENGINEERING_RULES.md` — durable implementation conventions.

## Machine authorities

### Domain

- `docs/project/domain/domain-contracts.json`
- `docs/project/domain/operational-contracts.json`
- `docs/project/domain/use-case-contracts.json`
- `docs/project/domain/schema-ownership.json`
- `docs/project/domain/application-data-boundary.json`

### Engineering

- `docs/project/engineering/ai-development-contract.json`
- `docs/project/engineering/stage-plan.json`
- `docs/project/engineering/project-knowledge.json`
- `docs/project/engineering/mcp-governance-contract.json`
- `docs/project/engineering/external-systems-registry.json`
- `docs/project/engineering/verification-topology.json`
- `docs/project/engineering/verification-command-surface.json`
- `docs/project/engineering/verification-consumer-graph.json`
- `docs/project/engineering/repository-contract-compiler.json`
- `docs/project/engineering/regression-ledger.json`
- `docs/project/governance/authority-dependencies.json`

### Runtime

- `docs/project/stack/runtime-environments.json`
- `docs/project/stack/docker-development-contract.json`
- `docs/project/stack/stack-manifest.json`
- `docs/project/stack/impact-test-map.json`
- `docs/project/stack/migration-lifecycle-contract.json`

### Security

- `docs/project/security/authorization-contract.json`

## Module authorities

- `docs/providers/` — provider behavior and compliance.
- `docs/operations/` — operational integrations and procedures.
- `docs/extensions/` — extension lifecycle.
- `docs/ui/` — public/admin design contracts.

## Historical records

`docs/foundation/`, older stage-specific UI/catalog documents, and root historical change manifests contain implementation evidence.

They must not be used as current product, workflow, runtime, or development-state authority unless an active machine contract explicitly references them.
