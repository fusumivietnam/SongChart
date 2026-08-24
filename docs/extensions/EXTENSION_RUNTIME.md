# Extension Runtime

## Status

The source includes a real filesystem-based plugin/theme runtime, not documentation only.

## Stable locations

```text
plugins/<vendor>/<plugin>/
themes/<vendor>/<theme>/
```

Core remains in `app/`, `routes/`, `resources/` and `database/`. Extensions must never be copied into core directories.

## ZIP installation

```bash
php artisan plugin:install storage/app/packages/example-plugin.zip
php artisan plugin:install storage/app/packages/example-plugin.zip --enable
php artisan theme:install storage/app/packages/example-theme.zip
php artisan theme:install storage/app/packages/example-theme.zip --activate
php artisan theme:activate vendor/theme
```

ZIP packages must contain `plugin.json` or `theme.json` at the archive root, or inside exactly one top-level directory.

## Safety boundary

The installer rejects absolute paths, drive paths, `..` traversal and NUL path names. It never executes scripts during extraction. Installation and enablement are separate decisions.

## Upgrade policy

Version 1 includes safe install and activation foundations. In-place upgrade, rollback snapshots, signatures and admin upload UI must be implemented as a separate reviewed milestone. Do not overwrite an installed package manually.


## Lifecycle governance

Mọi thay đổi installer/plugin/theme phải đọc toàn bộ tài liệu `docs/extensions/`.

Cho đến khi hoàn thành E2:
- production upgrade chạy trong maintenance window;
- package cài ở trạng thái disabled;
- backup database trước migration;
- cấm overwrite-in-place;
- không hứa database rollback nếu migration destructive.
