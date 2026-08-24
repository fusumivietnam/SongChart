# Extension Dependency Resolution

```json
{
  "requires": {
    "core": "^1.0",
    "php": "^8.3",
    "plugins": {
      "songchart/catalog-tools": "^2.0"
    },
    "extensions": ["intl", "mbstring"]
  },
  "suggests": {
    "plugins": {
      "songchart/analytics": "^1.0"
    }
  },
  "conflicts": {
    "plugins": {
      "legacy/catalog-importer": "*"
    }
  }
}
```

## Rules

- semantic version constraints;
- không circular dependency;
- plugin-to-plugin dependency chỉ qua stable public contract;
- plugin không phụ thuộc theme;
- child theme chỉ có một parent;
- optional dependency kiểm tra qua capability;
- disable dependency phải cảnh báo dependents;
- uninstall dependency bị chặn khi còn dependent active.

Không tách mọi feature thành plugin. Chỉ tách khi có lifecycle, owner, compliance boundary hoặc deployment độc lập.
