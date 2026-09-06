FROM composer:2 AS composer
FROM node:24-bookworm-slim AS frontend

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY public ./public
COPY vite.config.* ./
RUN npm run build

FROM php:8.5-fpm-bookworm AS php-base

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        libicu-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
        zip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_pgsql \
        posix \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && php -r '$required = ["bcmath", "curl", "dom", "intl", "mbstring", "opcache", "pcntl", "pdo_pgsql", "posix", "redis", "xml", "zip"]; foreach ($required as $extension) { if (! extension_loaded($extension)) { fwrite(STDERR, "Missing PHP extension: {$extension}\\n"); exit(1); } }' \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html

FROM php-base AS vendor
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --no-scripts \
    --classmap-authoritative

FROM php-base AS app
ARG VCS_REF=unknown
ARG BUILD_DATE=unknown
LABEL org.opencontainers.image.title="SongChart" \
      org.opencontainers.image.revision="$VCS_REF" \
      org.opencontainers.image.created="$BUILD_DATE"
ENV APP_ENV=production \
    APP_DEBUG=false

COPY . .
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build
RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction --no-scripts \
    && php artisan package:discover --ansi \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm", "-F"]

FROM caddy:2.11.3-alpine AS edge
ARG VCS_REF=unknown
ARG BUILD_DATE=unknown
LABEL org.opencontainers.image.title="SongChart Edge" \
      org.opencontainers.image.revision="$VCS_REF" \
      org.opencontainers.image.created="$BUILD_DATE"
COPY docker/production/Caddyfile /etc/caddy/Caddyfile
COPY --from=app /var/www/html/public /srv/public
EXPOSE 80 443
