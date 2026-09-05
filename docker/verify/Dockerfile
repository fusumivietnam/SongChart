FROM composer:2 AS composer
FROM node:24-bookworm-slim AS node

FROM php:8.5-cli-bookworm

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
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
        pcntl \
        pdo_pgsql \
        sockets \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && php -r '$required = ["bcmath", "curl", "dom", "intl", "mbstring", "pcntl", "pdo_pgsql", "redis", "sockets", "xml", "zip"]; foreach ($required as $extension) { if (! extension_loaded($extension)) { fwrite(STDERR, "Missing PHP extension: {$extension}\\n"); exit(1); } } echo "PHP extension smoke check passed.\\n";' \
    && git config --system --add safe.directory /workspace \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=node /usr/local/ /usr/local/
COPY docker/verify/php.ini /usr/local/etc/php/conf.d/99-songchart-verify.ini

WORKDIR /workspace

CMD ["bash", "scripts/canonical-verify.sh"]
