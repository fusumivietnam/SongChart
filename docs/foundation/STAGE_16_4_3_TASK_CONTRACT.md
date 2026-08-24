# Stage 16.4.3 — Docker Local Development & Trusted HTTPS Profile

## Authority and official sources

### Repository authorities

Stage 16.4.2 canonical verification, PostgreSQL 18 authority, Laravel-native-first rules, lockfile authority, Laragon support, and release gates remain authoritative.

### Installed versions

- PHP 8.5
- Laravel 13
- PostgreSQL 18.4 Docker reference; release major remains exactly 18
- Redis 7.4
- Node 22
- Caddy 2.11.3
- Docker Desktop / WSL2 host
- mkcert host certificate tooling

### Official external sources

- Docker Compose service dependencies support `condition: service_healthy`.
- Docker published ports can be restricted to a specific host IP.
- Docker named volumes persist independently from containers.
- Caddy supports explicit PEM certificate/key pairs and HTTP reverse proxying.
- Laravel 13 documents HTTPS considerations for local Vite development.
- mkcert installs a locally trusted development CA and generates certificates for `.test` hosts.

### Native capability assessment

Docker Compose owns service orchestration and persistence. Caddy owns reverse proxy/TLS serving. mkcert owns local certificate issuance/trust. Laravel owns application serving and proxy interpretation. SongChart only wires these capabilities into a safe local profile.

### Custom implementation justification

Custom scripts are limited to repeatable Windows setup/lifecycle, hosts/certificate bootstrap, environment generation and governance checks. No custom TLS, proxy, container runtime or certificate authority implementation is introduced.

## Objective

Provide a browser-testable Docker development site with trusted HTTPS while keeping Laragon available simultaneously and keeping canonical verification separate.

## Scope

- `compose.dev.yml`
- Caddy HTTPS reverse proxy
- `docker.songchart.test`
- host ports 8080/8443 to avoid Laragon conflicts
- Windows mkcert/hosts bootstrap
- `.env.docker.example`
- Docker-local APP_KEY generation
- persistent Docker development PostgreSQL/Redis/dependency volumes
- queue worker
- local-only trusted proxy handling
- one-click setup/up/down entry points
- governance verifier/tests/docs

## Non-goals

- replacing Laragon
- production deployment topology
- public certificates/ACME for `.test`
- claiming host 80/443 by default
- sharing Laragon development database
- secure Vite HMR
- committing certificates, private keys or `.env.docker`

## Expected files

Docker dev composition, Caddy config, local environment example, setup/lifecycle scripts, proxy bootstrap change, static verifier, architecture tests, docs and stack authority updates.

## Allowed incidental files

README, documentation index/history, impact map, package registry, Composer scripts, gitignore and candidate metadata.

## Scope deviations

Stage 16.4.2 runtime closure was reported by the target as `Canonical verification PASSED`; the exact target-generated evidence file is not reproduced or fabricated in this packaging environment.

## Tests and verification

Packaging must pass PHP syntax, YAML parsing and static repository governance. Target closure additionally requires setup/up checks and successful HTTPS response from `https://docker.songchart.test:8443`.
