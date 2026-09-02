# SongChart

SongChart là nền tảng music discovery, metadata, knowledge graph và điều hướng người nghe đến các nền tảng phát hợp pháp.

## Product boundary

SongChart:
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
- Project Intelligence graph là snapshot eventual-consistency; không scan/rebuild source trong HTTP request.

Đọc theo thứ tự:
1. `PROJECT_AUTHORITY.md`
2. `docs/project/generated/development-state.json`
3. `docs/project/generated/project-context.json`
4. `docs/project/engineering/project-knowledge.json`
5. `docs/project/engineering/project-kernel-contract.json`
6. `docs/project/engineering/architecture-graph-contract.json`
7. `docs/START_HERE.md`

Historical stage documents là evidence; không dùng chúng làm current-state authority.
