# Generated Feature Matrix

> Generated from repository authorities and Laravel route registry. Do not edit manually.

| Journey | Journey state | Implemented route surfaces | Contract-mapped use cases | Contract gaps | Explicit product/domain gaps |
| --- | --- | ---: | ---: | ---: | ---: |
| `visitor.discover` | `implemented` | 0 | 15 | 0 | 2 |
| `visitor.follow_destination` | `partial` | 0 | 5 | 0 | 2 |
| `member.manage_account` | `partial` | 3 | 0 | 3 | 2 |
| `editor.ingest_and_admit` | `implemented` | 4 | 0 | 4 | 3 |
| `operator.observe_and_recover` | `implemented` | 1 | 0 | 1 | 2 |

## Implemented surfaces with missing executable use-case mapping

- `member.manage_account` -> `account.overview` — surface `GET|HEAD /account` exists, but executable use-case mapping is missing.
- `member.manage_account` -> `account.profile` — surface `GET|HEAD /account/profile` exists, but executable use-case mapping is missing.
- `member.manage_account` -> `account.security` — surface `GET|HEAD /account/security` exists, but executable use-case mapping is missing.
- `editor.ingest_and_admit` -> `admin.canonical-admissions.index` — surface `GET|HEAD /admin/canonical-admissions` exists, but executable use-case mapping is missing.
- `editor.ingest_and_admit` -> `admin.canonical-admissions.show` — surface `GET|HEAD /admin/canonical-admissions/{admission}` exists, but executable use-case mapping is missing.
- `editor.ingest_and_admit` -> `admin.canonical-admissions.stage` — surface `POST /admin/canonical-admissions/assertions/{assertion}` exists, but executable use-case mapping is missing.
- `editor.ingest_and_admit` -> `admin.canonical-admissions.decide` — surface `POST /admin/canonical-admissions/{admission}/decisions` exists, but executable use-case mapping is missing.
- `operator.observe_and_recover` -> `admin.audit.index` — surface `GET|HEAD /admin/audit` exists, but executable use-case mapping is missing.

## Unknown declared entrypoints

- None.
