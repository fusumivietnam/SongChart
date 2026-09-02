# Lộ trình sản phẩm

Trạng thái: tài liệu định hướng cho hạng mục đang triển khai và tương lai. Tài liệu này không quyết định stage hiện tại; `docs/project/DEVELOPMENT_STATE.md` là nguồn chuẩn cho operational current state, task contract của stage đang hoạt động sở hữu scope/acceptance, và `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã hoàn tất.

## Nguyên tắc đọc roadmap

- Chỉ giữ hạng mục đang triển khai hoặc chưa triển khai.
- Công việc đã hoàn tất phải chuyển sang Development History sau governed acceptance.
- Roadmap không thay thế task contract, candidate evidence hay `DEVELOPMENT_STATE.md`.
- Product stage không bị kéo dài chỉ để triển khai một engineering framework chưa có use case thực tế.
- Cross-stage engineering improvements được áp dụng dần khi chúng giảm trực tiếp navigation/debug/verification cost; không tạo thêm stage sản phẩm nếu không cần.
- UI/Admin ưu tiên thuật ngữ tiếng Việt rõ nghĩa; code/contract giữ tên kỹ thuật khi cần đối chiếu.

## Stage 19.0 — Production Readiness & First Release

Mục tiêu: biến accepted `main` tree thành first production release có deployment topology rõ ràng, secrets/runtime an toàn, queue/scheduler vận hành được, observability/backup có evidence, security review và production smoke trước khi tạo release package/tag.

Trọng tâm:

- production deployment topology tối thiểu và reproducible;
- environment/secrets hardening không commit secret thật;
- queue worker + scheduler production lifecycle;
- PostgreSQL 18 production configuration và backup/restore verification;
- Redis/runtime operational boundaries;
- observability, alerting và health/smoke evidence;
- security review cho auth, privileged operations, secrets, provider credentials và public surface;
- release package/tag chỉ từ accepted exact `main` tree.

Chiến lược closure:

- inventory-first: không chọn hosting/platform trước khi repository runtime authority và release needs chỉ ra topology cần thiết;
- reuse Docker/Laravel/Caddy/PostgreSQL/Redis primitives hiện hữu trước khi thêm deployment framework;
- production-only config phải có owner và fail closed khi secret/runtime dependency thiếu;
- backup/recovery phải có restore evidence, không chỉ có tài liệu;
- network/provider live smoke là release-confidence evidence, không thay canonical deterministic gates;
- không mở product/provider breadth trong Stage 19 trừ khi production blocker chứng minh cần thiết.

### Planned slices

1. **19.0.1 — Production topology + environment inventory** — xác định supported first-release topology, runtime processes, ports/domains/TLS assumptions, env ownership và gaps.
2. **19.0.2 — Secrets/environment hardening** — production env contract, secret injection boundaries, fail-closed config checks.
3. **19.0.3 — Queue/scheduler production runtime** — worker/scheduler lifecycle, restart/failure behavior, queue topology and health.
4. **19.0.4 — Observability + alerting** — operational health, logs/metrics, alertable failure classes using existing first-party/runtime capabilities where possible.
5. **19.0.5 — Backup/recovery** — PostgreSQL backup + verified restore path, retention/operational procedure.
6. **19.0.6 — Security review + production smoke** — hardened public/admin/auth/provider boundaries and end-to-end production smoke checklist/evidence.
7. **FINAL — First release package/tag** — candidate/canonical exact-head closure, accepted PR/main CI, then release artifact/tag from accepted exact main tree.

AI Operations nếu được triển khai trước/sau production readiness phải bắt đầu ở read-only `observe → explain → recommend`; mọi mutation đi qua cùng governed Laravel Gate/Application Action/Audit surface như Admin, không có direct DB/secret/env bypass.

## Cross-stage engineering track — không phải product stage

Các cải tiến dưới đây được triển khai dần khi có evidence về lợi ích. Chúng không mặc định block product closure:

1. **Graph navigation** — truy vấn từ path/use case/failure tới owner, authority, reverse consumers, regressions và focused checks; ưu tiên projection/compile từ graph hiện có thay vì tạo source-of-truth mới.
2. **AI development routing** — `.agents/skills/*` và AI development protocol giúp AI chọn đúng bounded context/layer, official source và debug route; AI learning chỉ phục vụ development navigation/debug.
3. **Machine current-state projection** — cân nhắc machine-readable development state khi manual Markdown thực sự gây drift; Markdown vẫn là human projection.
4. **Mutation-envelope enforcement** — tự phát hiện command gây tracked mutation ngoài contract khi usage evidence đủ mạnh.
5. **Root-cause audit clustering** — nhóm failure theo owner/root cause để giảm sửa downstream symptom.
6. **Closure/delivery ergonomics** — tiếp tục dùng exact-HEAD seal, impact/reconcile/candidate/canonical workflow đã được harden ở 18.5.1; chỉ mở rộng khi có failure class mới có evidence.

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
