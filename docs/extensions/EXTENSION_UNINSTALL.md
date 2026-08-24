# Extension Uninstall and Data Retention

Phân biệt:

- Disable: dừng runtime, giữ code/data.
- Remove code: xóa releases, giữ data/config.
- Uninstall: chạy cleanup workflow.
- Purge: xóa dữ liệu extension sau xác nhận.

Không dùng một nút “Delete” cho cả bốn hành động.

Mặc định preserve data.

Manifest phải khai báo:
- preserve_data_by_default;
- export support;
- cleanup handler;
- owned tables/files;
- retention days.

Purge phải:
- hiển thị record/file bị ảnh hưởng;
- có export nếu chứa user data;
- yêu cầu typed confirmation;
- yêu cầu recent 2FA ở production;
- ghi audit riêng.
