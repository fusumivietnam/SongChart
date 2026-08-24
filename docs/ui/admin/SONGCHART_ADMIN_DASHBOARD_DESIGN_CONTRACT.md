# SongChart Admin Dashboard Design Contract

> **Canonical direction:** Dark-sidebar, light-workspace, attention-first admin dashboard  
> **Status:** Approved baseline  
> **Scope:** Admin shell, dashboard homepage, operational work queues, data quality, provider status and system oversight  
> **Rule:** Read this file before every admin UI task.

---

## 1. Product purpose

SongChart admin is not a technical playground.

The admin dashboard exists to help operators:

1. Understand what needs attention now.
2. Resolve data-quality problems quickly.
3. Review ingestion, provider and sync health.
4. Manage entities and editorial workflows.
5. Track recent actions and accountability.
6. Operate the system in business language, not low-level engineering jargon.

The dashboard must prioritize:

- work that needs action;
- unresolved data conflicts;
- duplicate candidates;
- verification tasks;
- provider/sync warnings;
- recent meaningful activity;
- high-level health summaries.

The dashboard must not become a generic analytics wall full of vanity charts.

---

## 2. Approved visual direction

The approved admin direction is:

> **Dark indigo sidebar + bright card-based workspace + purple-blue accents + operational clarity.**

Required characteristics:

- dark left navigation;
- light main canvas;
- white cards with subtle borders;
- strong title hierarchy;
- Vietnamese labels;
- operational status badges;
- task lists and warnings are easy to scan;
- charts are secondary to actionable work;
- density is moderate, not overly sparse and not crowded.

The admin must feel:

- trustworthy;
- calm;
- structured;
- fast to scan;
- operations-focused;
- friendly to non-technical administrators.

The admin must not feel:

- developer-console-like;
- overly playful;
- neon;
- glassmorphic;
- chart-heavy;
- enterprise-grey and lifeless;
- inconsistent between modules.

---

## 3. Information hierarchy

The admin dashboard homepage must follow this priority order:

```text
Page title
→ Context/subtitle
→ Work needing attention / clear next actions
→ High-level operational metrics
→ Data-source status
→ General notices
→ Secondary technical diagnostics
```

If space is limited, preserve the order above.

Do not place technical diagnostics above operator work.

---

## 4. Design tokens

### 4.1 Color system

Use semantic tokens.

```css
--admin-bg-page: #F6F8FC;
--admin-bg-surface: #FFFFFF;
--admin-bg-subtle: #F1F5F9;

--admin-text-primary: #0F172A;
--admin-text-secondary: #475569;
--admin-text-muted: #94A3B8;
--admin-text-inverse: #FFFFFF;

--admin-sidebar-bg: #0B1328;
--admin-sidebar-bg-2: #101A36;
--admin-sidebar-text: rgba(255,255,255,.84);
--admin-sidebar-muted: rgba(255,255,255,.56);
--admin-sidebar-active: #4F46E5;

--admin-primary: #5B4EF7;
--admin-primary-hover: #4D3FEA;
--admin-primary-soft: #EEF0FF;

--admin-success: #16A34A;
--admin-success-soft: #EAF8EF;
--admin-warning: #F59E0B;
--admin-warning-soft: #FFF6E5;
--admin-danger: #EF4444;
--admin-danger-soft: #FEECEC;
--admin-info: #3B82F6;
--admin-info-soft: #EAF2FF;

--admin-border: #E2E8F0;
--admin-border-strong: #CBD5E1;
--admin-focus: #6D5DFB;
```

Rules:

- purple/indigo is the primary accent;
- red is for urgent errors and destructive actions;
- amber is for warnings and “needs review” states;
- green is for healthy, verified or completed states;
- blue is for informational/system states;
- sidebar uses dark indigo only, not pure black;
- do not use gradients on content cards by default.

### 4.2 Typography

Preferred family:

```text
Inter, Manrope, Plus Jakarta Sans, system-ui, sans-serif
```

Scale:

```text
Dashboard title: 40–44px / 1.1
Section title: 24–28px / 1.2
Card title: 18–20px / 1.3
Metric number: 28–40px / 1.1
Body: 14–16px / 1.6
Small/meta: 12–13px / 1.5
```

Rules:

- page title uses 700–800 weight;
- numbers use 700–800 weight;
- labels use 500–600;
- metadata/support text uses 400–500;
- avoid serif fonts;
- uppercase is limited to tiny category labels.

### 4.3 Radius

```text
Controls: 10–12px
Cards: 14–16px
Panels/charts: 18px
Pills: 9999px
```

### 4.4 Shadow

```css
--admin-shadow-card: 0 6px 18px rgba(15, 23, 42, 0.06);
--admin-shadow-float: 0 12px 30px rgba(15, 23, 42, 0.08);
```

Keep elevation subtle.

### 4.5 Spacing

Base unit: `4px`

Preferred scale:

```text
4, 8, 12, 16, 20, 24, 32, 40, 48
```

Main content container:

```text
Desktop side padding: 28–32px
Section gap: 20–24px
Card internal padding: 20–24px
```

---

## 5. Admin shell

### 5.1 Layout

Canonical desktop layout:

```text
Left fixed sidebar
+ top header
+ scrolling main content area
```

Sidebar width:

```text
Expanded: 240–264px
Collapsed: 72–84px
```

Main workspace:

- light background;
- max width large enough for dashboard grids;
- no unnecessary centered narrow layout;
- comfortable grid spacing.

### 5.2 Sidebar structure

Required group order:

```text
Dashboard

Quản lý dữ liệu
- Danh mục
- Thực thể
- Ghi âm (Recordings)
- Bản phát hành (Releases)
- Nghệ sĩ (Artists)
- Tác phẩm (Works)
- Bộ sưu tập (Collections)

Chất lượng dữ liệu
- Xung đột dữ liệu
- Trùng lặp
- Cần xác minh
- Đề xuất hợp nhất

Nguồn dữ liệu & Provider
- Nguồn dữ liệu
- Provider
- Đồng bộ & Lịch sử

Mở rộng
- Plugin / Extension
- Cấu hình hệ thống

Quản trị
- Người dùng
- Vai trò & Quyền
- Nhật ký hoạt động
```

Rules:

- group labels use small uppercase or muted section labels;
- active route uses filled indigo background;
- counts may appear on problematic sections;
- do not overload the sidebar with one-off links;
- nested expansion must stay simple.

### 5.3 Top header

Required items:

```text
Global search
Notifications
Help/status icon
Date range selector
Quick actions / filter controls
User identity block
```

Rules:

- search is placed in header;
- header background is light and calm;
- date range is visible for dashboards with time-based views;
- quick actions stay on the right;
- user avatar/name/role are visible.

---

## 6. Dashboard homepage contract

### 6.1 Header block

Required:

```text
Title: Dashboard
Subtitle: overview of system health and work requiring attention
```

Use a simple textual summary, not a decorative hero.

### 6.2 Summary metric cards

The first row contains top-level metric cards.

Recommended metrics:

```text
Tổng thực thể
Recordings
Releases
Artists
Works
Collections
```

Each card contains:

- metric label;
- primary number;
- icon;
- change indicator vs previous period;
- explanatory text.

Rules:

- 4–6 cards per row depending on viewport;
- icons are subtle and color-coded;
- change text uses semantic color;
- metric cards are compact and comparable;
- do not use oversized illustration blocks.

### 6.3 Activity overview chart

A trend chart is allowed and recommended.

Content:

```text
Created
Updated
Deleted
```

Rules:

- chart title: `Tổng quan hoạt động`;
- time-range control top right;
- lines only or very clean area/line combinations;
- chart must support tooltips;
- chart is secondary to actionable work;
- avoid more than 3–4 series.

### 6.4 Entity distribution card

A donut or pie chart is acceptable.

Title:

```text
Phân bố theo loại thực thể
```

Shows:

- Recordings;
- Releases;
- Artists;
- Works;
- Collections;
- Other.

Rules:

- total in center;
- legend always visible;
- percentages and counts readable;
- colors stable per entity type;
- chart must not be purely decorative.

### 6.5 My work panel

This panel is mandatory.

Title:

```text
Công việc của tôi
```

Each task row includes:

- task name;
- context description;
- severity badge;
- recency or due time.

Examples:

- xem xét xung đột dữ liệu;
- hợp nhất trùng lặp;
- xác minh thông tin;
- cập nhật nguồn dữ liệu.

Rules:

- written in business language;
- no raw queue-job identifiers;
- severity badges use semantic tokens;
- the panel must be scannable in under 10 seconds.

### 6.6 Work needing action panel

Title:

```text
Công việc cần xử lý
```

Recommended rows:

- Xung đột dữ liệu;
- Trùng lặp;
- Cần xác minh;
- Đề xuất hợp nhất;
- Đóng góp mới.

Each row contains:

- label;
- short explanation;
- count;
- chevron/action affordance.

Rules:

- this is one of the most important panels;
- counts must be prominent;
- wording must help non-technical admins understand the task.

### 6.7 Recent activity panel

Title:

```text
Hoạt động gần đây
```

Each row contains:

- actor avatar or icon;
- actor name;
- action summary;
- affected entity;
- relative timestamp.

Rules:

- only meaningful human-readable actions;
- no low-level log noise;
- keep 5–8 items visible;
- support “Xem tất cả”.

### 6.8 System warnings panel

Title:

```text
Cảnh báo hệ thống
```

Includes:

- provider sync failures;
- stale data warnings;
- high failure ratio;
- scheduled maintenance;
- degraded service notices.

Each alert row contains:

- severity icon;
- concise headline;
- short explanation;
- timestamp.

Rules:

- keep wording plain;
- explain the operational impact;
- do not expose stack traces on the dashboard.

---

## 7. Content language rules

Admin UI language must prioritize operations and business meaning.

Prefer:

- `Cần xác minh`;
- `Dữ liệu có thể trùng lặp`;
- `Đồng bộ provider thất bại`;
- `Cần xem xét nguồn dữ liệu`;
- `Đóng góp mới từ người dùng`.

Avoid showing raw technical phrases on the main dashboard such as:

- migration batch;
- queue payload;
- SQL constraint error;
- cache invalidation;
- job worker timeout;
- API response schema mismatch.

Technical detail may exist in drill-down screens, not as the primary dashboard language.

---

## 8. Component contracts

### 8.1 Metric card

Structure:

```text
Icon
Label
Value
Delta
Helper text
```

Rules:

- compact;
- comparable height;
- subtle icon circle;
- helper text beneath number;
- no dense paragraphs.

### 8.2 Status badge

Approved semantic states:

```text
Cao
Trung bình
Thấp
Thông tin
Ổn định
Lỗi
Cảnh báo
```

Color mapping:

- high/critical → red;
- medium → amber;
- low/minor → muted amber or neutral;
- info → blue;
- healthy/stable → green.

### 8.3 Task row

Must include:

- leading icon;
- title;
- one-line explanation;
- count or badge;
- optional timestamp.

The title must describe the work, not the implementation.

### 8.4 Chart card

Must include:

- title;
- optional legend;
- optional filter control;
- chart body;
- accessible fallback summary.

### 8.5 Alert row

Must include:

- severity icon;
- short headline;
- supporting explanation;
- time;
- click target to open details.

---

## 9. Required dashboard states

The dashboard must support:

```text
loading
empty
error
partial data
stale data
permission-limited
maintenance mode
```

### Loading

Use skeleton cards and chart placeholders matching layout.

Do not show a full-page spinner for normal loads.

### Empty state

If no task/activity exists, say so clearly:

```text
Hiện chưa có công việc cần xử lý.
```

### Partial data

If some providers or services fail, continue showing available data and display a note:

```text
Một số dữ liệu hiện chưa thể cập nhật đầy đủ.
```

### Error state

Use plain language:

```text
Không thể tải đầy đủ dữ liệu dashboard. Vui lòng thử lại sau.
```

No raw stack traces on the dashboard page.

---

## 10. Responsiveness

### Desktop

Grid may use:

- 4–6 metric cards in first row;
- 2–3 column content sections;
- one persistent sidebar.

### Tablet

Rules:

- metric cards wrap into fewer columns;
- some panels may stack;
- header controls may collapse;
- sidebar may collapse to icons or drawer.

### Mobile

Rules:

- sidebar becomes drawer;
- header remains useful and compact;
- metric cards stack vertically or in 2-column mini-grid;
- charts should remain readable or convert to summary cards;
- task and alert lists stay first-class;
- no tiny unreadable tables.

Admin mobile does not need full parity in density, but core work actions must remain accessible.

---

## 11. Accessibility

Minimum target: WCAG 2.2 AA.

Required:

- visible focus states;
- semantic headings;
- keyboard navigable sidebar and header controls;
- non-color status meaning;
- chart summaries or accessible legends;
- sufficient contrast in sidebar;
- touch targets at least 44×44px;
- readable error messages;
- screen-reader labels for icon buttons.

Never use color as the only indicator of severity or status.

---

## 12. Data presentation rules

The admin dashboard must explain values in context.

Every important number should be traceable to a human meaning.

Examples:

- `23 xung đột dữ liệu` is acceptable.
- `23 invalid rows` is not acceptable on the main dashboard.

If a metric comes from a time range, display the range.

If a warning is stale-sensitive, display when it was last checked.

If a metric is not actionable, consider removing it from the dashboard.

---

## 13. What not to show on the main dashboard

Do not show these as primary dashboard items unless specifically requested by a technical operator view:

- raw SQL/database statistics;
- Redis internals;
- cache hit ratios;
- background-worker process IDs;
- low-level exception dumps;
- full API payload errors;
- hosting/server telemetry noise;
- dozens of small vanity charts;
- irrelevant total counts without context.

Technical data belongs in drill-down operational screens, not the default business dashboard.

---

## 14. Recommended dashboard actions

Allowed visible quick actions:

- tạo thực thể mới;
- xem hàng chờ xử lý;
- kiểm tra provider;
- xuất báo cáo;
- lọc theo khoảng thời gian.

Do not overload the top area with too many action buttons.

Maximum quick actions visible in header area: `2–4`.

---

## 15. Implementation boundaries

Preferred implementation stack:

```text
Laravel Blade
Tailwind CSS
Alpine.js for lightweight interactions
Livewire for stateful admin modules where justified
Vite for assets
```

Component directories should follow:

```text
resources/views/components/admin/
resources/views/components/ui/
resources/views/components/charts/
resources/views/admin/
```

Rules:

- reuse admin primitives before creating new card patterns;
- do not create a new card style for every module;
- use a shared chart container pattern;
- keep icons consistent;
- page-specific styles should be minimal.

---

## 16. Canonical admin dashboard inventory

The admin system should eventually standardize these screens:

1. Dashboard home.
2. Entity list view.
3. Entity detail/editor.
4. Conflict review queue.
5. Duplicate merge queue.
6. Verification queue.
7. Provider registry/status.
8. Sync history.
9. Extension manager.
10. User/role management.
11. Activity log.
12. System settings.

This file governs the dashboard home and shared admin visual language.

---

## 17. Prohibited deviations

Do not:

- replace the dark sidebar with a light one without system-wide redesign;
- switch to a dark main workspace;
- remove actionable work panels in favor of more charts;
- show only technical metrics on the dashboard;
- invent different card styles across modules;
- use overly bright gradients or glossy effects;
- overload the page with tiny widgets;
- hide context behind unlabeled icons;
- expose stack traces on the homepage;
- treat the admin like a BI tool before it serves core workflows.

---

## 18. AI implementation protocol

Before modifying admin UI, the AI must:

1. Read this document fully.
2. Identify whether the task affects shell, dashboard or shared admin patterns.
3. Reuse approved tokens and admin components.
4. Preserve business-first language.
5. Implement loading, empty and error states.
6. Validate desktop and mobile behavior.
7. Validate accessibility and keyboard navigation.
8. Keep visual consistency with the approved dashboard.

Task/PR summary must explain:

- which approved pattern was reused;
- which new admin component was created;
- why it is reusable;
- which data/action priorities were preserved;
- which accessibility checks were considered.

---

## 19. Acceptance checklist

An admin dashboard implementation is accepted only when:

- the dark sidebar / light workspace structure is preserved;
- the dashboard title and context are clear;
- summary metrics are readable;
- actionable work is visible above low-priority content;
- my work and work queues are understandable;
- recent activity is human-readable;
- system warnings are concise and meaningful;
- spacing, typography and colors match the contract;
- keyboard focus is visible;
- loading, empty and error states exist;
- business users can understand what each value means.

---

## 20. Source-of-truth priority

```text
1. This admin dashboard design contract
2. Approved admin shared components
3. Approved admin design tokens
4. Approved reference screenshots
5. Page-specific implementation
```

When screenshots and implementation differ, use this contract to resolve behavior, hierarchy and accessibility.

If a new requirement conflicts with this file:

1. document the conflict;
2. propose the smallest system change;
3. update this contract first;
4. then update components and pages.

Do not silently diverge.
