# Composer compatibility fix for Laravel 13

Required constraints:

```json
"laravel/tinker": "^3.0",
"pestphp/pest-plugin-laravel": "^4.1"
```

After updating constraints:

```bat
rmdir /s /q vendor 2>nul
del composer.lock 2>nul
composer clear-cache
composer update --with-all-dependencies
```

The `setup` Composer script must not call `composer install` recursively. Dependency installation is performed before `composer setup`.
