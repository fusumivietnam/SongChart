# SongChart Delivery & Upgrade Workflow

Status: mandatory delivery authority for implementation stages and corrective candidates.

## Artifact rule

Every implementation stage or same-stage corrective candidate must publish **both** artifacts from the exact same source tree:

1. **Full Laragon-ready source ZIP** — recovery/bootstrap artifact with project files at ZIP root. It must exclude local secrets and generated dependency/runtime state such as `.env`, `.env.docker`, `vendor/`, `node_modules/`, logs, certificates, and compiled caches.
2. **Incremental changeset ZIP** — preferred upgrade artifact from the immediately preceding accepted/canonical baseline. It must contain only changed/added files, an explicit removal manifest, a human-readable manifest, and an apply script.

The changeset is the normal fast-path. The full source ZIP is the recovery path when the target tree is missing, corrupted, or not on the declared baseline.

## Changeset invariants

A changeset must:

- declare the exact `from` baseline and `to` stage/candidate;
- never contain or overwrite `.env`, `.env.docker`, private certificates, database volumes, `vendor/`, or `node_modules/`;
- preserve relative repository paths under a dedicated `files/` directory;
- list deleted repository paths explicitly in `REMOVE_FILES.txt` even when the list is empty;
- copy files first and remove only declared obsolete paths;
- fail closed when the project root does not contain `artisan` and `PROJECT_AUTHORITY.md`;
- make a timestamped backup of overwritten/deleted target files before mutation;
- not run host PHP/Composer/Node/PostgreSQL as release authority;
- when verification is requested during iteration, call `stage-verify.bat`; for final canonical closure call `verify-songchart.bat` directly because canonical verification already includes stage closure. Do not require both commands back-to-back; never substitute host `composer stage:verify`;
- be generated only after the implementation tree has passed the focused/static checks available to the authoring environment.

## Stage workflow

For every stage:

```text
accepted baseline
    ↓
implement directly on one source tree
    ↓
focused/static verification
    ↓
generate incremental changeset from accepted baseline
    ↓
generate full Laragon-ready ZIP from the same tree
    ↓
apply changeset on target (preferred)
    ↓
stage-verify.bat while iterating (as needed)
    ↓
manual web/admin smoke test when UI/runtime changed
    ↓
verify-songchart.bat for final closure
    ↓
canonical internally reruns stage:verify
    ↓
canonical PASS → next baseline
```

Do not create a changeset from a packaging tree that differs from the full-source artifact. Do not continue to the next stage until the target canonical result is known, unless the user explicitly asks for an exploratory branch.

## Corrective candidates

A correction to the current stage uses a suffix/revision (`R2`, `v2`, etc.) and must also publish both artifacts. Its changeset baseline is the last artifact the user is known to have applied, not an assumed local tree.

## Windows fast path

From the SongChart project root, apply the stage changeset:

```powershell
powershell -ExecutionPolicy Bypass -File <changeset>\apply.ps1 -ProjectRoot (Get-Location).Path
```

Then use the Docker-first wrapper that matches the task:

```bat
:: iterative candidate gate
stage-verify.bat

:: final closure; run this by itself when ready
verify-songchart.bat
```

`verify-songchart.bat` is a self-contained superset and invokes the Composer stage gate internally. Running both commands consecutively is optional, not required.

The `.env` retained in the Laragon project directory is local state and must not be supplied by release artifacts.

## Changeset post-apply integrity

Every incremental changeset MUST verify the exact target tree after copying files. At minimum it must:

- confirm `README.md` resolves to the intended stage/candidate;
- confirm each task contract touched by the changeset exists in the target tree;
- validate required governance headings when the corrective concerns contract evidence;
- fail before Docker verification if copied-file integrity does not match the packaged payload.

A successful copy operation alone is not sufficient evidence that an upgrade was applied correctly.

## Current-stage governance pair

Every changeset must ship and post-verify both the current-stage task contract and validation report before reporting success.
