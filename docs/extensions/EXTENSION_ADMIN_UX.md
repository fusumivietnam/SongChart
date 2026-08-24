# Extension Admin UX

## Menu

```text
Extensions
├── Installed
├── Upload package
├── Updates
├── Themes
├── Health
└── Trusted publishers
```

## Upload flow

1. Chọn ZIP.
2. Inspect.
3. Xem identity/publisher.
4. Xem compatibility.
5. Xem quyền và dữ liệu truy cập.
6. Xem dependencies/conflicts.
7. Xem filesystem/database changes.
8. Chọn install disabled hoặc install and enable.
9. Theo dõi progress.
10. Xem health result.

Dùng ngôn ngữ nghiệp vụ:
- “thêm cấu trúc dữ liệu” thay vì “run migration”;
- “thêm tác vụ nền” thay vì “queue hook”;
- “kết nối dịch vụ ngoài” thay vì “bind adapter”.

Upload/install chỉ dành cho super-admin.
Production upgrade yêu cầu recent password/2FA.
Theme phải preview được trước activate.
