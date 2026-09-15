# SongChart Frontend Design Contract

> **Canonical direction:** Search-Centric Music Finder  
> **Status:** Approved baseline  
> **Scope:** Public frontend, authentication, entity pages and provider routing  
> **Rule:** Read this file before every frontend task.

---

## 1. Product positioning

SongChart is a music discovery, metadata and routing platform.

The interface helps users:

1. Search for artists, recordings, releases, works and versions.
2. Distinguish entity types clearly.
3. Understand relationships between entities.
4. Open official destinations on supported providers.
5. Inspect normalized metadata and source provenance.
6. Save searches and collections after signing in.

SongChart must not be presented as a streaming service. Never imply that SongChart hosts full audio, replaces providers, guarantees permanent provider availability or owns third-party media.

---

## 2. Approved visual direction

The approved design is:

> **Bright, search-first, structured, trustworthy and conversion-oriented.**

Required characteristics:

- light neutral page background;
- white content surfaces;
- dark navy text;
- violet primary accent;
- green reserved for available/success states;
- explicit entity labels;
- clear provider actions;
- generous whitespace;
- subtle borders and shadows;
- strong information hierarchy;
- metadata is readable without looking like an admin dashboard.

The interface must feel modern, calm, precise and consumer-friendly.

It must not become cinematic, editorial-heavy, glassmorphic, dashboard-heavy, neon, gaming-like or visually experimental.

---

## 3. Design tokens

### Colors

Use semantic tokens. Do not place raw colors directly inside feature views.

```css
--color-bg-page: #F8FAFC;
--color-bg-surface: #FFFFFF;
--color-bg-subtle: #F3F4F6;

--color-text-primary: #111827;
--color-text-secondary: #64748B;
--color-text-muted: #94A3B8;
--color-text-inverse: #FFFFFF;

--color-primary: #4F35F5;
--color-primary-hover: #4326E8;
--color-primary-soft: #F0EDFF;
--color-primary-border: #CFC7FF;

--color-success: #16A34A;
--color-success-soft: #EAF8EF;
--color-warning: #D97706;
--color-danger: #DC2626;

--color-border: #E2E8F0;
--color-border-strong: #CBD5E1;
--color-focus: #6D5DFB;
```

Rules:

- violet is the only primary brand accent;
- green is for availability, verification success and safe states;
- red is for errors, destructive actions and critical unavailability;
- provider colors appear only in provider-specific icons/actions;
- no gradients on ordinary content cards;
- no colored body text except semantic states and labels.

### Typography

Preferred family:

```text
Inter, Manrope, Plus Jakarta Sans, system-ui, sans-serif
```

Scale:

```text
Display: 48–64px / 1.05
H1: 40–48px / 1.1
H2: 28–32px / 1.2
H3: 20–24px / 1.3
Body large: 18px / 1.65
Body: 15–16px / 1.6
Small: 13–14px / 1.5
Caption: 12px / 1.4
```

Rules:

- headings use weight 700–800;
- body uses 400–500;
- metadata labels use 500–600;
- no serif, condensed or decorative fonts;
- uppercase is limited to small category labels.

### Radius

```text
Small controls: 8px
Inputs/buttons: 10–12px
Cards: 14–16px
Large surfaces: 20–24px
Pills: 9999px
```

### Shadow

```css
--shadow-card: 0 4px 16px rgba(15, 23, 42, 0.06);
--shadow-float: 0 12px 32px rgba(15, 23, 42, 0.10);
```

Avoid glow, dramatic elevation and multiple layered shadows.

### Spacing

Base unit: `4px`.

Approved scale:

```text
4, 8, 12, 16, 20, 24, 32, 40, 48, 64, 80
```

Containers:

```text
Desktop max width: 1280–1360px
Desktop padding: 32px
Tablet padding: 24px
Mobile padding: 16px
```

---

## 4. Global shell

### Desktop header

```text
Logo
Khám phá
Nghệ sĩ
Phát hành
Bộ sưu tập
Global search/search trigger
Account/avatar
```

Rules:

- white background and bottom border;
- maximum height 72px;
- active item uses violet text and underline;
- avatar remains on the far right;
- do not add top-level navigation without approval.

### Mobile navigation

Use a fixed bottom navigation:

```text
Khám phá
Tìm kiếm
Nghệ sĩ
Bộ sưu tập
Tài khoản
```

Rules:

- maximum five items;
- labels are always visible;
- active item uses violet;
- minimum touch target 44×44px;
- respect safe-area insets;
- no icon-only navigation.

---

## 5. Search experience

Search is the primary product interaction.

### Homepage hero

Required order:

```text
H1: Bạn muốn tìm gì hôm nay?
Supporting description
Large search input
Search button
Entity filters
```

Entity filters:

```text
Tất cả
Nghệ sĩ
Bài hát
Album
Phiên bản
Tác phẩm
```

Rules:

- search remains above the fold;
- search input is visually dominant;
- primary button is solid violet;
- selected entity filter is solid violet;
- no autoplay media;
- no promotional carousel above search.

### Autocomplete

Each suggestion may contain:

- thumbnail/fallback;
- title;
- context;
- entity type;
- year;
- verified state.

Autocomplete must support keyboard navigation and clearly distinguish artist, recording, release, work and version.

### Search results

Desktop structure:

```text
Search bar
Entity filters
Sort/filter controls
Main results
Optional sidebar
```

Grouped sections:

```text
Kết quả phù hợp nhất
Nghệ sĩ
Bài hát / Bản thu
Album / Phát hành
Phiên bản
Tác phẩm
Bộ sưu tập
```

Every result row must include title, entity type, primary context, secondary metadata and a clear navigation affordance.

Do not display fabricated popularity, listener or chart metrics.

### Empty state

Must include:

- searched query;
- spelling/broader search suggestion;
- alternative entity filter;
- related searches;
- option to report missing content.

---

## 6. Shared entity-page structure

All entity pages use:

```text
Breadcrumb
Identity hero
Primary metadata
Primary action
Related content
Sources/provenance
```

### Artist page

Hero:

```text
Artist image
Artist name
Canonical verification state
Country/origin
Genres
Short description
Chọn nơi nghe
Secondary content CTA
Share/menu
```

Sections:

```text
Featured recordings
Notable releases
Biography
Identity metadata
Related artists
Official links
Sources
```

Rules:

- verification means canonical identity verification;
- do not display follower/monthly-listener counts unless sourced and permitted;
- biography must not dominate the first viewport.

### Release page

Hero:

```text
Artwork
Release title
Artist
Release type
Version status
Release date
Label
Country
Track count
Chọn nơi nghe
Xem phiên bản
```

Sections:

```text
Track list
Other editions/versions
Participating artists
Credits
Sources
```

Track rows include number, title, duration, provider action and overflow menu.

Do not show an internal play action unless an approved embedded provider is actually active.

### Recording page

Hero:

```text
Artwork
Recording title
Artist
Release context
Duration
Release date
Short description
Chọn nơi nghe
Xem bản phát hành
```

Sections:

```text
Recording metadata
Other versions
Related work
Sources
Provider availability
Quick facts
```

Canonical distinction:

```text
Work = abstract composition/song
Recording = specific recorded performance
Release = publication/container
Version = live, remaster, acoustic, edition or other variant
```

### Work page

Hero:

```text
Tác phẩm
Title
Songwriter/composer
Composition year
Original context
Description
Genre/language
```

Sections:

```text
Notable recordings timeline
Original recording
Covers
Live versions
Related works
Source history
```

Do not treat release artwork as canonical artwork for an abstract work unless clearly labeled.

### Collection page

Hero:

```text
Collection artwork
Collection type
Title
Description
Curator
Item count
Last updated
Open collection
Provider chooser
```

Sections:

```text
Entity filters
Ordered mixed-entity list
Editorial rationale
Related topics
Related collections
```

Every collection item must state its entity type.

---

## 7. Provider chooser

Provider routing is a primary conversion flow.

Required fields:

- entity title/context;
- provider name and official icon;
- availability state;
- region note where known;
- action label `Mở`;
- external-link indicator;
- disclosure that the user leaves SongChart.

Canonical states:

```text
available → Có sẵn
unavailable → Không có sẵn
unknown → Chưa xác định
region_restricted → Giới hạn khu vực
stale → Cần kiểm tra lại
```

Rules:

- unavailable providers are never actionable;
- do not claim universal availability;
- available providers sort first;
- always link to official provider destinations;
- remember provider preference only with consent;
- never imply endorsement without a formal partnership.

---

## 8. Authentication

Desktop structure:

```text
Left: product benefits
Right: authentication card
```

Form structure:

```text
Đăng nhập / Tạo tài khoản tabs
Email
Password
Remember me
Forgot password
Primary submit
OAuth providers
Terms/privacy
```

Rules:

- full labels remain visible;
- password visibility control is required;
- errors appear under the relevant field;
- OAuth buttons appear only when configured;
- mobile becomes one column;
- no heavy decorative artwork.

---

## 9. Component contracts

### Buttons

Approved variants:

```text
primary
secondary
ghost
danger
provider
icon
```

Minimum height: `44px`.

Primary uses violet background and white text. Secondary uses white background, neutral border and dark text.

Do not create a new variant without a reusable cross-page case.

### Inputs

Required states:

```text
default
hover
focus
filled
disabled
error
success
```

Rules:

- minimum height 44px;
- visible label;
- violet focus ring;
- specific error message;
- icon never replaces label.

### Entity badges

Canonical labels:

```text
Nghệ sĩ
Bài hát
Bản thu
Album
EP
Phát hành
Phiên bản
Tác phẩm
Bộ sưu tập
```

Suggested mapping:

```text
Artist: violet
Recording/song: blue
Release/album: green
Version: lavender
Work: amber
Collection: indigo
```

Use the same mapping everywhere.

### Cards

Rules:

- one purpose per card;
- avoid card-inside-card;
- not every section is a card;
- subtle border/shadow only;
- action placement stays consistent.

### Tables/lists

Use tables for track lists, versions, comparisons and dense search results. On mobile, convert tables into structured rows/cards.

---

## 10. Required states

Every production page must support:

```text
loading
empty
error
partial data
stale data
permission denied
degraded provider data
```

Use layout-matching skeletons instead of full-page spinners.

For partial data, show available fields plus:

```text
Một số thông tin chưa được xác minh.
```

A provider failure must not hide the entire entity page.

---

## 11. Responsive rules

### Desktop

- two-column detail layout is allowed;
- main column: 65–72%;
- sidebar: 28–35%;
- sticky sidebar only for useful provider/data actions.

### Tablet

- reduce hero density;
- move sidebar below content when needed;
- search stays full-width;
- navigation may collapse.

### Mobile

- single column;
- fixed bottom navigation;
- provider CTA visible without excessive scrolling;
- discovery cards may scroll horizontally;
- tables become rows;
- no hover-only actions;
- no clipped metadata;
- minimum touch target 44×44px.

Responsive behavior must be intentional, not accidental wrapping.

---

## 12. Accessibility

Minimum target: WCAG 2.2 AA.

Required:

- visible keyboard focus;
- semantic heading order;
- real form labels;
- meaningful button names;
- artwork alt text;
- decorative images use empty alt;
- state is not communicated by color alone;
- reduced-motion support;
- dialog focus trapping;
- accessible icon-button labels;
- landmarked mobile navigation.

Never remove focus outlines without an accessible replacement.

---

## 13. SEO and structured data

Public entity pages require:

- canonical URL;
- unique title;
- meta description;
- Open Graph;
- social card metadata;
- breadcrumb structured data;
- appropriate MusicGroup, MusicAlbum, MusicRecording or CreativeWork schema;
- stable normalized slug;
- source/sameAs links where appropriate.

Design Lab and previews are always:

```html
<meta name="robots" content="noindex,nofollow">
```

---

## 14. Performance

Targets:

```text
LCP < 2.5s
INP < 200ms
CLS < 0.1
```

Rules:

- responsive images;
- lazy-load below-fold media;
- reserve artwork dimensions;
- no hero video;
- server-render primary metadata;
- avoid unnecessary hydration;
- load provider widgets only when required.

---

## 15. Implementation boundaries

Preferred frontend stack:

```text
Laravel Blade
Tailwind CSS
Alpine.js for small interactions
Livewire only for stateful server-driven UI
Vite
```

Executable directory ownership follows the current repository rather than requiring a parallel `pages/` tree:

```text
resources/css/tokens.css                         semantic design tokens
resources/views/layouts/frontend.blade.php      public/auth shell owner
resources/views/layouts/admin.blade.php         admin shell owner
resources/views/components/ui/                   shared UI primitives
resources/views/components/entity/               shared entity patterns
resources/views/components/provider/             provider-routing patterns
resources/views/components/search/               shared search patterns
resources/views/components/shell/                shared public shell/navigation
resources/views/home.blade.php                    homepage composition
resources/views/search/                           search page family
resources/views/entities/                         canonical entity-detail family
resources/views/catalog/                          public catalog/browse family
resources/views/charts/                           chart family
resources/views/account/                          account family
resources/views/auth/                             authentication family
resources/views/admin/                            admin page family
resources/views/ui-preview/                       internal design-system implementation rendered through /development/design-system
```

Rules:

- reuse primitives before creating components;
- page views compose components;
- do not duplicate component markup;
- do not create a parallel `resources/views/pages/` hierarchy merely to satisfy an obsolete convention;
- do not introduce React/Vue for isolated interactions;
- no raw colors in feature templates;
- no page-specific CSS when a shared token/component can solve it;
- new reusable patterns must be represented in the canonical `/development/design-system` inventory before broad page adoption.

---

## 16. Canonical page inventory

The approved direction must be consistently implemented for:

1. Homepage/discovery.
2. Search results.
3. Artist detail.
4. Release detail.
5. Recording detail.
6. Work detail.
7. Collection detail.
8. Provider chooser.
9. Login/register.
10. Mobile discovery/navigation.

A page is not complete until both desktop and mobile states exist.

---

## 17. Prohibited deviations

Do not:

- switch to dark-first;
- introduce serif typography;
- add gradients to ordinary cards;
- add carousels above search;
- turn every section into a card;
- invent new entity colors;
- merge work, recording and release;
- hide provider disclosure;
- imply internal playback when routing externally;
- fabricate charts, listeners or popularity;
- add glassmorphism;
- add autoplay;
- create a separate visual language for each entity page;
- create a new page shell when the approved shell exists.

---

## 18. AI implementation protocol

Before frontend changes, the AI must:

1. Read this document.
2. Identify the canonical page/entity type.
3. Reuse approved tokens.
4. Reuse existing components.
5. Preserve entity terminology.
6. Implement all relevant states.
7. Validate desktop and mobile.
8. Validate keyboard use.
9. Preserve light-first direction.
10. Avoid unapproved patterns.

Task/PR summary must state:

- approved pattern reused;
- components created or changed;
- reuse rationale;
- responsive states implemented;
- accessibility checks performed.

---

## 19. Acceptance checklist

A frontend implementation is accepted only when:

- search is visible and usable;
- entity type is explicit;
- title/context hierarchy is correct;
- primary action is clear;
- provider routing is disclosed;
- source information is reachable;
- spacing follows tokens;
- mobile layout is intentional;
- keyboard focus is visible;
- loading, empty and error states exist;
- no unapproved visual pattern is introduced;
- screenshots match the approved direction.

---

## 20. Source-of-truth priority

```text
1. This design contract
2. Approved design tokens
3. Approved shared components
4. Approved reference screenshots
5. Page-specific implementation
```

When behavior or accessibility is unclear, this contract overrides the screenshots.

When a requirement conflicts with this contract:

1. document the conflict;
2. propose the smallest system change;
3. update this file first;
4. update tokens/components;
5. then update pages.

Never diverge silently.
