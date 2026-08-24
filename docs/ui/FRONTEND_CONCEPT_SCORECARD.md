# Frontend Concept Scorecard

Use one row per reviewer.

| Criterion | Weight | Score 1–5 | Notes |
|---|---:|---:|---|
| Understands product in five seconds | 3 | | |
| Search is easy to start | 3 | | |
| Entity types are distinguishable | 3 | | |
| Provider CTA is explicit | 3 | | |
| Metadata remains readable | 2 | | |
| Mobile layout is credible | 3 | | |
| Accessibility risk is low | 3 | | |
| Performance risk is low | 2 | | |
| Components can be reused | 3 | | |
| Works for artist detail | 2 | | |
| Works for release detail | 2 | | |
| Works for recording detail | 2 | | |
| Supports editorial content | 1 | | |
| Visual identity is distinctive | 2 | | |

## Decision rule

Reject any concept scoring below 3 in:

- accessibility;
- mobile;
- search;
- entity clarity;
- provider CTA clarity.

The winning direction should then be converted into:

- shared design tokens;
- frontend shell;
- entity page contracts;
- reusable Blade components;
- visual regression baselines.
