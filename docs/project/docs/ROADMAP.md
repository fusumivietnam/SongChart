# Lộ trình sản phẩm

Trạng thái: định hướng active/future. `docs/project/engineering/stage-plan.json` sở hữu stage đang triển khai; generated state + Git/GitHub sở hữu projection/runtime facts; `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã accepted.

## Accepted baseline through Stage 26

Stage 21–26 đã hoàn tất và không còn là future roadmap. Chúng tạo baseline hiện tại gồm:

- Editorial/Admin foundation và task-oriented operations baseline.
- Production vertical closure: provider evidence → canonical identity/admission → deterministic chart snapshot/provenance → canonical public read path.
- AI-ready control plane và vibe-coding operations với repository-owned context, impact-aware verification, bounded agent handoff và human-gated automation.
- Public product UX baseline cho discovery/search/entity/chart/mobile/accessibility/performance.
- Operational intelligence, provider/data-pipeline health, scale scorecard và evidence-gated infrastructure decisions.
- Global delivery/scale policy cho edge/cache, database read scaling, regional resilience, traffic control và external APM evaluation.
- Authority/UX coherence baseline: route/design/external-system authority được reconcile với repository reality; executable design ownership dùng canonical design-system route family, semantic tokens và representative public/admin accessibility evidence mà không tạo frontend runtime hoặc design-system subsystem song song.

Accepted chronology và verification evidence thuộc `docs/project/DEVELOPMENT_HISTORY.md` và generated development state; không copy lại volatile accepted-state facts vào roadmap.

## Post-Stage-26 direction

Không mở stage/tranche mới chỉ từ roadmap này. `docs/project/engineering/stage-plan.json` tiếp tục sở hữu activation state; mỗi hướng chỉ trở thành implementation work khi có accepted use case + task contract riêng.

### 1. Product telemetry foundation

Xây event taxonomy trước khi phát triển intelligence/retention loops.

- Chuẩn hóa product events như search, zero-result, entity view, relationship click, provider click, favorite/collection action và data-issue report.
- Mỗi event phải có schema, producer, purpose, PII classification, retention, sampling và consumer rõ ràng.
- Product telemetry không trở thành canonical music-domain authority hoặc privileged/business audit authority.
- Chỉ persist event khi có consumer/metric và retention policy rõ; contract approval không tự động cho phép raw event storage.
- Ưu tiên derived metrics/snapshots thay vì đưa raw event streams trực tiếp cho AI.

### 2. Retention & user-value loops

Đóng các MVP/user loops trước khi mở recommendation/community/native-app scope.

- Audit và đóng favorites/user collections theo MVP authority.
- Thêm recently viewed/recent searches và anonymous local state ở nơi phù hợp.
- Hỗ trợ anonymous → account merge/sync có governed semantics nếu implementation chứng minh giá trị.
- Xây return loop: discover → save → collection/history → return → discover more.
- Follow/notification/reactivation chỉ mở khi có persistent user intent và measurable return value.

### 3. Demand intelligence & zero-result recovery

Biến search failure thành product/data signal thay vì dead end.

- Zero-result UX phải có recovery: broader search, near matches, alias/transliteration/typo paths và governed data-request action.
- Lưu normalized demand evidence, cluster query variants và tính demand priority.
- Nối high-value unmet demand vào enrichment/import/editorial priority thay vì gọi provider synchronous từ public search.
- Theo dõi zero-result recovery, demand-to-entity resolution và future-search-success.

### 4. Data quality & trust intelligence

Biến provenance/canonical rigor thành measurable quality và public trust.

- Định nghĩa data-quality contract cho completeness, freshness, conflict severity, duplicate suspicion, provider agreement và confidence.
- Tạo prioritized Data Opportunity Queue dựa trên demand × incompleteness/conflict × exposure × provider availability.
- Public trust UX có thể hiển thị last-updated/source/state ở mức phù hợp và cung cấp report-issue path.
- User/editor reports chỉ tạo evidence/review tasks; canonical mutation vẫn thuộc governed application flow.

### 5. Provider evidence & quota economy

Nâng provider protection thành provider-efficiency system.

- Chuẩn hóa freshness/evidence policy theo provider + capability + data type: fresh, stale-acceptable, refresh-due, expired, negative-cached.
- Xây refresh planner quyết định có thực sự cần upstream call dựa trên local evidence, demand, priority, quota/cost và failure state.
- Persist durable provider-usage aggregates: calls, quota units, failures, snapshot/evidence hits, avoided calls và useful updates.
- Đo provider value/efficiency (useful update per request/quota unit) để điều chỉnh refresh cadence.
- Reuse existing durable observations/snapshots; Redis chỉ giữ runtime coordination/cache/locks, không trở thành business truth.
- Provider/API outage phải degrade về bounded stale/local evidence ở nơi policy cho phép.

### 6. Snapshot & client/edge reuse

Mở rộng pattern compute-once/read-many từ chart ra các read-heavy/intelligence surfaces.

- Chuẩn hóa snapshot metadata: version, generated_at, source watermark/input fingerprint, fresh/stale boundary.
- Candidate snapshots: provider health, data quality, search demand, quota status, SEO performance, scale scorecard.
- Client/edge hierarchy hướng tới browser cache → edge cache → server snapshot → durable evidence → external provider.
- PWA/client reuse, local history, stale-while-revalidate và analytics batching được đánh giá sau khi UX authority ổn định.
- Native mobile/desktop public clients không phải prerequisite; desktop operator shell chỉ đánh giá nếu local-AI/filesystem/operator workflows tạo ROI rõ.

### 7. Acquisition & SEO feedback loop

Chuyển SEO từ generation-only thành measured acquisition loop.

- Hoàn thiện canonical public metadata/sitemap presentation còn partial.
- Đánh giá Google Search Console, Bing Webmaster và IndexNow theo external-system authority khi implementation bắt đầu.
- Nối publish/change → crawl/index evidence → impressions/query/CTR → landing behavior → data/content improvement.
- Search-engine evidence không trở thành canonical music-domain truth.

### 8. Operator cockpit & decision intelligence

Chuyển Admin từ subsystem navigation sang prioritized operational work khi evidence đủ.

- Unified attention/action queue cho data gaps, conflicts, provider failures, quota pressure, broken destinations, system/SEO issues.
- Recommendation phải có evidence, confidence, expected impact và human approval boundary.
- Lưu decision/action/outcome để đo before/after và cải thiện recommendation sau này.
- Deterministic rules sở hữu scale/quota/safety decisions; AI chỉ summarize, classify, explain và rank trừ khi một authority mới explicit mở quyền cao hơn.

### 9. Internal/product AI

Ưu tiên AI nội bộ có ROI trước visitor chatbot.

- Candidate low-risk tasks: operator digest, provider/data-gap summary, conflict clustering, editorial suggestions, quota/scale explanation.
- AI đọc typed snapshots/derived evidence, không có arbitrary raw database authority.
- RAG phải là retrieval projection trên repository/canonical evidence, không trở thành authority song song. Ưu tiên thứ tự `structured retrieval → lexical/alias retrieval → semantic retrieval → typed evidence pack → model router` thay vì mặc định vector similarity là truth.
- Canonical PostgreSQL/read models/snapshots giữ quyền sở hữu dữ liệu; vector/embedding index nếu được mở chỉ là rebuildable projection có stable entity IDs, provenance, freshness, authority/confidence metadata và bounded corpus ownership.
- Product/operational/engineering corpus phải tách boundary; visitor-facing retrieval chỉ được đọc public-approved canonical/provenance evidence, không được thấy quarantine/raw provider/internal admin evidence.
- Model router cho phép deterministic/no-model path cho entity facts, local/free model cho summarize/classify đơn giản và strong cloud model cho cross-system reasoning khi justified.
- RAG write path bị cấm trực tiếp: model chỉ tạo answer/proposal; mọi canonical mutation phải đi qua governed application command + validation + human gate tương ứng.
- Visitor-facing AI assistant chỉ triển khai khi product evidence cho thấy conversational discovery tạo giá trị; bắt đầu read-only, retrieval-grounded, có citation/provenance, privacy boundary và cost controls.

#### Governed RAG progression

RAG không được mở như một subsystem độc lập trước khi evidence/read-model foundation đủ. Hướng triển khai mục tiêu:

1. **RAG-0 — deterministic retrieval:** intent → canonical entity/read model/snapshot → grounded answer, không cần vector DB hoặc LLM cho factual queries đơn giản.
2. **RAG-1 — internal grounded assistant:** typed evidence packs cho operator/editor/data-gap/provider-conflict use cases; local model được ưu tiên khi đủ chất lượng.
3. **RAG-2 — semantic projection:** chỉ thêm embeddings/`pgvector`/hybrid ranking khi lexical/structured retrieval không đáp ứng một use case đo được; PostgreSQL-native capability được ưu tiên trước dedicated vector infrastructure.
4. **RAG-3 — visitor assistant:** public-only corpus, citation/provenance và cost/privacy guardrails; chỉ mở khi telemetry chứng minh conversational discovery có return value.
5. **RAG-4 — governed AI actions:** AI có thể đề xuất action nhưng không tự mutate canonical/product truth; human/authority gate vẫn sở hữu mutation.

### 10. Governed intelligence & adaptive assistance

Chỉ mở sau khi telemetry, demand/data-quality, provider economy và decision/outcome feedback đã có evidence đủ mạnh.

- Hợp nhất typed evidence/snapshots thành governed intelligence surfaces thay vì cho model arbitrary database access.
- Query/intent routing phải ưu tiên deterministic rules cho factual/domain paths; semantic/model routing chỉ dùng khi tạo measurable value.
- RAG evaluation phải đo retrieval precision/coverage, citation correctness, stale-evidence rate, unsupported-answer rate, latency và cost trước khi mở rộng model autonomy.
- Local/cloud model routing là implementation choice, không phải business authority; core product correctness không phụ thuộc model availability.
- Adaptive recommendations/automation chỉ mở khi outcome feedback + guardrail + rollback/degradation path đã accepted.

## Evidence-gated / explicitly deferred directions

Các capability sau không được coi là thiếu foundation và không được promote chỉ vì roadmap đề cập:

- CDN/Workers/Hyperdrive/replicas/multi-region/load balancing/external APM ngoài các trigger Stage 24/25 đã accepted.
- Microservices, Kubernetes, event-streaming platform, dedicated graph database và data warehouse nếu chưa có measurable pressure/use case.
- Dedicated vector database/semantic-search service nếu PostgreSQL-native structured/lexical/`pgvector` retrieval chưa chứng minh thiếu capability hoặc scale.
- Native mobile apps nếu retention/native-only value chưa được chứng minh.
- Recommendation graph, public playlists và comments/community trước khi telemetry/retention foundation chứng minh nhu cầu.
- Visitor AI hoặc autonomous production mutation trước khi có explicit authority, safety/cost boundary và measured value.

## System flywheels mục tiêu

Roadmap hậu Stage 26 ưu tiên đóng các vòng phản hồi thay vì thêm subsystem rời rạc:

1. Demand loop: search/zero-result → demand → enrichment → better search.
2. Retention loop: discover → save/history/collection → return → more discovery.
3. Quality loop: gap/conflict → prioritized review → canonical correction → better public product.
4. Provider-efficiency loop: provider call → useful-update evidence → adaptive refresh → lower cost/better freshness.
5. Growth loop: canonical public content → indexing/search traffic → user demand → better content/data.
6. Trust loop: provenance/staleness/conflict → report/review → correction → higher trust.
7. Operations loop: signal → deterministic recommendation → human action → measured outcome → better future decision.
8. Experiment/learning loop: hypothesis → bounded variant → measurement → rollout/revert decision.
9. Reactivation loop: persistent user intent → meaningful entity/chart/data change → bounded notification → return visit → stronger intent/retention evidence.
10. Destination-health loop: outbound failure/staleness/user report → validation/replacement queue → healthier provider destinations → higher outbound success/trust.
11. Cost/value loop: provider/AI/infrastructure spend → measurable useful outcome → retain/optimize/defer decision → improved unit economics.
12. Authority-improvement loop: drift/ambiguity detected → owning authority corrected → compiler/verifier/context regenerated → fewer future inconsistencies and agent mistakes.
13. Grounded-intelligence loop: governed evidence → retrieval/evidence pack → answer/recommendation → citation/outcome evaluation → retrieval/routing improvement without mutating source authority.

### Flywheel control contract

Một flywheel chỉ được coi là implemented khi có đủ các mắt xích sau; việc chỉ có event hoặc dashboard không được coi là closure:

1. **Trigger** — sự kiện/tình huống khởi động vòng lặp được định nghĩa rõ.
2. **Evidence** — input/signal có schema, provenance và authority boundary.
3. **Decision/priority** — rule hoặc governed process biến evidence thành next action; AI không được tự trở thành business authority.
4. **Action** — hành động có owner, idempotency/retry semantics và human gate khi cần.
5. **Outcome** — kết quả được đo bằng metric trước/sau hoặc success/failure state.
6. **Feedback** — outcome quay trở lại priority/policy/product decision cho vòng tiếp theo.
7. **Cost/risk guardrail** — quota, latency, privacy, abuse, notification fatigue hoặc infrastructure cost có boundary tương ứng.
8. **Exit/degradation path** — loop có thể tắt/degrade mà không làm hỏng canonical product hoặc core availability.

Mỗi accepted flywheel implementation phải khai báo tối thiểu:

- owner/capability;
- source events/snapshots;
- primary metric + guardrail metrics;
- action sink/queue;
- evaluation window;
- success threshold hoặc insufficient-evidence semantics;
- privacy/retention classification nếu có user telemetry;
- dependency/failure fallback;
- verification evidence.

### Flywheel coverage map

Các loop không độc lập; roadmap phải ưu tiên nối chúng thành một hệ thống thay vì tối ưu từng vòng riêng lẻ:

```text
Organic/direct visitor
        ↓
Search / discovery ───────────────→ Retention ───────────→ Reactivation
        │                              │                       │
        ├→ zero-result → Demand ───────┤                       │
        │                  │           │                       │
        │                  ↓           │                       │
        │             Data quality ←───┘                       │
        │                  │                                   │
        │                  ↓                                   │
        │             Canonical graph                          │
        │                  │                                   │
        │        ┌─────────┴──────────┐                        │
        │        ↓                    ↓                        │
        │    Public/SEO           Destinations ─→ health loop  │
        │        │                    │                        │
        │        ↓                    ↓                        │
        └──── Growth loop       Provider evidence              │
                                  │                            │
                                  ↓                            │
                          Provider efficiency                  │
                                  │                            │
                                  └──── cost/value ────────────┘

All product/data/provider/system signals
        ↓
Operations/decision loop
        ↓
Experiment/learning
        ↓
measured changes back into product/data/policy

Governed canonical/read-model/snapshot evidence
        ↓
Structured/lexical/semantic retrieval
        ↓
Typed evidence pack + model routing
        ↓
Grounded answer/recommendation + citation
        ↓
Evaluation/outcome feedback
        ↓
retrieval/routing improvement (not authority mutation)

All authorities/contracts
        ↓
Drift detection
        ↓
Authority-improvement loop
        ↓
Compiler/verifier/generated context
        ↓
safer future development and AI-agent work
```

Flywheel metrics không được tối ưu cục bộ nếu gây hại vòng khác. Ví dụ tăng notification click-through không được đánh đổi bằng notification fatigue; tăng provider freshness không được đánh đổi bằng quota exhaustion; tăng SEO landing volume không được đánh đổi bằng low-quality/fabricated public data.

## Foundation research priorities

Nghiên cứu/phát triển theo thứ tự ưu tiên, không mặc định thành stage:

- P0: capability registry, event taxonomy, freshness/evidence policy và authority-improvement automation trên baseline drift detection đã accepted.
- P1: data-quality contract, decision/outcome authority, privacy/data-lifecycle classification, internal boundary/dependency authority; typed evidence-pack contract và retrieval-corpus boundary cho future RAG.
- P2: cost authority, feature-flag lifecycle, experiment authority, unified authority graph/contract compiler; retrieval/model evaluation contract, hybrid-ranking evidence và local/cloud model routing khi có use case.

## Quy tắc roadmap

- Roadmap mô tả hướng tương lai; stage/tranche chỉ bắt đầu từ accepted use case + task contract trong stage authority.
- Completed work thuộc Development History/generated accepted state, không duy trì như future roadmap.
- Provider data là evidence/reference, không phải schema/canonical authority.
- Scale/infrastructure adoption phải chỉ ra metric/threshold và expected improvement.
- AI availability không được trở thành prerequisite cho core product/monitoring/data correctness.
- RAG/vector/embedding index chỉ là rebuildable retrieval projection; canonical/read-model/snapshot authority vẫn thuộc repository-owned data contracts.
- Structured/deterministic retrieval phải được đánh giá trước lexical/semantic/model retrieval; không dùng LLM cho factual query nếu typed read path đã đủ.
- New authority phải giảm ambiguity/drift hoặc tạo executable enforcement; không tạo thêm documentation layer trùng lặp.
- UX/page implementation phải consume approved tokens/components/patterns/layouts thay vì tự phát minh design direction cục bộ.
- Product/data/operations changes phải ưu tiên đóng feedback loops và đo outcome thay vì thêm feature rời rạc.
- Flywheel closure phải tuân theo Flywheel Control Contract; không chấp nhận loop chỉ có telemetry nhưng không có action/outcome/feedback hoặc chỉ có action nhưng không đo được effect.
- Cross-flywheel guardrails ưu tiên system-level outcome hơn local metric optimization.
