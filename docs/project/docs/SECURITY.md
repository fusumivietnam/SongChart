# Security Baseline

- OWASP-oriented input validation and output escaping.
- CSRF protection for browser mutations.
- Secure, HTTP-only, SameSite cookies.
- Password hashing through framework defaults.
- MFA-ready account model.
- OAuth state and PKCE where supported/required.
- Secrets only in environment/secret manager.
- Encrypt sensitive tokens at rest.
- Minimize OAuth scopes.
- Rotate/revoke provider tokens.
- Audit privileged actions.
- Sanitize user-generated HTML or avoid accepting it.
- Validate uploaded MIME/content, not only extension.
- Rate-limit abuse-prone endpoints.
- Dependency and secret scanning in CI.

Security-sensitive decisions require an ADR or threat-model update.
