# Stage 16.5.5 Validation Report — Verification Surface Reduction

Status: implementation candidate v23; Docker canonical closure pending.

## Implemented

- added machine-readable `verification-command-surface.json`;
- removed six redundant Composer aliases;
- retired historical Stage 11 release/export executables from active scripts;
- migrated active verifiers/Architecture tests to stage/canonical/package owners;
- updated active README/project/AI/testing/release documentation;
- added `verification-surface:verify`;
- added `VerificationCommandSurfaceTest`;
- registered the new authority in repository compiler, impact map and dependency graph;
- recorded REG-029 `verification-command-surface-sprawl`.

## Closure rule

No stage closure is claimed until exact-tree `songchart verify` passes in Docker and canonical evidence/provenance remains valid.


## v23.1 corrective — runtime authority stops requiring retired database-test aliases

The first v23 canonical run reached `runtime-authority:verify`. That verifier still required `test:all`, even though Stage 16.5.5 intentionally removed it from the active command surface.

v23.1:
- validates only active database test commands: `test`, `test:feature`, `test:postgres`;
- explicitly rejects restoration of `test:all` and `test:postgres-clean`;
- removes those aliases from `database-test-contract.json`;
- records REG-030 `retired-alias-consumer-drift`.

No application/runtime/database behavior changes.


## v23.2 corrective — packaging Architecture test follows provenance ownership

The v23.1 canonical run passed 282 tests and failed only because `ContractCoverageReleaseBaselineTest` required `package-verified-source.php` itself to contain the `closure_ready` literal.

That is a cross-layer assertion. The packager deliberately delegates canonical-tree admissibility to `verify-artifact-provenance.php`.

v23.2 verifies the real boundary:
- source verifier owns dependency lockfile presence;
- packager must invoke `verify-artifact-provenance.php`;
- provenance verifier owns `closure_ready` and exact-tree/lock hash checks;
- all negative Pest expectations in the touched Architecture test are expressed as statically visible boolean predicates.

REG-031 records the cross-layer packaging assertion class.


## Canonical closure

Status: **CLOSED**.

The exact Stage 16.5.5 v23.2 target tree completed Docker canonical verification successfully:

```text
[SongChart verify] Canonical verification PASSED.
[SongChart verify] PASSED.
```

Stage 16.5.6 therefore starts from the canonical-closed v23.2 baseline.
