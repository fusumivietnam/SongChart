# Local Development Setup

SongChart uses Linux/WSL2 + Docker Engine with Compose v2 as the primary local development runtime. GitHub Codespaces is the preferred remote adapter. Host PHP, Composer, Node.js, PostgreSQL and Redis are not required.

## Golden path

For normal development, use one command:

```bash
./songchart dev ready
```

`dev ready` is convergent. When `.env.docker` is missing it automatically delegates to the bootstrap flow, then brings the persistent development runtime to a usable state. On later runs it reuses the existing configuration, PostgreSQL data, local administrator account and dependency/browser caches where their fingerprints still match.

You may explicitly run:

```bash
./songchart dev setup
```

`dev setup` is bootstrap-only. If a usable `.env.docker` already exists, setup must not reinstall/reset the development environment; it preserves existing local state and delegates to `dev ready`.

## Persistent development state

Normal `dev ready`, `dev up`, `dev down` and repeated `dev setup` calls must preserve:

- the `songchart_dev_pgdata` PostgreSQL volume and development data;
- the configured local administrator identity and existing credentials;
- Redis development state;
- Composer and npm dependency volumes when lock fingerprints match;
- the Playwright browser runtime when its fingerprint matches.

No normal setup/ready command may run `migrate:fresh`, drop/recreate the development database, delete development volumes, or reset the local administrator password. Any future destructive reset must be an explicit separately named command with an intentional operator confirmation contract.

The development database is `songchart_docker`. Verification uses the isolated `songchart_verify_test` database and must not mutate development data.

## Useful commands

```bash
./songchart dev ready
./songchart dev status
./songchart dev url
./songchart dev logs
./songchart dev db backup
./songchart dev db restore
./songchart dev down
```

For focused verification use:

```bash
./songchart impact --verify
./songchart dev test <test-path>
```

For stage/canonical closure use the governed verification entrypoints rather than the development database:

```bash
./songchart test
./songchart verify
```

Native Windows Batch/PowerShell and Laragon execution paths are retired. Use WSL2/Linux and the `./songchart` CLI.
