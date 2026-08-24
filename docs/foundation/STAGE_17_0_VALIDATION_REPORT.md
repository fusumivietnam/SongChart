# Stage 17.0 Validation Report

Status: implementation validation in the supplied source tree.

## Implemented

- reconciled roadmap so already-delivered provider mutation/recovery is no longer scheduled as future Stage 16.8 work;
- moved current-stage pointer to Stage 17.0;
- decomposed the application composition root into focused Laravel service providers;
- removed direct service-container resolution from admin operations Blade views;
- converted provider chooser to a class-based Blade component with injected destination policy;
- added `/development/design-system` as the canonical internal design-system route family while retaining legacy aliases;
- normalized the malformed ExtensionInstaller method formatting;
- documented extension subsystem freeze and foundation closure rules;
- removed eleven obsolete Stage 04–10 delivery-only batch scripts after confirming no repository consumers;
- removed generated Blade compiled-view cache files from the supplied source tree;
- expanded `.gitignore` so local `.env`, Composer/npm dependencies, generated frontend output, runtime caches, PHPUnit cache and IDE/OS noise do not become repository source.

## Environment limits

The supplied analysis runtime is not the authoritative SongChart runtime. Docker Desktop/WSL2 is the primary SongChart runtime; host PHP/Composer/Node/PostgreSQL/Redis are not release-authoritative. Canonical candidate evidence must be regenerated through the Docker entrypoints after these changes. This report does not claim PostgreSQL, PHPStan, Pint, Pest, stage, or canonical PASS until those Docker commands complete.


## Corrected Windows execution authority

- `stage-verify.bat` delegates to `songchart.bat test`, which runs Stage closure inside `compose.verify.yml`.
- `verify-songchart.bat` delegates to `songchart.bat verify`, which runs canonical Docker verification and candidate evidence.
- `composer stage:verify` is an internal container closure command and should not be invoked from host Windows as the normal project workflow.
- Historical `scripts/*.bat` compatibility/delivery shims are retained; Stage 17.0 does not remove them merely because they are old.

## R3 corrective verification

A full static verification sweep of the Laragon-ready R3 artifact exposed packaging and authority-drift issues that the initial focused checks did not cover. The corrected candidate now:

- restores `.github/workflows/tests.yml` and the required `.agents/skills/*/SKILL.md` authority surfaces in the deployment artifact;
- updates auth, discovery, search, provider, Horizon/Pulse and related architecture verifiers/tests to follow the focused service-provider ownership introduced by Stage 17.0 instead of hard-coding `AppServiceProvider`;
- adds the required official-source sections to the current-stage task contract;
- refreshes the executable repository contract graph against the exact corrected source tree;
- guarantees `songchart test` tears down the ephemeral verification stack through `finally`, including failure paths;
- retains the PostgreSQL 18 development volume at `/var/lib/postgresql` and runtime-directory bootstrap before Composer installation.

Static validation on the corrected tree passed PHP syntax across 504 PHP files and passed the repository verifiers for Docker local development, documentation, repository state, Docker-first authority, candidate contract, verification topology/command surface, CI configuration, database authority, foundation closure, authentication, Laravel alignment, discovery rules, provider catalog/canonical mutation, queue/Horizon/Pulse authorization, official-source governance, reproducibility and repository contract compilation.

Target Docker execution (`stage-verify.bat` and `verify-songchart.bat`) remains required before `closure_ready` may become true.
