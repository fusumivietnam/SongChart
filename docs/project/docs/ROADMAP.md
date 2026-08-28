# Lộ trình sản phẩm

Trạng thái: tài liệu định hướng cho hạng mục đang triển khai và tương lai. Tài liệu này không quyết định stage hiện tại; `docs/project/DEVELOPMENT_STATE.md` là nguồn chuẩn cho operational current state, task contract của stage đang hoạt động sở hữu scope/acceptance, và `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã hoàn tất.

## Nguyên tắc đọc roadmap

- Chỉ giữ hạng mục đang triển khai hoặc chưa triển khai.
- Công việc đã hoàn tất phải chuyển sang Development History sau governed acceptance.
- Roadmap không thay thế task contract, candidate evidence hay `DEVELOPMENT_STATE.md`.
- UI/Admin ưu tiên thuật ngữ tiếng Việt rõ nghĩa; code/contract giữ tên kỹ thuật khi cần đối chiếu.

## Stage 18.3.1 — Verification & AI Workflow Convergence

Mục tiêu: hội tụ research → impact → implementation → post-diff → reconcile → audit → focused verify → candidate → canonical → delivery thành một golden path có dependency awareness cho người phát triển và AI.

Trọng tâm:

- planned-path và actual-diff impact resolution;
- semantic authority + reverse verification consumer + focused-check routing;
- collect-all diagnostic audit nhưng giữ strict quality/candidate/canonical fail-fast;
- generated-authority reconcile từ exact tree;
- impact map không được chứa command/test target chết;
- runtime/generated artifact ownership không được làm canonical mutate tracked tree;
- one-writer-per-surface và exact-commit AI/device handoff;
- official/native capability review trong task contract.

Không thuộc stage này:

- evidence caching/reuse;
- aggressive CI path pruning;
- merge queue khi chưa có nhu cầu concurrency thực tế;
- workflow framework mới;
- product feature mới.

## Stage 18.4 — Admin Completion & Operational Convergence

Mục tiêu: Admin vận hành được sản phẩm mà không phải dựa vào Tinker hoặc sửa `.env` cho các thao tác thường xuyên.

Trọng tâm:

- Dashboard operational;
- System Settings;
- Provider management và operational health;
- credential management/pool UX phù hợp authority hiện có;
- import workflow, progress, retry và failure UX;
- canonical admission;
- identity conflicts;
- catalog administration;
- users/roles;
- privileged audit.

Không block release bởi OAuth, credential rotation phức tạp, scheduler nâng cao hoặc provider breadth mới nếu chưa có use case bắt buộc.

## Stage 18.5 — Public Product / Frontend Release Pass

- information architecture và homepage;
- catalog/search UX;
- Artist/Group/Release/Recording/Work public pages;
- responsive/mobile;
- accessibility;
- performance/Core Web Vitals;
- loading/empty/error states;
- visual SEO polish và release QA.

## Stage 18.6 — Provider Destination & Media Quality

- destination/media selector theo availability, freshness và provenance;
- deterministic preference/ranking giữa approved destinations;
- operational visibility cho stale/unavailable destinations;
- YouTube/media destination approval quality;
- mở rộng provider breadth chỉ khi evidence quality và official API capability chứng minh nhu cầu.

## Nhóm 19.x — Production readiness / first release

- deployment topology;
- queue/scheduler production;
- observability và alerting;
- backup/recovery;
- secrets/environment hardening;
- security review;
- production smoke verification;
- release package/tag từ accepted `main` exact tree.

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
