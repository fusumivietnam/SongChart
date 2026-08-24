# Extension Upgrade and Rollback

Không nâng cấp bằng cách ghi đè thư mục đang active.

```text
Inspect
→ Stage version mới
→ Snapshot
→ Migrate
→ Health check
→ Switch active pointer
→ Giữ version cũ
```

## Layout

```text
plugins/vendor/plugin/
├── releases/
│   ├── 1.3.0/
│   └── 1.4.0/
├── shared/
└── current pointer
```

Trên Windows có thể dùng registry pointer thay symlink.

## Snapshot tối thiểu

- active version/path;
- registry record;
- validated config;
- migration baseline;
- database backup reference;
- generated assets;
- checksum/signature metadata.

## Migration

Mỗi migration khai báo:
- reversible;
- destructive;
- estimated impact;
- backup requirement;
- minimum core;
- old-code compatibility.

Ưu tiên expand-and-contract. Không drop/rename field trong cùng release đầu tiên ngừng đọc field đó.

## Rollback

- Code: đổi active pointer.
- Config: restore snapshot.
- Database: chỉ chạy down migration khi được đánh dấu safe.
- Data: cần compensating action do plugin định nghĩa.

Giữ tối thiểu hai release thành công gần nhất.
