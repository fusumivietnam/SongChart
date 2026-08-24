# Stage 12.4 Task Contract — PostgreSQL-First Test Matrix & Database Authority Closure

## Goal
Make PostgreSQL the mandatory development and release authority while retaining SQLite only as a fast compatibility lane.

## Acceptance criteria
- Base PHPUnit configuration does not force SQLite.
- Explicit SQLite and PostgreSQL Composer commands exist.
- Release verification runs both lanes.
- PostgreSQL credentials are supplied through `TEST_PGSQL_*` or `DB_*`.
- CI uses the explicit lane commands.
- Project setup no longer creates `database/database.sqlite`.
- Database authority is executable through `composer database:verify`.

## Non-goals
No catalog schema, provider adapter, import pipeline or package changes.
