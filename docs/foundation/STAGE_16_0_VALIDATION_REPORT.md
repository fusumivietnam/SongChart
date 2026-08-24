# Stage 16.0 — Discovery Domain Contracts — Validation Report

## Acceptance criteria

- Discovery is an explicit read-oriented domain over canonical entities.
- Provider API and canonical mutation dependencies are prohibited from the Discovery domain.
- Rules are typed, field-registry constrained and raw-SQL-free.
- Channel modes, state, publication, placements and versioned projection contracts are machine-readable.
- PostgreSQL owns bounded Discovery storage with JSONB only for flexible rule/presentation/projection payloads.
- Public request execution of Discovery rules is explicitly prohibited.
- Candidate v9 AdminDashboardTest static-analysis correction is preserved.

## Evidence matrix

| Lane | Command | Result | Evidence environment |
|---|---|---|---|
| PHP syntax | `php -l` for changed PHP | passed | packaging environment |
| JSON authority | decode modified JSON authorities | passed | packaging environment |
| Discovery contracts | `php scripts/verify-discovery-domain-contracts.php` | passed | packaging environment |
| Domain contracts | `php scripts/verify-domain-contracts.php` | passed | packaging environment |
| Schema ownership | `php scripts/verify-schema-ownership.php` | passed | packaging environment |
| Documentation | `php scripts/verify-documentation.php` | passed | packaging environment |
| Repository state | `php scripts/verify-repository-state.php` | passed | packaging environment |
| Impact map | `php scripts/verify-impact-test-map.php` | passed | packaging environment |
| Pint | target command | not yet run | target dependencies required |
| Larastan/PHPStan | target command | not yet run | target dependencies required |
| PostgreSQL Pest | focused/full suites | not yet run | `songchart_test` required |
| Full release | `composer release:verify` | not yet run | Laragon target required |

## Additional packaging evidence

- `php scripts/verify-code-generation-guardrails.php`: passed.
- `php scripts/verify-authority-dependencies.php`: passed after restoring the baseline-governed GitHub Actions workflow omitted by the Closure v8 archive.
- `php scripts/verify-database-authority.php`: passed.
- `php scripts/verify-runtime-authority-closure.php`: passed.
- `php scripts/verify-ci-configuration.php`: passed.
- `php scripts/verify-performance-baseline.php`: passed.
- `php scripts/verify-source-package.php`: passed in working-tree mode.
- Discovery domain smoke test (typed rules + channel activation/revision): passed.
- Delivery-mode source verification cannot be claimed from the supplied Closure v8 baseline because that archive does not contain `composer.lock` or `package-lock.json`, both mandatory under the existing delivery verifier. v10 does not fabricate lockfiles.

## Security and authorization review

No route or public mutation surface is added. Discovery capability names are declared for future authorization integration. The Discovery domain has architecture guards against provider dependencies, Eloquent coupling and raw SQL rule execution.

## Spec-compliance review

The contract intentionally maps product concepts to the current canonical vocabulary (`recording` instead of a new `track` entity and `release` instead of a duplicate `album` entity). Work and recording-version entities remain outside public Discovery until a later accepted use case requires them.

## Rollback

Revert Stage 16.0 files and roll back the Discovery migration. No canonical or provider data is transformed by this stage.
