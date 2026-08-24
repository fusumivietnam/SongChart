# SongChartWeb

SongChartWeb là nền tảng music discovery, metadata, knowledge graph và điều hướng người nghe đến các nền tảng phát hợp pháp.

## Product boundary

SongChartWeb:
- Chuẩn hóa artist, release, recording, provider identity và external links.
- Hỗ trợ tìm kiếm, khám phá, playlist cá nhân và embedded player khi provider cho phép.
- Không lưu trữ, tải lại hoặc phân phối file audio/video của bên thứ ba.
- Không xây chart tổng hợp ở giai đoạn đầu.
- Không phụ thuộc vào một provider duy nhất.

## Architecture

- Laravel modular monolith.
- Server-rendered HTML ưu tiên SEO.
- Livewire chỉ dùng cho vùng tương tác.
- Provider integrations nằm sau contract và compliance registry.
- PostgreSQL là nguồn dữ liệu chuẩn.
- Queue xử lý import, sync, enrichment và webhook.
- Search engine là projection; database luôn là source of truth.

Đọc theo thứ tự:
1. `AGENTS.md`
2. `docs/PRODUCT.md`
3. `docs/SCOPE.md`
4. `docs/ARCHITECTURE.md`
5. `docs/DATA_MODEL.md`
6. `docs/ENGINEERING_RULES.md`
7. `docs/ROADMAP.md`
