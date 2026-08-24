# Normalized Provider DTOs

Stage 14.1 replaces free-form normalized attribute arrays with typed provider-neutral DTOs.

Every field uses `ProviderField` and one explicit presence state:

- `missing`: the provider payload did not contain the field.
- `unknown`: the provider explicitly communicates that the value is unknown.
- `explicit_null`: the provider supplied the field with a null/cleared value.
- `provided`: the provider supplied a non-null value.

`null` must never be used to collapse these meanings. Entity DTOs currently cover artist, work, recording, and release. Identifiers and relationships are typed DTOs. These objects remain independent from Eloquent and do not perform canonical mutations.
