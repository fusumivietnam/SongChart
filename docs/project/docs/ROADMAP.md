# Lộ trình sản phẩm

Trạng thái: tài liệu định hướng cho hạng mục đang triển khai và tương lai. Tài liệu này không quyết định stage hiện tại; `README.md` là nguồn chuẩn cho current-stage pointer, `docs/foundation/STAGE_18_1_TASK_CONTRACT.md` sở hữu scope/acceptance hiện tại, và `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã hoàn tất.

## Nguyên tắc đọc roadmap

- Chỉ giữ hạng mục đang triển khai hoặc chưa triển khai.
- Công việc đã hoàn tất phải chuyển sang Development History sau governed acceptance.
- Roadmap không thay thế task contract, candidate evidence hay `DEVELOPMENT_STATE.md`.
- UI/Admin ưu tiên thuật ngữ tiếng Việt rõ nghĩa; code/contract giữ tên kỹ thuật khi cần đối chiếu.

## Stage 18.1 — Rich Entity & Multi-Provider Evidence Model

Mục tiêu hiện tại: hoàn thiện provider ingestion theo mô hình evidence đa nguồn, không cho provider payload ghi trực tiếp vào canonical entities.

Trọng tâm còn lại để đóng Stage 18.1:

- ổn định rich normalized provider DTO + validation cho fields, identifiers, relationships, media, destinations, availability, classifications và metrics;
- giữ provider-specific mapping sau typed mapper registry; chỉ mapper thực sự hỗ trợ mới được công bố trong Admin;
- hoàn thiện read-only preview → deterministic import plan → fingerprint-checked execution;
- tiếp tục tái sử dụng `ProviderImportOrchestrator`, identity resolution, validation và canonical-admission boundaries; không tạo ingestion pipeline thứ hai;
- làm rõ provider UX/Admin wording và operational feedback trước candidate closure;
- giữ Codespaces/shared-demo chỉ là development adapters, không thay persistence/domain authority;
- hoàn tất focused verification, candidate preparation, Stage closure và canonical closure trên exact target tree.

Điều kiện an toàn:

- provider payload không tự động mutate canonical entities;
- preview-only payload không trở thành provider evidence chỉ vì được xem trước;
- shared demo database không được dùng cho destructive development/test workflow;
- AI agents phải đọc repository authority, chạy `songchart ai status`, và không tự tạo workflow truth song song.

## Nhóm 18.x — Public search, discovery và rich canonical surfaces

Sau Stage 18.1:

- production search ưu tiên PostgreSQL với ranking rõ ràng;
- hoàn thiện canonical pages cho Artist, Group, Release, Recording và Work;
- provider destination/media selector thể hiện availability, freshness và provenance rõ ràng;
- structured metadata, canonical URL và SEO hoàn thiện trước public indexing rộng rãi;
- mở rộng provider breadth theo evidence quality và official API capability, không theo số lượng connector.

## Nhóm 19.x — Giá trị cho người dùng cá nhân

- follow Artist/Group;
- save nội dung và private collections;
- discovery dựa trên hành động rõ ràng của người dùng;
- chỉ kết nối provider account khi API chính thức và policy cho phép.

## Nhóm 20.x — Production readiness

- deployment, queue và scheduler production;
- observability và provider operational metrics;
- security headers, backup và recovery;
- sitemap, structured data và production SEO verification.

## Quy tắc roadmap

- Mỗi stage bắt đầu từ accepted use case + executable task contract.
- Field/state mới của provider phải có authority trước migration/application use.
- Một feature chưa đóng nếu PostgreSQL, quality, candidate và canonical verification chưa đạt authority hiện hành.
- `DEVELOPMENT_STATE.md` phải được cập nhật cùng logical changeset khi blocker/evidence/next action thay đổi.
- Roadmap không lưu debug diary hoặc verification transcript; các dữ kiện vận hành ngắn hạn thuộc Development State.
