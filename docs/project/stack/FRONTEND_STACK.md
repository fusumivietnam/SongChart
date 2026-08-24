# Frontend Stack

## Rendering ownership

- Blade is the default rendering surface.
- Livewire is for meaningful server-backed interaction.
- Alpine.js is for local, ephemeral UI state.
- Vite owns asset compilation.
- Tailwind and semantic design tokens own styling.

## Restrictions

- Do not introduce another SPA framework or bundler without an ADR.
- Do not call provider APIs directly from browser feature code.
- Axios is restricted to approved browser transport cases; prefer forms, Livewire or the browser platform first.
- Reuse shared layouts and UI primitives.
- All components must support keyboard access, responsive behavior and light/dark themes.
