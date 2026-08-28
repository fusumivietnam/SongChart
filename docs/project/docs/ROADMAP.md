# Lộ trình sản phẩm

Trạng thái: tài liệu định hướng cho hạng mục đang triển khai và tương lai. Tài liệu này không quyết định stage hiện tại; `docs/project/DEVELOPMENT_STATE.md` là nguồn chuẩn cho operational current state, task contract của stage đang hoạt động sở hữu scope/acceptance, và `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã hoàn tất.

## Nguyên tắc đọc roadmap

- Chỉ giữ hạng mục đang triển khai hoặc chưa triển khai.
- Công việc đã hoàn tất phải chuyển sang Development History sau governed acceptance.
- Roadmap không thay thế task contract, candidate evidence hay `DEVELOPMENT_STATE.md`.
- Product stage không bị kéo dài chỉ để triển khai một engineering framework chưa có use case thực tế.
- Cross-stage engineering improvements được áp dụng dần khi chúng giảm trực tiếp navigation/debug/verification cost; không tạo thêm stage sản phẩm nếu không cần.
- UI/Admin ưu tiên thuật ngữ tiếng Việt rõ nghĩa; code/contract giữ tên kỹ thuật khi cần đối chiếu.

## Stage 18.4 — Admin Completion & Operational Convergence

Mục tiêu: Admin vận hành được sản phẩm mà không phải dựa vào Tinker hoặc sửa `.env` cho các thao tác thường xuyên.

Trọng tâm:

- Dashboard operational và attention routing;
- System Settings, với phân tách rõ runtime-configurable vs deployment/secret-owned configuration;
- Provider management và operational health;
- credential management/pool UX phù hợp authority hiện có và không render secrets;
- import workflow, progress, retry và failure UX;
- canonical admission;
- identity conflicts;
- catalog administration;
- users/roles trên các mutation use case được chấp nhận rõ ràng;
- privileged audit.

Chiến lược closure:

- ưu tiên hoàn thiện các edge vận hành còn thiếu trên surface đã tồn tại thay vì mở subsystem mới;
- mỗi slice phải nhỏ, có action path rõ, focused regression và không mở rộng scope nếu inventory cho thấy capability đã đủ;
- nếu acceptance criteria cốt lõi đã đạt, đóng 18.4 sớm và chuyển phần engineering optimization không bắt buộc sang cross-stage track;
- không block release bởi OAuth, credential rotation phức tạp, scheduler nâng cao, AI operations automation hoặc provider breadth mới nếu chưa có use case bắt buộc.

## Stage 18.5 — Public Product / Frontend Release Pass

- information architecture và homepage;
- catalog/search UX;
- Artist/Group/Release/Recording/Work public pages;
- responsive/mobile;
- accessibility;
- performance/Core Web Vitals;
- loading/empty/error states;
- visual SEO polish và release QA.

Engineering graph/navigation improvements tiếp tục được dùng nền, nhưng không được làm 18.5 trở thành một architecture rewrite.

## Stage 18.6 — Provider Destination & Media Quality

- destination/media selector theo availability, freshness và provenance;
- deterministic preference/ranking giữa approved destinations;
- operational visibility cho stale/unavailable destinations;
- YouTube/media destination approval quality;
- mở rộng provider breadth chỉ khi evidence quality và official API capability chứng minh nhu cầu.

## Cross-stage engineering track — không phải product stage

Các cải tiến dưới đây được triển khai dần khi có evidence về lợi ích. Chúng không mặc định block product closure:

1. **Graph navigation** — truy vấn từ path/use case/failure tới owner, authority, reverse consumers, regressions và focused checks; ưu tiên projection/compile từ graph hiện có thay vì tạo source-of-truth mới.
2. **AI development routing** — `.agents/skills/*` và AI development protocol giúp AI chọn đúng bounded context/layer, official source và debug route; AI learning chỉ phục vụ development navigation/debug.
3. **Machine current-state projection** — cân nhắc machine-readable development state khi manual Markdown thực sự gây drift; Markdown vẫn là human projection.
4. **Mutation-envelope enforcement** — tự phát hiện command gây tracked mutation ngoài contract khi usage evidence đủ mạnh.
5. **Root-cause audit clustering** — nhóm failure theo owner/root cause để giảm sửa downstream symptom.
6. **Closure/delivery ergonomics** — machine-readable exact-HEAD seal và delivery helper chỉ sau khi close→push→PR lifecycle đã ổn định qua nhiều stage.

Không tạo `engineering-knowledge-graph.json`, verifier mới hoặc workflow command mới chỉ để phản chiếu dữ liệu đã có. Mỗi surface mới phải chứng minh query/use case không thể đáp ứng tốt bằng owner hiện tại.

## Repository simplification track

Dọn dần, không xóa theo cảm tính. Một surface chỉ được retire khi không còn active runtime/build/authority/navigation/verification consumer.

Ưu tiên rà soát:

- historical root `STAGE_*_CHANGE_MANIFEST.md` và historical stage docs: giữ chronology/evidence nhưng loại khỏi active navigation; cân nhắc archive/consolidate khi verifier không còn phụ thuộc;
- model-specific instruction files `AGENTS.md`, `CLAUDE.md`, `GEMINI.md`: giữ thin routing only, không duplicate AI workflow;
- `.agents/skills/*`: giữ ngắn, route tới authority thay vì copy rules;
- duplicate prose docs: consolidate vào Documentation Index/owner, giữ compatibility link nếu cần;
- dead verification aliases, compatibility scripts và retired command references: xóa khi verification command-surface/consumer graph chứng minh không còn consumer;
- overlapping `Application` / `Actions` / `Services` / `Support`: không mass-move; với code mới chọn owner rõ ràng, và chỉ refactor lớp cũ khi một use case thực tế chạm tới nó;
- stale generated/runtime artifacts: regenerate hoặc bỏ tracked ownership theo authority, không allowlist để né gate.

## Nhóm 19.x — Production readiness / first release

- deployment topology;
- queue/scheduler production;
- observability và alerting;
- backup/recovery;
- secrets/environment hardening;
- security review;
- production smoke verification;
- release package/tag từ accepted `main` exact tree.

AI Operations nếu được triển khai trước/sau production readiness phải bắt đầu ở read-only `observe → explain → recommend`; mọi mutation đi qua cùng governed Laravel Gate/Application Action/Audit surface như Admin, không có direct DB/secret/env bypass.

## Nhóm sau release — Giá trị cho người dùng cá nhân

- follow Artist/Group;
- save nội dung và private collections;
- discovery dựa trên hành động rõ ràng của người dùng;
- chỉ kết nối provider account khi API chính thức và policy cho phép.

## Quy tắc roadmap

- Mỗi stage bắt đầu từ accepted use case + executable task contract.
- Field/state mới của provider phải có authority trước migration/application use.
- Một feature chưa đóng nếu PostgreSQL, quality, candidate và canonical verification chưa đạt authority hiện hành.
- `DEVELOPMENT_STATE.md` phải được cập nhật cùng logical changeset khi blocker/evidence/next action thay đổi.
- Roadmap không lưu debug diary hoặc verification transcript; các dữ kiện vận hành ngắn hạn thuộc Development State.
- Engineering improvement phải giảm measurable friction hoặc close một failure class; nếu chỉ tạo thêm chuẩn/contract mà không giảm ambiguity, verification cost hoặc operational risk thì không đưa vào.
