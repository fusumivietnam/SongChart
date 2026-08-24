# Plugin Package Standard

Required package structure:

```text
vendor-plugin/
├── plugin.json
├── src/
│   └── PluginServiceProvider.php
├── database/migrations/
├── resources/views/
├── routes/
├── tests/
├── README.md
└── CHANGELOG.md
```

Minimum `plugin.json`:

```json
{
  "type": "plugin",
  "name": "Example Plugin",
  "slug": "vendor/example-plugin",
  "version": "1.0.0",
  "core": {"constraint": "^1.0"},
  "service_provider": "Vendor\ExamplePlugin\PluginServiceProvider",
  "autoload": {"namespace": "Vendor\ExamplePlugin\", "path": "src"},
  "capabilities": [],
  "permissions": [],
  "external_services": [],
  "uninstall": {"preserve_data_by_default": true}
}
```

A plugin may register routes, views, migrations, commands and capability contributors only through its service provider and published extension contracts.
