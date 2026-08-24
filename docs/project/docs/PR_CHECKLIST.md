# Pull Request Checklist

- [ ] Scope matches task.
- [ ] No unrelated refactor.
- [ ] Architecture/module ownership respected.
- [ ] Validation and authorization included.
- [ ] Migration has constraints/indexes/rollback.
- [ ] Tests cover success and failure.
- [ ] Pint/static analysis pass.
- [ ] Provider policy reviewed where applicable.
- [ ] SEO/accessibility reviewed where applicable.
- [ ] Logs contain no secrets.
- [ ] Documentation updated.

## Documentation governance

- [ ] Read `docs/DOCUMENTATION_GOVERNANCE.md` and the owning module authorities.
- [ ] Updated an existing authority instead of creating a duplicate responsibility.
- [ ] Added an ADR for architecture, dependency, ownership, provider policy or public URL decisions.
- [ ] Updated task contract, implementation status and change manifest where applicable.
- [ ] Listed verification that could not be executed.
- [ ] `composer docs:verify` passes.
- [ ] Delivery contains full source and change-set packages with deletion and Windows scripts.
