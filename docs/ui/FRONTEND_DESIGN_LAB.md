# Frontend Design Lab

Route:

```text
/design-lab
```

The lab contains ten runnable Blade references. They are concept explorations, not ten separate themes.

## Shared constraints

All concepts must preserve:

- search as a primary journey;
- clear entity identity;
- explicit provider actions;
- no misleading in-site playback promise;
- mobile-first layout;
- semantic information hierarchy;
- no provider API calls during rendering;
- noindex for the design lab itself.

## Concepts

1. Editorial Library
2. Search First
3. Artwork Gallery
4. Knowledge Graph
5. Calm Minimal
6. Music Magazine
7. Cinematic Dark
8. Utility Discovery
9. Community Shelves
10. Provider First

## Decision process

Score every concept from 1–5 for:

- five-second comprehension;
- search discoverability;
- entity clarity;
- provider CTA clarity;
- metadata readability;
- mobile suitability;
- visual differentiation;
- accessibility;
- performance risk;
- component reuse;
- suitability for artist pages;
- suitability for release/recording pages;
- suitability for collections/editorial content.

Do not select a concept based only on visual impact. Select a system that can support the full SongChart journey.

## Recommended synthesis

A likely production direction is a synthesis rather than an untouched concept:

- shell and typography from Editorial Library or Calm Minimal;
- search behavior from Search First;
- entity relationships from Knowledge Graph;
- provider chooser from Provider First;
- optional editorial modules from Music Magazine;
- dark presentation only as a supported theme variant, not a separate product.
