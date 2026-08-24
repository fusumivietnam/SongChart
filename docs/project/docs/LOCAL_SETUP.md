# Local Setup with Laragon

## Required

- PHP 8.4
- Composer 2
- Node.js LTS
- PostgreSQL 18.x
- Git

Optional initially:
- Redis
- Meilisearch
- Mailpit

## Bootstrap

```bash
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan test
```

Configure a Laragon local domain such as `songchart.test` and HTTPS where possible.

Do not require Redis or Meilisearch until the corresponding feature is enabled. Keep local defaults easy to start.


## Release verification

Docker Desktop + WSL2 is the primary local application runtime.

```bat
songchart dev setup
songchart dev up
songchart test
songchart verify
```
 Use the unified Docker-first CLI; Laragon is compatibility-only. Before packaging a closure candidate, run the isolated canonical verification lane:

```bat
verify-songchart.bat
```

This uses PostgreSQL 18 in Docker and does not use the Laragon development database.
