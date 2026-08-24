# Code Generation & Static-Analysis Guardrails

Status: mandatory implementation authority.

## Purpose

Prevent recurring code-generation defects at Laravel/Pest/Eloquent/static-analysis boundaries before a changeset is packaged.

## Mandatory rules

### Pest TestCase context

Any Pest file using Laravel helpers through `$this` must import `Tests\TestCase` and each relevant closure must declare `/** @var TestCase $this */`. Avoid fully-qualified PHPDoc types when Pint's `fully_qualified_strict_types` rule would normalize them. Do not suppress `method.notFound` or `property.protected` findings.

### Static-analysis-safe Pest assertions

Changed Pest tests must not use Pest's dynamic `->not` expectation property because Larastan/PHPStan may model it as an undefined property. Express negative invariants with statically visible boolean predicates, for example `expect(str_contains($source, 'token'))->toBeFalse()` or `expect(in_array($value, $items, true))->toBeFalse()`. Do not use `is_subclass_of(KnownClass::class, KnownParent::class)` for a relationship PHPStan can prove at analysis time; when the inheritance relationship itself is the architecture contract, inspect it through `ReflectionClass` or another non-tautological repository boundary.


For reflection assertions, `ReflectionClass::getParentClass()` returns `ReflectionClass|false`; branch explicitly on `false` before calling methods. Do not use nullsafe access (`?->`) on this sentinel union in changed tests.

### Persistence enum boundary

Do not compare an Eloquent attribute that static analysis models as a scalar directly with a backed enum instance. At persistence boundaries, read the persisted scalar with `getRawOriginal()` and convert explicitly with `Enum::from()` / `tryFrom()`. Domain services may use enums; presentation/read DTOs must expose intentional scalar or presentation values.

### Datetime boundary

Do not call Carbon/DateTime methods on an attribute unless its static type proves an object. Normalize dynamic Eloquent attributes through an explicit accessor, DTO, or `getAttribute()` plus `DateTimeInterface` check.

### Iterable value types

Every array/list PHPDoc used by production or verifier code must include value types (`array<string, mixed>`, `list<string>`, or a precise array shape). Do not add analyzer ignores for `missingType.iterableValue`.

### Dead-code cleanup

When a refactor removes the final caller of a private helper/property/import, remove the dead symbol in the same change. Do not keep speculative helpers "for later".

### Semantic UI assertions

Role, navigation, authorization and structural UI tests must use stable semantic attributes (`data-admin-nav`, `data-work-item`, route/middleware state, canonical URLs) rather than page-wide negative text assertions. Human text is asserted only when wording itself is the acceptance contract.

### Framework magic boundary

Do not assume static analysis will infer framework magic for Eloquent casts, Pest `$this`, `DB::select()` row shapes, environment values, or route collections. Normalize/type these boundaries explicitly.


### Authority dependency closure

A stage that changes a repository authority MUST list that authority under `Changed authorities` in its task contract. `docs/project/governance/authority-dependencies.json` is the dependency registry. Every registered authority file and dependent must be declared in the stage change surface and checked for superseded invariants before packaging. Do not patch only the first failing legacy test.

### Contradiction scan

Before packaging, scan current executable surfaces for deprecated authority tokens registered in `authority-dependencies.json`. Historical stage records are not rewritten; current tests, verifiers, CI, runtime configuration and current design/testing authorities must not retain superseded invariants.

## Change-impact preflight

Before implementation, identify every changed authority, resolve its registered dependents, then search all affected identifiers across source, tests, executable contracts and documentation. A new route/action/state must reconcile superseded legacy assertions in the same stage.

## Packaging preflight

Before a changeset is packaged, the changed surface must have:

1. PHP syntax verification.
2. Pint on changed PHP files (or repository Pint when practical).
3. PHPStan/Larastan on changed PHP files and directly affected tests.
4. Focused tests from the impact map.
5. Executable contract/verifier checks for the changed authority.
6. Authority-dependency closure and contradiction scan.

If target dependencies are unavailable in the packaging environment, validation documentation must state the unperformed gates explicitly; syntax-only evidence is never equivalent to PHPStan/Pest evidence.

### Changeset focused-gate completeness

Changeset installers MUST derive the focused PHP file set from every `*.php` file present under `payload/`. Do not maintain a handwritten PHP file list for Pint or PHPStan. The same derived set MUST be syntax-checked, formatted, style-tested, and statically analysed before focused runtime tests. This prevents a changed PHP file from bypassing focused gates and failing only during the full release gate.
