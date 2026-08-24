# GitHub Copilot Instructions

Follow `AGENTS.md` and the documents under `docs/`.

Generate code that:
- uses strict types;
- follows Laravel conventions;
- keeps controllers thin;
- places business rules in Actions/Services inside the owning module;
- validates input with Form Requests;
- authorizes mutations with Policies;
- uses DTOs at provider boundaries;
- adds tests for observable behavior;
- avoids speculative abstractions and hidden side effects.
