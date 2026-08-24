# Static Analysis and Formatting

- Laravel Pint owns PHP formatting.
- Larastan/PHPStan owns static type validation.
- `declare(strict_types=1);` is required for project PHP files.
- Do not weaken global analysis settings to hide a local type error.
- Prefer explicit casts, enum casts, typed DTOs and narrow return types.
- Suppressions require a documented framework limitation and the smallest possible scope.
- Run `composer quality:normalize` before `composer quality:verify`.

## New-code and vendor-derived policy

New stage errors must be fixed rather than added to the PHPStan baseline. Vendor-derived code committed into SongChart, including published package migrations/config, is repository code for quality purposes and must pass the canonical Pint/Larastan gates. When an upstream type is broader than supported runtime values, adapt fail-closed with an explicit exhaustive fallback rather than weakening analysis.
