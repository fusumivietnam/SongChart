# Stage 16.5.6 Validation Report — Migration Lifecycle & Upgrade Safety

Status: implementation candidate v24; Docker canonical closure pending.

## Implemented

- restored the frozen Activitylog create migration to the pre-repair historical shape;
- added guarded forward migration `2026_08_16_000100_add_attribute_changes_to_activity_log_if_missing.php`;
- added `migration-lifecycle-contract.json` with SHA-256 fingerprints for 17 historical migrations;
- added `migration-lifecycle:verify`;
- added isolated PostgreSQL `migration-upgrade:verify`;
- canonical pipeline now runs the previous-schema → current upgrade lane before final migration runtime/schema evidence;
- package schema contract records historical/forward ownership;
- AI/project authorities prohibit edits to frozen migration history;
- REG-032 records the historical-migration mutation / upgrade-gap failure class.

## Verification status

Packaging-side static/focused checks will be recorded below. Target Docker canonical closure is not claimed until `songchart verify` passes on the exact tree.


## Packaging-side focused verification

Passed:

- migration lifecycle authority;
- verification command surface/topology;
- release orchestration;
- package schema contract;
- executable repository compiler;
- regression ledger (32 guarded classes);
- candidate contract;
- authority dependency closure;
- impact map;
- AI development protocol;
- documentation and official-source governance;
- code-generation guardrails;
- PostgreSQL/runtime authority static gates;
- CI/package/engineering governance;
- source package verification;
- PHP syntax for both migration files, runtime upgrade runner and Architecture test.

Not claimed in the packaging environment:

- Pint target normalization;
- PHPStan/Larastan target closure;
- Pest full suite;
- PostgreSQL runtime upgrade fixture;
- frontend production build;
- Docker canonical closure.

Those remain owned by `songchart verify` on the target Docker Desktop environment.


## v24.1 corrective — privileged audit consumes migration lifecycle authority

The first v24 canonical run stopped in `privileged-audit:verify` because privileged-audit verification still required `attribute_changes` to exist inside the frozen historical Activitylog create migration.

That ownership is invalid after Stage 16.5.6. v24.1 migrates both privileged-audit consumers to:
- `package-schema-contracts.json` for the final Activitylog schema and ULID adaptations;
- `migration-lifecycle-contract.json` for immutable-history / forward-only policy;
- the registered forward migration for the `attribute_changes` repair.

`migration-lifecycle:verify` now also rejects future verifier/Architecture consumers that try to assert this forward correction against the frozen historical create migration. REG-033 records the class.


## v24.2 corrective — semantic migration fingerprinting

The v24.1 canonical run exposed a design flaw in the immutability guard: frozen migrations used raw file SHA-256 even though canonical verification intentionally runs Pint normalization before quality gates. Formatter/line-ending changes therefore looked like migration mutations.

v24.2 replaces raw-byte fingerprints with `php-token-semantic-v1`:

- ignores PHP whitespace, comments, docblocks and open/close-tag formatting;
- preserves PHP token identity/text and punctuation;
- formatter/CRLF↔LF changes do not alter the fingerprint;
- schema/code changes still alter the fingerprint;
- all 17 frozen historical migrations are re-fingerprinted through the shared `PhpSemanticFingerprint` primitive.

REG-034 records raw-byte migration fingerprint drift.


### v24.2 migration-lifecycle consumer audit

Scanned active `scripts/`, Unit/Architecture/Feature tests, machine contracts and active engineering documentation for:

- raw SHA-256 assumptions over SongChart historical migrations;
- direct ownership assumptions against `2026_08_13_000100_create_activity_log_table.php`;
- `attribute_changes` historical-migration assertions;
- consumers of `historical_migrations`.

Result:

- no executable consumer computes raw SHA-256 over SongChart historical migration files;
- `verify-package-upstream-adaptations.php` still hashes the installed **vendor Spatie migration** for upstream provenance, which is a separate package artifact and is intentionally byte-exact;
- lifecycle-owned consumers (`verify-migration-lifecycle.php`, `MigrationLifecycleUpgradeSafetyTest`) remain explicit exceptions;
- privileged-audit consumers resolve final schema/lifecycle authorities rather than requiring the repaired column in frozen history.

Focused proof also passed:
- semantic fingerprint is invariant under whitespace/comment/CRLF↔LF changes;
- semantic fingerprint changes when a migration changes `json(...)` to `text(...)`;
- migration lifecycle, privileged audit, repository compiler, regression ledger, candidate, authority dependency, impact-map, AI protocol, code-generation, topology and source gates all pass packaging-side.


## v24.3 corrective — exact canonical target migration baseline

v24.2 proved that migration history cannot be retroactively frozen from a packaging-side full-source artifact when the actual canonical target may contain already-normalized equivalent source.

v24.3 bootstraps the baseline only from the exact Docker canonical target:

- pre-canonical `migration-lifecycle:verify` allows a one-time Stage 16.5.6 bootstrap window;
- after locked Pint normalization and `stage:verify`, canonical runs `migration-lifecycle:seal`;
- the sealer writes `docs/project/generated/migration-history-baseline.json` once;
- the sealer refuses to overwrite an existing baseline;
- it refreshes the exact-tree repository manifest;
- canonical immediately reruns `migration-lifecycle:verify` in strict sealed mode;
- subsequent stages inherit and enforce the sealed baseline.

REG-035 records non-canonical retroactive migration baseline drift.


## Canonical closure — v24.3

Status: **CLOSED**.

The exact Stage 16.5.6 v24.3 target tree completed Docker canonical verification successfully:

```text
[SongChart verify] Canonical verification PASSED.
[SongChart verify] PASSED.
```

Stage 16.5.7 therefore starts from the canonical-closed v24.3 tree, including the target-sealed migration-history baseline.
