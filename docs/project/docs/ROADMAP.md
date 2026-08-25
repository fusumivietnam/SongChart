# Lộ trình sản phẩm

Trạng thái: tài liệu định hướng cho các hạng mục hiện tại và tương lai. Tài liệu này không quyết định stage hiện tại của repository. `README.md` là nguồn chuẩn cho stage hiện tại; `docs/project/DEVELOPMENT_HISTORY.md` lưu lịch sử các stage đã hoàn tất.

## Nguyên tắc đọc roadmap

- Chỉ giữ các hạng mục đang triển khai hoặc chưa triển khai.
- Công việc đã hoàn tất phải chuyển sang `docs/project/DEVELOPMENT_HISTORY.md`.
- Tên kỹ thuật quan trọng được giữ bằng tiếng Anh trong ngoặc ở lần xuất hiện đầu để dễ đối chiếu code và contract.
- Giao diện người dùng và Admin ưu tiên thuật ngữ tiếng Việt rõ nghĩa; không phơi thuật ngữ nội bộ nếu người dùng không cần biết.

## Stage 18.0 — Trải nghiệm media YouTube

Mục tiêu: giúp người dùng xem hoặc mở nội dung YouTube đã được SongChart xác minh từ trang Recording, đồng thời vẫn giữ kiểm soát về độ mới, khả năng nhúng và quota.

Phạm vi đang triển khai:

- phát video YouTube đã duyệt và còn hiệu lực bằng iframe riêng tư (`youtube-nocookie.com`);
- khi không thể nhúng, hiển thị liên kết mở YouTube bên ngoài kèm lý do dễ hiểu;
- kiểm tra độ mới của điểm đến (destination freshness) và tự đánh dấu nội dung cần kiểm tra lại;
- làm mới trạng thái video bằng job nền có giới hạn;
- theo dõi hạn mức API (quota) và tình trạng sử dụng trong Admin;
- đưa các chính sách vận hành như thời hạn kiểm tra, bật/tắt nhúng, lịch làm mới và ngưỡng quota vào Admin thay vì hard-code;
- chỉ giữ secret hạ tầng như API key trong environment/secret store; Admin không trở thành trình sửa `.env`;
- hoàn thiện trình bày media trên Recording trước, sau đó mới mở rộng sang Artist khi có use case rõ ràng.

Điều kiện an toàn:

- chỉ destination có trạng thái đã duyệt (`approved`) mới được hiển thị công khai;
- chỉ YouTube destination còn mới, cho phép nhúng (`embeddable`) và có resource ID hợp lệ mới được phát trong SongChart;
- destination cũ hoặc không cho phép nhúng không được tự động phát;
- SongChart không lưu hoặc re-host file media của provider.

## Nhóm 18.x — Tìm kiếm và khám phá công khai

Sau khi media experience ổn định:

- tìm kiếm production ưu tiên PostgreSQL, có ranking rõ ràng;
- hoàn thiện trang canonical cho Nghệ sĩ, Nhóm nhạc, Bản phát hành, Bản thu và Tác phẩm;
- trình chọn nền tảng nghe/xem hiển thị rõ khả dụng và thời điểm kiểm tra gần nhất;
- metadata có cấu trúc, canonical URL và SEO được hoàn thiện trước khi public indexing rộng rãi.

## Nhóm 19.x — Giá trị cho người dùng cá nhân

- theo dõi (follow) Nghệ sĩ/Nhóm nhạc;
- lưu nội dung (save) và bộ sưu tập riêng tư;
- khám phá theo quy tắc dựa trên hành động rõ ràng của người dùng;
- chỉ kết nối tài khoản provider khi API chính thức và chính sách của provider cho phép.

## Nhóm 20.x — Sẵn sàng vận hành production

- chuẩn triển khai, queue và scheduler;
- quan sát hệ thống (observability) và chỉ số vận hành provider;
- hoàn thiện security headers, backup và quy trình khôi phục;
- sitemap, structured data và kiểm chứng SEO trên production.

## Thuật ngữ dùng trong UI/Admin

| Thuật ngữ kỹ thuật | Cách hiển thị ưu tiên | Ghi chú |
|---|---|---|
| Provider | Nguồn dữ liệu / Nền tảng | Dùng “Nền tảng” khi nói YouTube; “Nguồn dữ liệu” khi nói MusicBrainz hoặc provenance. |
| Provider destination | Điểm đến trên nền tảng | Ví dụ video YouTube đã liên kết với một Recording. |
| Canonical | Dữ liệu chuẩn | Có thể giữ “canonical” trong màn kỹ thuật dành cho admin nâng cao. |
| Canonical admission | Duyệt vào dữ liệu chuẩn | Tránh “admission” trên UI người dùng. |
| Candidate evidence | Bằng chứng chờ duyệt | Dùng trong Admin review flow. |
| Metadata assertion | Bằng chứng metadata | Tên model/contract vẫn giữ nguyên trong code. |
| Freshness | Độ mới / Thời hạn kiểm tra | UI nên nói “Đã kiểm tra ngày…” hoặc “Cần kiểm tra lại”. |
| Embeddable | Có thể phát trực tiếp | Dễ hiểu hơn “có thể nhúng”. |
| Outbound fallback | Mở trên nền tảng | Không dùng “fallback” trên UI. |
| Review state | Trạng thái duyệt | Ví dụ: Chờ duyệt / Đã duyệt / Đã từ chối. |
| Quota | Hạn mức API | Có thể ghi “Hạn mức YouTube API” trong Admin. |
| Enrichment | Bổ sung dữ liệu | Ví dụ “Kế hoạch bổ sung dữ liệu”. |
| Enrichment plan | Kế hoạch bổ sung dữ liệu | Không cần dùng từ “enrichment” trên frontend. |
| Identity bridge | Liên kết định danh | Mô tả việc nối MusicBrainz/ISRC/provider identity vào một thực thể SongChart. |
| Entity passport | Hồ sơ chất lượng dữ liệu | Dùng cho màn kỹ thuật; frontend phổ thông có thể rút gọn thành “Chất lượng dữ liệu”. |
| Provenance | Nguồn gốc dữ liệu | UI có thể dùng “Nguồn dữ liệu”. |
| Operational visibility | Theo dõi vận hành | Dùng trong Admin. |

## Quy tắc roadmap

- Mỗi stage triển khai phải bắt đầu từ use case đã được chấp nhận và contract thực thi được.
- Field/state mới của provider phải được khai báo trong contract authority trước khi migration hoặc application code sử dụng.
- Một feature chưa được coi là đóng release nếu chưa có đầy đủ PostgreSQL, quality và canonical verification theo authority hiện hành.
- SQLite không phải môi trường xác minh có thẩm quyền.
- PostgreSQL 18 Docker persistence dùng parent mount `/var/lib/postgresql`; mount cũ `/var/lib/postgresql/data` không được dùng cho dev stack.
