# Theme Package Standard

Required package structure:

```text
vendor-theme/
├── theme.json
├── resources/
│   ├── views/
│   ├── css/
│   └── js/
├── public/
├── README.md
└── CHANGELOG.md
```

Minimum `theme.json`:

```json
{
  "type": "theme",
  "name": "Example Theme",
  "slug": "vendor/example-theme",
  "version": "1.0.0",
  "core": {"constraint": "^1.0"},
  "parent": "songchart/default",
  "supports": ["light", "dark", "responsive"]
}
```

Themes override Blade views by matching core view paths. They must not contain business logic, migrations, provider API calls or authorization rules.
