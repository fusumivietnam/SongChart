# Stage 16.4.2 Validation Report

Status: implementation/static-governance candidate. Canonical Docker closure is intentionally not claimed in the packaging environment.

Implemented:
- Docker Compose verification environment with PHP 8.5, Node 22, PostgreSQL 18.4 and Redis
- isolated named volumes for Composer `vendor/`, frontend `node_modules` and Composer cache
- Windows PowerShell/BAT and Linux/WSL entry points
- PostgreSQL major-18 runtime verifier
- PostgreSQL-major awareness in runtime environment and `songchart:doctor`
- CI PostgreSQL service moved from major 17 to major 18
- candidate evidence recorder and extended candidate closure contract
- active stack/project authorities updated to PostgreSQL 18
- static verifier and architecture tests

Packaging environment limitation:
- Docker CLI: unavailable
- Composer: unavailable
- packaging PHP: 8.4

Therefore this environment can validate source contracts but cannot execute the canonical container. `candidate-verification.json` remains `closure_ready=false` until the target canonical verification succeeds.

## Packaging-environment evidence

Passed:
- PHP syntax for Stage 16.4.2 PHP files
- Compose YAML structural parse
- reproducible-environment static verifier
- package governance
- Unit-test taxonomy
- candidate contract
- engineering governance
- Pulse/queue governance
- documentation and repository-state verification
- schema ownership and impact-map verification
- official-source and authority-dependency verification
- code-generation guardrails
- PostgreSQL-only database authority static verification
- CI configuration
- Laravel alignment
- source-package verification

Not run here:
- Docker image build / Compose runtime
- Composer install
- locked Pint
- Larastan/PHPStan
- Pest
- PostgreSQL 18 runtime connection
- Redis runtime
- npm build
- `composer release:verify`

## v16.1 corrective — PHP 8.5 core-extension build contract

The first real Docker build exposed a verification-image defect that static packaging checks could not detect: the Dockerfile attempted to rebuild `curl`, `dom`, `mbstring`, and `xml` from the PHP 8.5 source tree. Rebuilding `dom` failed because PHP 8.5 DOM now depends on Lexbor headers that are not available through the extracted standalone extension build path used by `docker-php-ext-install`.

v16.1 removes those core extensions from the compile list, keeps only SongChart-required extensions that must be added to the base image, adds a build-time `extension_loaded()` smoke check, adds a reusable runtime smoke verifier, and makes the static governance verifier reject future attempts to rebuild those core extensions.

Canonical Docker closure remains required before `closure_ready=true`.
