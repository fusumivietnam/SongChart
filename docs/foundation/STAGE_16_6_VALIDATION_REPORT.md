# Stage 16.6 Validation Report — Authorization Consolidation

Status: implementation candidate v26; Docker canonical closure pending.

## Implemented

- added machine authorization authority `authorization-contract.json`;
- added stable `Capability` Gate-name enum;
- added singleton `AuthorizationMatrix`;
- removed duplicated capability methods from `UserRole` and `User`;
- AppServiceProvider registers every capability through one Gate loop;
- privileged role/activation mutations use distinct Laravel Gate capabilities;
- preserved last-active-SuperAdmin transactional protection as a business invariant;
- migrated admin/observability consumers away from legacy role capability methods;
- added Unit/Feature authorization matrix/Gate coverage;
- registered authorization in repository compiler and verification consumer graph;
- REG-037 records authorization capability duplication.

## Closure rule

No Stage 16.6 closure is claimed until the exact target completes `songchart verify`.


## Focused verification

Packaging-side exact-tree checks passed:

```text
authorization matrix probe            PASS
PHP syntax                            PASS
auth:verify                           PASS
queue infrastructure authorization   PASS
Pulse authorization                  PASS
privileged audit contract             PASS
repository compiler                   PASS
verification consumer graph           PASS — 0 ownership violations
verifier execution ownership          PASS — 0 violations
regression ledger                     PASS — 37 guarded classes
candidate contract                    PASS
authority dependency closure          PASS
impact map                            PASS
AI development protocol               PASS
verification topology                 PASS
code-generation guardrails            PASS
documentation                         PASS
official-source governance            PASS
source verification                   PASS
legacy role-capability scan           PASS — 0 violations
```

The authorization impact probe resolves the machine contract and privileged user administration service to the `authorization` authority and its full registered consumer set.

Not claimed packaging-side: locked Pint/PHPStan, Laravel container Gate Feature tests, PostgreSQL Feature suite, frontend build, or Docker canonical closure. Those remain target-owned by `songchart verify`.


## v26.1 corrective — Laravel alignment consumes authorization authority

The v26 canonical run failed before runtime/database tests because `verify-laravel-alignment.php` still required the old implementation literal `Gate::define('access-admin')`.

v26.1 corrects ownership rather than restoring the old implementation:

- `can:access-admin` remains the route-level named Gate contract;
- `Capability::AccessAdmin` owns the stable Gate name;
- `authorization-contract.json` owns which roles receive it;
- `AppServiceProvider` registers all Gates through `Capability::cases()` + `AuthorizationMatrix`;
- Laravel alignment verifies those boundaries rather than a direct `Gate::define(...)` line;
- the `foundation-alignment` verification rule now consumes the `authorization` semantic authority;
- alignment verifier/test are registered authorization consumers;
- a consumer-graph literal boundary forbids future verifier/Architecture assertions against direct named Gate registration.

REG-038 records alignment/authorization consumer drift.


### v26.1 focused closure

Packaging-side exact-tree checks passed:

```text
alignment:verify                         PASS
authorization hardening                 PASS
repository compiler                     PASS
consumer graph violations               0
verifier execution-owner violations     0
foundation-alignment owner              authorization + stack-manifest + repository-compiler
regression ledger                       PASS — 38 guarded classes
candidate contract                      PASS
authority dependency closure            PASS
impact map                              PASS
AI protocol                             PASS
verification topology                   PASS
code-generation guardrails              PASS
documentation                           PASS
source verification                     PASS
PHP syntax                              PASS
```

Impact resolution now explicitly lists `verify-laravel-alignment.php` and `LaravelFeatureAlignmentTest.php` as authorization consumers. No direct named `Gate::define(...)` implementation assertions remain in active verifier/Architecture consumers.


## v26.2 corrective — Identity conflict UI consumes authorization authority

The v26.1 canonical run failed because `verify-identity-conflict-review-ui.php` still required the old direct `Gate::define('manage-identity-conflicts')` implementation.

v26.2 keeps the route contract `can:manage-identity-conflicts` and verifies ownership through:

- `Capability::ManageIdentityConflicts`;
- `authorization-contract.json` role matrix;
- shared `Capability::cases()` + `AuthorizationMatrix` Gate registration;
- existing Feature behavior proving SystemOperator is forbidden while review-capable roles are allowed.

The identity-conflict UI verifier, Architecture test and Feature test are now registered authorization consumers.

REG-039 records identity-conflict UI authorization consumer drift.


### v26.2 focused closure

Packaging-side exact-tree checks passed:

```text
identity-conflict-ui:verify              PASS
authorization hardening                 PASS
Laravel alignment                       PASS
repository compiler                     PASS
consumer graph                          PASS
regression ledger                       PASS — 39 guarded classes
candidate contract                      PASS
authority dependency closure            PASS
impact map                              PASS
code-generation guardrails              PASS
verification topology                   PASS
source verification                     PASS
PHP syntax                              PASS
```

The touched `IdentityConflictReviewUiTest` was also brought into the current Pest/Laravel helper convention (`Tests\TestCase` import + closure annotation), preventing the next static guard from failing on an already-affected consumer.


## v26.3 corrective — exact-target removed-symbol closure

The v26.2 canonical run exposed two static-analysis failures:

1. the exact target still contained orphan `app/Http/Middleware/EnsureUserIsAdmin.php`, which called the removed `User::isAdmin()` method;
2. `AuthorizationMatrix::capabilitiesFor()` wrapped `array_map()` with a redundant `array_values()`, rejected by strict PHPStan.

The first failure was not visible in the packaging-side reconstructed source because that artifact did not contain the orphan middleware even though the exact target did. This proves that reconstructed/pre-canonical source cannot establish consumer completeness for a symbol-removal refactor.

v26.3 therefore:

- explicitly retires and backs up `EnsureUserIsAdmin.php` on the target;
- records that legacy middleware as forbidden in `authorization-contract.json`;
- makes `auth:verify` scan the exact source tree for forbidden legacy authorization surfaces and removed methods;
- scans route/Blade Gate names against `Capability`;
- removes the PHPStan-redundant `array_values()` call;
- records an exact-target removed-symbol workflow in the AI development protocol.

REG-040 records authorization removed-symbol / target-inventory drift.


### v26.3 broad source audit

The corrective was followed by a repository-wide audit instead of stopping at the two reported PHPStan errors.

Results in the packaging environment:

```text
PHP files linted                         455 PASS
active verify-*.php scripts              74
packaging-side verifier PASS             65
packaging-environment dependent          9
legacy authorization method references   0 in application source
direct Gate::define outside provider     0
consumer graph violations                0
verifier execution-owner violations      0
regression ledger                        40 guarded classes
```

The 9 verifier failures are environment/evidence dependent, not source failures:

- artifact provenance: candidate is intentionally not canonical-closed;
- canonical PHP extensions: packaging PHP lacks target extensions;
- migration lifecycle: the target-sealed baseline is intentionally absent from reconstructed packaging source;
- migration runtime/test DB safety/package upstream adaptation: `vendor/` is intentionally absent;
- PostgreSQL major/runtime environment: packaging runtime has no authoritative PostgreSQL driver/server;
- release locks: the historical pre-canonical full-source artifact used for reconstruction did not contain the exact target lockfiles.

This audit also confirms why the stale middleware escaped prior packaging checks: the exact target contained a file that the reconstructed source artifact did not. v26.3 treats exact-target inventory as authoritative for removed-symbol closure.


## Canonical closure — v26.3

Status: **CLOSED**.

The exact Stage 16.6 v26.3 target completed Docker canonical verification successfully:

```text
[SongChart verify] Canonical verification PASSED.
[SongChart verify] PASSED.
```

Stage 16.7 starts from this canonical-closed authorization baseline.
