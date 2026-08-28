# Stage 18.3.1 Validation Report — Verification & AI Workflow Convergence

Status: implementation complete enough for focused validation; candidate/canonical pending.

## Scope evidence

Implemented workflow surfaces:

- planned impact: `./songchart impact <paths...>`;
- actual-diff impact: `./songchart impact --diff`;
- generated authority reconcile: `./songchart reconcile`;
- collect-all quality/static/governance diagnosis: `./songchart audit`;
- impact-map command/test-target validation;
- runtime artifact tracked-tree ownership guard;
- AI protocol + machine contract golden path;
- delivery one-writer/safe-rebase/exact-closed-HEAD discipline;
- task-contract template planned/post-diff impact fields;
- Architecture coverage for the new workflow surface.

## Official-source evidence

Reviewed on 2026-08-28:

- Git rebase: https://git-scm.com/docs/git-rebase
- GitHub protected branches / required checks: https://docs.github.com/en/repositories/configuring-branches-and-merges-in-your-repository/managing-protected-branches/about-protected-branches
- Composer scripts: https://getcomposer.org/doc/articles/scripts.md
- PHPStan PHPDoc types: https://phpstan.org/writing-php-code/phpdoc-types
- Laravel storage directory ownership: https://laravel.com/docs/13.x/structure

These sources establish upstream behavior only; SongChart repository authorities own project-specific workflow.

## Expected focused verification

Pending execution on exact branch tree:

```bash
./songchart impact --diff
./songchart composer impact-map:verify
./songchart composer runtime-artifact:verify
./songchart composer ai-protocol:verify
./songchart composer verification-topology:verify
./songchart composer verification-surface:verify
./songchart dev test --no-build tests/Architecture/VerificationAiWorkflowConvergenceTest.php
./songchart composer exec pint -- --test
./songchart composer exec phpstan analyse
```

Then:

```bash
./songchart reconcile
./songchart audit
./songchart candidate
./songchart close
```

## Validation matrix

| Requirement | Implementation evidence | Runtime evidence |
|---|---|---|
| Planned + post-diff impact | `scripts/resolve-repository-impact.php`, `songchart` | pending |
| Reverse consumer/focused-check reporting | impact resolver + impact map + consumer graph | pending |
| Reconcile generated authority | `songchart` `reconcile_authority` | pending |
| Collect-all audit | `scripts/run-workflow-audit.php` | pending |
| Dead impact command rejection | `scripts/verify-impact-test-map.php` + corrected map aliases | pending |
| Runtime artifact tracking guard | `scripts/verify-runtime-artifact-ownership.php`, Composer quality ownership | pending |
| AI golden path | AI protocol + machine contract | pending |
| Safe AI/local handoff | `DELIVERY_WORKFLOW.md` | documentation verification pending |
| Public command authority | `verification-command-surface.json`, `verification-topology.json` | pending |
| Architecture regression guard | `VerificationAiWorkflowConvergenceTest.php` | pending |
| Candidate closure | existing governed lane | pending |
| Canonical closure | existing governed lane | pending |

## Deferred by design

- verification evidence caching/reuse;
- aggressive CI path pruning;
- merge queue adoption;
- new workflow orchestration framework.

These are performance/concurrency optimizations, not prerequisites for correctness and diagnosability.

## Closure rule

Stage 18.3.1 is accepted only when focused validation, quality, candidate and canonical closure pass on the exact final HEAD with tracked Git state clean. Any generated authority change after canonical requires another closure run.
