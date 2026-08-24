# Database Conventions

- PostgreSQL is the development, test-authority, release-validation and production database.
- Local `.env` uses `DB_CONNECTION=pgsql`.
- `composer test`, `composer test:all`, `composer test:feature`, and `composer test:postgres` resolve through the protected PostgreSQL `songchart_test` lane.
- `TEST_PGSQL_DATABASE` never falls back to development `DB_DATABASE`; destructive tests are refused unless the target ends in `_test`, differs from development, and carries the testing environment marker.
- SQLite is compatibility-only through `composer test:sqlite:compat`; it is not CI/release evidence.
- PostgreSQL is authoritative for migrations, constraints, JSON behavior, locking, upsert, query plans and import semantics.
- Canonical entities use internal ULIDs.
- Provider IDs are stored as external identifiers or provider mappings.
- Multi-write invariants use transactions.
- Every schema change includes constraints, indexes, rollback and PostgreSQL tests.
- Foreign-key delete behavior must be intentional.
- Import jobs must be idempotent before bulk ingestion is enabled.


## Release major-version authority

Release verification requires PostgreSQL major version 18 exactly. The canonical verification container currently pins PostgreSQL 18.4; future 18.x minor updates are maintenance changes, while PostgreSQL 19 requires an explicit compatibility review and authority update.
