# Lộ trình sản phẩm

Trạng thái: định hướng active/future. `docs/project/engineering/stage-plan.json` sở hữu stage đang triển khai; generated state + Git/GitHub sở hữu projection/runtime facts; `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã accepted.

## Accepted baseline through Stage 25

Stage 21–25 đã hoàn tất và không còn là future roadmap. Chúng tạo baseline hiện tại gồm:

- Editorial/Admin foundation và task-oriented operations baseline.
- Production vertical closure: provider evidence → canonical identity/admission → deterministic chart snapshot/provenance → canonical public read path.
- AI-ready control plane và vibe-coding operations với repository-owned context, impact-aware verification, bounded agent handoff và human-gated automation.
- Public product UX baseline cho discovery/search/entity/chart/mobile/accessibility/performance.
- Operational intelligence, provider/data-pipeline health, scale scorecard và evidence-gated infrastructure decisions.
- Global delivery/scale policy cho edge/cache, database read scaling, regional resilience, traffic control và external APM evaluation.

Accepted chronology và verification evidence thuộc `docs/project/DEVELOPMENT_HISTORY.md` và generated development state; không copy lại volatile accepted-state facts vào roadmap.

## Post-Stage-25 direction

Không mở stage/tranche mới chỉ từ roadmap này. `docs/project/engineering/stage-plan.json` tiếp tục giữ `next_tranche = null` cho tới khi một hướng được chuyển thành accepted use case + task contract riêng.

### 1. Authority & UX consolidation

Ưu tiên đầu tiên là giảm semantic/design drift trước khi mở rộng thêm product surface.

- Rà và đồng bộ route/design/external-system/issue authority với repository reality.
- Nâng design authority từ documentation thành executable contracts: semantic tokens, component registry, pattern registry, layout registry và canonical screens.
- Không cho page-level visual invention; visual concept mới phải thay đổi system-level design authority trước.
- Bổ sung visual-regression/browser evidence cho representative public/admin layouts.
- Nghiên cứu capability registry + authority graph để nối capability → route → use case → domain → UI → event → test → metric.
- Bổ sung drift detection để phát hiện docs/code/routes/issues/registries mâu thuẫn nhau.
- Giữ Blade/Livewire là default frontend stack; React/Inertia chỉ được xem xét bằng benchmark/use case có client-state/interaction complexity thực sự, không rewrite chỉ để đạt visual fidelity.

### 2. Product telemetry foundation

Xây event taxonomy trước khi phát triển intelligence/retention loops.

- Chuẩn hóa product events như search, zero-result, entity view, relationship click, provider click, favorite/collection action và data-issue report.
- Mỗi event phải có schema, producer, purpose, PII classification, retention, sampling và consumer rõ ràng.
- Product telemetry không trở thành canonical music-domain authority.
- Ưu tiên derived metrics/snapshots thay vì đưa raw event streams trực tiếp cho AI.

### 3. Retention & user-value loops

Đóng các MVP/user loops trước khi mở recommendation/community/native-app scope.

- Audit và đóng favorites/user collections theo MVP authority.
- Thêm recently viewed/recent searches và anonymous local state ở nơi phù hợp.
- Hỗ trợ anonymous → account merge/sync có governed semantics nếu implementation chứng minh giá trị.
- Xây return loop: discover → save → collection/history → return → discover more.
- Follow/notification/reactivation chỉ mở khi có persistent user intent và measurable return value.

### 4. Demand intelligence & zero-result recovery

Biến search failure thành product/data signal thay vì dead end.

- Zero-result UX phải có recovery: broader search, near matches, alias/transliteration/typo paths và governed data-request action.
- Lưu normalized demand evidence, cluster query variants và tính demand priority.
- Nối high-value unmet demand vào enrichment/import/editorial priority thay vì gọi provider synchronous từ public search.
- Theo dõi zero-result recovery, demand-to-entity resolution và future-search-success.

### 5. Data quality & trust intelligence

Biến provenance/canonical rigor thành measurable quality và public trust.

- Định nghĩa data-quality contract cho completeness, freshness, conflict severity, duplicate suspicion, provider agreement và confidence.
- Tạo prioritized Data Opportunity Queue dựa trên demand × incompleteness/conflict × exposure × provider availability.
- Public trust UX có thể hiển thị last-updated/source/state ở mức phù hợp và cung cấp report-issue path.
- User/editor reports chỉ tạo evidence/review tasks; canonical mutation vẫn thuộc governed application flow.

### 6. Provider evidence & quota economy

Nâng provider protection thành provider-efficiency system.

- Chuẩn hóa freshness/evidence policy theo provider + capability + data type: fresh, stale-acceptable, refresh-due, expired, negative-cached.
- Xây refresh planner quyết định có thực sự cần upstream call dựa trên local evidence, demand, priority, quota/cost và failure state.
- Persist durable provider-usage aggregates: calls, quota units, failures, snapshot/evidence hits, avoided calls và useful updates.
- Đo provider value/efficiency (useful update per request/quota unit) để điều chỉnh refresh cadence.
- Reuse existing durable observations/snapshots; Redis chỉ giữ runtime coordination/cache/locks, không trở thành business truth.
- Provider/API outage phải degrade về bounded stale/local evidence ở nơi policy cho phép.

### 7. Snapshot & client/edge reuse

Mở rộng pattern compute-once/read-many từ chart ra các read-heavy/intelligence surfaces.

- Chuẩn hóa snapshot metadata: version, generated_at, source watermark/input fingerprint, fresh/stale boundary.
- Candidate snapshots: provider health, data quality, search demand, quota status, SEO performance, scale scorecard.
- Client/edge hierarchy hướng tới browser cache → edge cache → server snapshot → durable evidence → external provider.
- PWA/client reuse, local history, stale-while-revalidate và analytics batching được đánh giá sau khi UX authority ổn định.
- Native mobile/desktop public clients không phải prerequisite; desktop operator shell chỉ đánh giá nếu local-AI/filesystem/operator workflows tạo ROI rõ.

### 8. Acquisition & SEO feedback loop

Chuyển SEO từ generation-only thành measured acquisition loop.

- Hoàn thiện canonical public metadata/sitemap presentation còn partial.
- Đánh giá Google Search Console, Bing Webmaster và IndexNow theo external-system authority khi implementation bắt đầu.
- Nối publish/change → crawl/index evidence → impressions/query/CTR → landing behavior → data/content improvement.
- Search-engine evidence không trở thành canonical music-domain truth.

### 9. Operator cockpit & decision intelligence

Chuyển Admin từ subsystem navigation sang prioritized operational work khi evidence đủ.

- Unified attention/action queue cho data gaps, conflicts, provider failures, quota pressure, broken destinations, system/SEO issues.
- Recommendation phải có evidence, confidence, expected impact và human approval boundary.
- Lưu decision/action/outcome để đo before/after và cải thiện recommendation sau này.
- Deterministic rules sở hữu scale/quota/safety decisions; AI chỉ summarize, classify, explain và rank trừ khi một authority mới explicit mở quyền cao hơn.

### 10. Internal/product AI

Ưu tiên AI nội bộ có ROI trước visitor chatbot.

- Candidate low-risk tasks: operator digest, provider/data-gap summary, conflict clustering, editorial suggestions, quota/scale explanation.
- AI đọc typed snapshots/derived evidence, không có arbitrary raw database authority.
- Model router cho phép local/free model với task đơn giản và strong cloud model cho cross-system reasoning khi justified.
- Visitor-facing AI assistant chỉ triển khai khi product evidence cho thấy conversational discovery tạo giá trị; bắt đầu read-only, retrieval-grounded, có citation/provenance và cost controls.

## Evidence-gated / explicitly deferred directions

Các capability sau không được coi là thiếu foundation và không được promote chỉ vì roadmap đề cập:

- CDN/Workers/Hyperdrive/replicas/multi-region/load balancing/external APM ngoài các trigger Stage 24/25 đã accepted.
- Microservices, Kubernetes, event-streaming platform, dedicated graph database và data warehouse nếu chưa có measurable pressure/use case.
- Native mobile apps nếu retention/native-only value chưa được chứng minh.
- Recommendation graph, public playlists và comments/community trước khi telemetry/retention foundation chứng minh nhu cầu.
- Visitor AI hoặc autonomous production mutation trước khi có explicit authority, safety/cost boundary và measured value.

## System flywheels mục tiêu

Roadmap hậu Stage 25 ưu tiên đóng các vòng phản hồi thay vì thêm subsystem rời rạc:

1. Demand loop: search/zero-result → demand → enrichment → better search.
2. Retention loop: discover → save/history/collection → return → more discovery.
3. Quality loop: gap/conflict → prioritized review → canonical correction → better public product.
4. Provider-efficiency loop: provider call → useful-update evidence → adaptive refresh → lower cost/better freshness.
5. Growth loop: canonical public content → indexing/search traffic → user demand → better content/data.
6. Trust loop: provenance/staleness/conflict → report/review → correction → higher trust.
7. Operations loop: signal → deterministic recommendation → human action → measured outcome → better future decision.
8. Experiment/learning loop: hypothesis → bounded variant → measurement → rollout/revert decision.

## Foundation research priorities

Nghiên cứu/phát triển theo thứ tự ưu tiên, không mặc định thành stage:

- P0: UX layout/pattern authority, capability registry, event taxonomy, freshness/evidence policy, authority drift detection.
- P1: data-quality contract, decision/outcome authority, privacy/data-lifecycle classification, internal boundary/dependency authority.
- P2: cost authority, feature-flag lifecycle, experiment authority, unified authority graph/contract compiler.

## Quy tắc roadmap

- Roadmap mô tả hướng tương lai; stage/tranche chỉ bắt đầu từ accepted use case + task contract trong stage authority.
- Completed work thuộc Development History/generated accepted state, không duy trì như future roadmap.
- Provider data là evidence/reference, không phải schema/canonical authority.
- Scale/infrastructure adoption phải chỉ ra metric/threshold và expected improvement.
- AI availability không được trở thành prerequisite cho core product/monitoring/data correctness.
- New authority phải giảm ambiguity/drift hoặc tạo executable enforcement; không tạo thêm documentation layer trùng lặp.
- UX/page implementation phải consume approved tokens/components/patterns/layouts thay vì tự phát minh design direction cục bộ.
- Product/data/operations changes phải ưu tiên đóng feedback loops và đo outcome thay vì thêm feature rời rạc.
