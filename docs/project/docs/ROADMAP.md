# Lộ trình sản phẩm

Trạng thái: tài liệu định hướng cho hạng mục đang triển khai và tương lai. Tài liệu này không quyết định stage hiện tại; `docs/project/DEVELOPMENT_STATE.md` là nguồn chuẩn cho operational current state, task contract của stage đang hoạt động sở hữu scope/acceptance, và `docs/project/DEVELOPMENT_HISTORY.md` sở hữu chronology đã hoàn tất.

## Nguyên tắc đọc roadmap

- Chỉ giữ hạng mục đang triển khai hoặc chưa triển khai.
- Công việc đã hoàn tất phải chuyển sang Development History sau governed acceptance.
- Roadmap không thay thế task contract, candidate evidence hay `DEVELOPMENT_STATE.md`.
- UI/Admin ưu tiên thuật ngữ tiếng Việt rõ nghĩa; code/contract giữ tên kỹ thuật khi cần đối chiếu.

## Stage 18.2 — Public Search & Canonical Surfaces

Mục tiêu tiếp theo: biến nền canonical/provider đã được governance thành trải nghiệm tìm kiếm và duyệt catalog có giá trị trực tiếp cho người dùng.

Trọng tâm:

- production search trên PostgreSQL với ranking và tie-break rõ ràng, deterministic;
- hoàn thiện canonical public pages cho Artist, Group, Release, Recording và Work;
- search facets, pagination, empty states và canonical URLs nhất quán;
- giữ public read models độc lập với raw provider payload và provider-specific transport;
- mọi destination/media hiển thị phải tiếp tục đi qua availability, freshness, provenance và policy hiện hành;
- không mở ingestion pipeline thứ hai và không cho search layer mutate canonical data.

Điều kiện an toàn:

- PostgreSQL vẫn là database authority cho verification/release;
- public search không truy cập provider API trực tiếp trong request path;
- canonical URLs không phụ thuộc provider identity;
- provider payload không tự động mutate canonical entities;
- Stage 18.2 chỉ bắt đầu sau khi post-18.1 repository hygiene được xác nhận sạch.

## Stage 18.3 — Public Metadata & SEO Readiness

- structured data cho canonical entity pages;
- canonical metadata, title/description và OpenGraph/social metadata;
- sitemap và indexability policy;
- duplicate-content/canonical-link verification;
- production SEO verification trước public indexing rộng rãi.

## Stage 18.4 — Provider Destination & Media Quality

- destination/media selector theo availability, freshness và provenance;
- deterministic preference/ranking giữa approved destinations;
- operational visibility cho stale/unavailable destinations;
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
- production SEO/indexing rollout và recovery verification.

## Quy tắc roadmap

- Mỗi stage bắt đầu từ accepted use case + executable task contract.
- Field/state mới của provider phải có authority trước migration/application use.
- Một feature chưa đóng nếu PostgreSQL, quality, candidate và canonical verification chưa đạt authority hiện hành.
- `DEVELOPMENT_STATE.md` phải được cập nhật cùng logical changeset khi blocker/evidence/next action thay đổi.
- Roadmap không lưu debug diary hoặc verification transcript; các dữ kiện vận hành ngắn hạn thuộc Development State.
