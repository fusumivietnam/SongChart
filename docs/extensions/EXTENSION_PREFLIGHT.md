# Extension Preflight Contract

Preflight chạy trước khi package đi vào thư mục active.

## Kiểm tra package

- chỉ có một extension root hợp lệ;
- không có absolute path hoặc `../`;
- giới hạn dung lượng ZIP, dung lượng giải nén và số file;
- không có filename trùng nhau do khác hoa/thường;
- manifest type khớp lệnh;
- slug khớp đường dẫn;
- semantic version hợp lệ;
- SHA-256 đã được tính.

## Tương thích

- core constraint;
- PHP constraint;
- PHP extensions;
- database support;
- extension API level;
- parent theme;
- schema version.

## Dependency

- dependency tồn tại;
- version phù hợp;
- dependency đang enabled khi bắt buộc;
- không circular;
- không conflict;
- optional dependency chỉ tạo warning.

## Permission/risk report

Hiển thị bằng ngôn ngữ tự nhiên:
- quyền admin mới;
- routes;
- scheduled/queue jobs;
- external domains;
- provider/token access;
- user-data access;
- tables/migrations;
- storage;
- webhooks;
- uninstall behavior.

## Kết quả

- pass;
- pass_with_warnings;
- fail.

Phải trả:
- blocking issues;
- warnings;
- operator confirmations;
- filesystem changes;
- database changes;
- giới hạn rollback.

## Dry-run

```bash
php artisan plugin:inspect package.zip
php artisan theme:inspect package.zip
```

Inspect không load service provider và không chạy package code.
