# Normalization Validation and Quarantine

Stage 14.2 validates typed normalized provider entities before canonical mutation.

## Authority

- Validation runs after adapter normalization and before matching or mutation.
- Invalid items become `quarantined`; they do not fail the entire import run.
- Every validation issue is persisted in `provider_import_failures` with stage `validation`.
- Quarantined items retain normalized output and issue details for audit and replay.
- Retry is explicit and only allowed for quarantined items.

## Initial failure taxonomy

`required-field`, `invalid-value`, `invalid-identifier`, `invalid-relationship`, `unsupported-entity`, `unsafe-url`, and `payload-too-large`.

The default validator enforces envelope identity, identifier namespaces, relationship targets, HTTPS-only normalized URLs, bounded strings, and a one MiB normalized entity boundary. Provider-specific validators may extend these rules in later stages, but must remain behind `NormalizedProviderEntityValidator`.
