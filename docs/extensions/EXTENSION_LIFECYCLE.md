# Extension Lifecycle

SongChart triển khai extension theo ba cấp:

1. Local package installation.
2. Managed upgrade with rollback.
3. Signed distribution/marketplace.

Marketplace không phải yêu cầu MVP.

## Trạng thái

- uploaded
- inspecting
- rejected
- staged
- installed
- disabled
- enabled
- upgrade_pending
- upgrade_failed
- rollback_pending
- uninstall_pending
- uninstalled
- quarantined

ZIP được upload chưa được xem là đã cài hoặc đáng tin.

## Luồng bắt buộc

```text
Upload
→ Extract vào quarantine
→ Validate cấu trúc
→ Parse manifest
→ Tính SHA-256
→ Compatibility preflight
→ Review permission/capability
→ Resolve dependency
→ Stage release
→ Tạo snapshot
→ Chạy migration/hook
→ Rebuild cache/assets
→ Health check
→ Enable/activate
→ Finalize
```

Sau khi đã tạo snapshot, lỗi phải dẫn tới rollback hoặc quarantine.

## Giai đoạn E1 — dùng ngay

Bắt buộc:
- chống ZIP traversal;
- validate manifest;
- SHA-256;
- kiểm tra core/PHP/PHP extension;
- phát hiện package conflict;
- preflight/dry-run;
- cài ở trạng thái disabled;
- audit log;
- không chạy code lúc inspect;
- không ghi đè release đang active.

## Giai đoạn E2 — production upgrade

Bắt buộc trước khi cho phép package bên thứ ba:
- staged release;
- active release pointer;
- snapshot;
- database backup reference;
- migration declaration;
- health check;
- code rollback;
- upgrade history;
- rollback command.

Không được mặc định coi database rollback là an toàn.

## Giai đoạn E3 — signed distribution

Chỉ cần khi phân phối package:
- publisher identity;
- signing key;
- detached signature;
- checksum manifest;
- key rotation/revocation;
- static/security review;
- package withdrawal;
- update feed;
- license/privacy declarations.

## Nguyên tắc lỗi

- Không tương thích core/PHP: reject.
- Thiếu dependency: reject.
- Circular dependency: reject.
- Checksum sai: quarantine.
- Signature không hợp lệ: quarantine.
- Migration lỗi: disable, rollback code, giữ diagnostic.
- Health check lỗi: không enable.
