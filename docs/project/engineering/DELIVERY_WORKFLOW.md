# SongChart Delivery & Upgrade Workflow

Status: mandatory delivery authority for implementation stages and corrective candidates.

## Source-of-truth rule

GitHub is the only development handoff and upgrade source of truth.

- One stage uses one stage branch.
- Logical implementation/corrective slices are commits on that branch.
- Device or AI handoff happens through committed/pushed Git state, never source ZIP copying.
- A stage closes through one pull request into `main` after exact-tree candidate and canonical verification.
- Tags/releases are created only from an accepted `main` tree.

Do not create incremental source ZIPs, full Laragon-ready ZIPs, patch installers, or host-specific apply scripts for normal development handoff.

## Stage workflow

```text
accepted main baseline
    ↓
stage branch
    ↓
logical commits + focused verification
    ↓
push to GitHub / Draft PR when useful
    ↓
exact-tree candidate preparation + stage verification
    ↓
canonical verification
    ↓
PR CI green
    ↓
merge to main
    ↓
release/tag when required
```

Git history is the rollback and provenance mechanism. Do not reconstruct target state from copied folders or packaging trees.

## Device and AI handoff

Before switching device or execution agent:

1. inspect `./songchart ai status`;
2. commit the intended logical state;
3. push the current stage branch;
4. on the next environment, fetch and fast-forward/reset only after reviewing divergence;
5. use `./songchart ai doctor` for runtime/test evidence that is not represented in Git.

Never use cross-device stash as a handoff mechanism.

## Verification

During iteration use focused verification, including:

```bash
./songchart dev test <test-path>
```

Stage candidate closure:

```bash
./songchart candidate --prepare   # only when generated authority needs refresh
./songchart candidate
```

Canonical closure after candidate PASS:

```bash
./songchart verify
```

Do not duplicate stage/canonical gates around these governed entrypoints.

## Release artifacts

`composer release:package` remains a post-canonical release/deployment artifact command. It is not a development handoff mechanism and must not be used to synchronize devices or working copies.

Release artifacts must never contain local secrets, `.env`, `.env.docker`, private certificates, database volumes, `vendor/`, `node_modules/`, logs, or mutable local runtime state unless an owning release contract explicitly requires a generated artifact.

## Corrective candidates

A same-stage correction remains on the current stage branch as a logical commit unless governance explicitly requires a separate revision. Re-run only the affected focused gates during iteration, then re-run exact-tree candidate/canonical closure before merge.

## Native Windows workflow

Native Batch/PowerShell development wrappers are retired. Windows development, when needed, uses WSL2/Linux with Docker and the same `./songchart` entrypoint as every other supported development environment.
