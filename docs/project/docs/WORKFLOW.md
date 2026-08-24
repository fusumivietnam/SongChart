# Development Workflow

## Branches

- `main`: releasable.
- Short-lived feature branches.
- Pull request required for meaningful changes.

## Commit format

Conventional Commits:
- feat
- fix
- refactor
- test
- docs
- chore
- perf
- security

## Pull request

Include:
- problem;
- solution;
- screenshots for UI;
- migrations;
- tests;
- policy/provider impact;
- rollback risk.

## Task template

1. Goal
2. Non-goals
3. Acceptance criteria
4. Affected modules
5. Data/migration impact
6. Provider/policy impact
7. Test plan
8. Documentation updates

## Documentation workflow

Before implementation:

- read `AGENTS.md`, `docs/START_HERE.md` and `docs/DOCUMENTATION_GOVERNANCE.md`;
- identify the owning authority and module documents;
- search for existing responsibilities before creating code or documentation;
- create or update the task contract.

After implementation:

- update affected authorities, status and change manifest;
- record architectural decisions in an ADR;
- separate verified checks from checks not executed;
- run `composer docs:verify`;
- package full source and a change-set with apply/verify scripts and deletion list.
