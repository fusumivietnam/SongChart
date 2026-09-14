# Package Registry

This document explains package-governance ownership. It intentionally does not duplicate package versions or status rows from machine-readable authorities.

## Authorities

- `package-registry.json` is the package-governance authority for Composer packages, runtime package policy, deployment profiles, planned package decisions, and external tools.
- `composer.json` declares Composer constraints; `composer.lock` owns exact resolved Composer versions.
- `stack-manifest.json` owns required, restricted, and forbidden frontend/package capability policy.
- `package.json` declares Node package constraints; `package-lock.json` owns exact resolved Node versions.

When these sources disagree, fix the declaring machine authority rather than copying the disagreement into another document.

## Package status semantics

- `required`: application/runtime capability owner; removal requires an explicit migration or retirement decision.
- `required-dev`: mandatory development, quality, build, or verification capability.
- `approved`: permitted within its documented runtime scope.
- `approved-dev`: permitted for development or test use only.
- `restricted`: not a default capability owner; use only when existing Laravel, Livewire, or browser/platform boundaries cannot satisfy the requirement and the stack authority permits it.
- `experimental`: not allowed in production paths without explicit approval.
- `deprecated`: no new usage; existing usage requires a retirement path.
- `forbidden`: must not be installed or referenced.

## Ownership rules

- No package may silently take ownership from an existing capability owner.
- Prefer framework/platform capabilities and existing approved owners before adding a package.
- A package addition is incomplete until its declaring manifest and governing machine authority agree.
- Exact resolved versions belong only in lockfiles.
- Runtime database authority remains PostgreSQL major 18 and is governed by `package-registry.json` even though PostgreSQL is not a Composer package.

Use the machine-readable authorities above for current package inventory and constraints.
