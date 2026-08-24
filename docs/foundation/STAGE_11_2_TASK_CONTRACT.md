# Stage 11.2 Task Contract — Documentation Governance

Product: `SongChart 0.1.0-dev`

## Goal

Make the existing documentation-first workflow explicit, navigable and executable without creating a second project documentation hierarchy.

## Non-goals

- no application feature;
- no schema, route, provider or UI behavior change;
- no package addition;
- no relocation of existing authority documents;
- no rewriting of historical phase documents merely for formatting consistency.

## Acceptance criteria

- one documentation governance contract defines authority, lifecycle, placement and Definition of Done;
- one documentation index routes contributors to existing authorities without copying their content;
- `AGENTS.md` and `docs/START_HERE.md` require the governance workflow;
- project workflow and PR checklist include documentation impact and verification;
- ADR registry records the documentation governance decision with unique identifiers;
- an executable verifier checks required documents, prohibited duplicate authority names and local Markdown links;
- `composer docs:verify` is included in `composer verify`;
- architecture tests protect the documentation boundary;
- Stage 11 status and change manifest are reconciled;
- delivery includes full source and an applicable Windows change-set.

## Affected modules

- repository governance;
- project documentation;
- verification tooling;
- architecture tests;
- release packaging.

## Data/provider/policy impact

None. This stage changes development governance only.

## Tests

- standalone documentation verifier;
- PHP syntax lint for the verifier and tests;
- architecture tests for required governance files and Composer scripts;
- full `composer verify` on a dependency-complete development machine.

## Documentation impact

Added governance/index/task/ADR documents and updated existing repository workflow, status and manifest documents.
