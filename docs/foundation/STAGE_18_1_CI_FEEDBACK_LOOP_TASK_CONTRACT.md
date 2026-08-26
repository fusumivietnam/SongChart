# Stage 18.1 Corrective — Development/CI Feedback Loop Optimization

Status: corrective task contract.

## Goal

Reduce redundant GitHub Actions execution during feature-branch iteration without weakening SongChart quality, PostgreSQL, frontend, candidate, canonical, or release gates.

## Non-goals

- Removing any CI job or verification command.
- Replacing PostgreSQL release authority with SQLite or mocks.
- Reusing stale candidate/canonical evidence.
- Adding path filters that could silently skip required verification.
- Changing canonical verification semantics.

## Acceptance criteria

- Feature-branch pushes without an open PR do not launch the full GitHub CI workflow.
- Pull requests targeting `main` run the existing quality, PostgreSQL, and frontend jobs.
- Pushes to `main` run the same jobs as a post-merge safety boundary.
- A newer run for the same PR/ref cancels an older in-progress run.
- CI configuration verification fails closed if the trigger/concurrency contract is removed.
- Developer/AI workflow authority explicitly requires focused local verification during iteration, logical-batch pushes, PR CI before merge, candidate closure on the exact candidate tree, and canonical closure before release/merge completion.
- Verification topology records that CI orchestration may reduce duplicate executions but must not reduce gate coverage.

## Affected modules and boundaries

- `.github/workflows/tests.yml`
- `scripts/verify-ci-configuration.php`
- `docs/project/engineering/AI_DEVELOPMENT_PROTOCOL.md`
- `docs/project/engineering/verification-topology.json`

## Security and data impact

No runtime application, schema, provider, authorization, secret, or canonical-data behavior changes.

## Verification plan

- `composer ci:configuration`
- `composer verification-topology:verify`
- `composer ai-protocol:verify`
- `composer quality:verify`
- `./songchart candidate`

## Rollback

Revert this corrective as one unit. Do not partially revert the workflow without also reconciling its machine verifier and engineering authorities.
