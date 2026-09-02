## Scope

- Stage / corrective:
- Semantic owner(s):
- Non-goals preserved:

## Exact tree

- Head SHA:
- Base SHA:
- Working tree clean: [ ]
- Upstream synchronized / no local-only commits: [ ]

## Contract convergence

- Current typed/domain/machine authority identified: [ ]
- Legacy source/tests/fixtures/docs converged to the current owner where applicable: [ ]
- No obsolete synonym/compatibility alias added without an explicit compatibility contract: [ ]
- Historical migrations unchanged unless a governed forward migration is part of this PR: [ ]

## Verification

- `./songchart impact --diff`: [ ] PASS / N/A
- `./songchart reconcile`: [ ] current / N/A
- `docs/project/generated/` clean: [ ]
- Focused Pint/PHPStan/tests: [ ] PASS / N/A
- `./songchart impact --verify`: [ ] PASS
- `./songchart candidate`: [ ] PASS
- `./songchart verify`: [ ] PASS
- Canonical-verified SHA matches PR head SHA: [ ]

## GitHub evidence

- Required PR checks on latest SHA: [ ] PASS
- PostgreSQL failure artifact reviewed when the CI PostgreSQL lane failed: [ ] N/A / reviewed
- No secret/runtime evidence committed to source: [ ]

## Release readiness

- Any tracked change after canonical PASS? [ ] No
- Release/package/tag requested by this PR? [ ] No / Yes, from accepted exact `main` only
