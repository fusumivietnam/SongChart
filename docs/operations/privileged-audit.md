# Privileged Operations & Audit

## Purpose

The privileged audit stream answers: who changed what, when, why, and what state changed.

It is not a replacement for application logs, Pulse, provider ingestion ledgers, queue logs, or technical tracing.

## Package authority

SongChart uses `spatie/laravel-activitylog:^5.0` for the activity storage model, causer/subject relationships, event names, properties, query scopes, and cleanup support.

Automatic broad model-event logging is forbidden. Privileged audit events are emitted explicitly at mutation boundaries.

## Log name

All SongChart privileged business events use:

```text
privileged
```

## Initial audited surfaces

- provider enable/disable/retire
- provider import retry/resume/cancel
- identity-conflict decisions
- extension install/upgrade/enable/disable/theme activation/rollback/cleanup
- user role changes
- user activation/deactivation

## Sensitive-data rules

Never include:
- passwords
- remember tokens
- two-factor secrets
- recovery codes
- API/provider secrets
- OAuth tokens
- raw provider payload bodies
- uploaded package contents

`config/activitylog.php` also excludes authentication-secret attribute names globally.

## Domain ledgers vs cross-cutting audit

`provider_operation_audits` remains the provider mutation idempotency/recovery ledger. `activity_log` is the cross-cutting privileged business audit. They have different responsibilities and neither replaces the other.

## Admin viewer

Operations administrators with `view-audit` may use:

```text
/admin/audit
```

The UI is paginated and filters by event/actor. It does not perform unbounded request-time aggregation.

## Privileged commands

Role change:

```bash
php artisan admin:user:set-role target@example.com editor \
  --actor=superadmin@example.com \
  --reason="Approved editorial role assignment."
```

Activation state:

```bash
php artisan admin:user:set-active target@example.com inactive \
  --actor=operator@example.com \
  --reason="Account disabled after access review."
```

Both commands require an explicit actor and rationale. Role changes require a super administrator. Activation changes require operations capability. Self-deactivation and removing the last active super administrator are blocked.

Use `--yes` only for an already-reviewed non-interactive run.

## Retention

Default retention is 365 days through `ACTIVITYLOG_CLEAN_AFTER_DAYS`. Retention changes require an explicit governance/privacy decision rather than ad-hoc cleanup.


## Focused database test authority

Stage-focused audit Feature tests must run through the canonical PostgreSQL lane with explicit schema preparation:

```text
php scripts/run-database-tests.php postgres --prepare-schema tests/Feature/PrivilegedAuditTest.php
```

`--prepare-schema` is destructive only to the isolated test database after the database-safety guard has confirmed development/test name isolation. It must not be pointed at the development database.


## Activitylog v5 schema adaptation

SongChart uses the Spatie v5 activity table contract but adapts morph identifiers for ULIDs. The required storage surface includes both:

```text
attribute_changes  JSON nullable
properties         JSON nullable
```

`attribute_changes` is package-owned state used by the v5 `Activity` model even when SongChart emits explicit business audit events rather than broad automatic model logging. It must not be removed from the adapted migration.
