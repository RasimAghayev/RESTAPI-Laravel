# syntax=docker/dockerfile:1
# Phase 5 container baseline — RESTAPI-Laravel (updated Phase 6)
# Multi-stage: base -> deps -> build -> test -> runtime
# The Laravel application lives under customer-invoice-api/; build context is the repo root.

# ------------------------------------------------------------------
# base — pinned runtime image, system deps, PHP extensions
# ------------------------------------------------------------------
ARG PHP_VERSION=8.5
FROM php:${PHP_VERSION}-fpm-alpine@sha256:951fb5b1780219b81d5daffa4cebef8e0077c537f3893d9790a5dfcd66ca334d AS base

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    APP_ENV=production

RUN apk add --no-cache git unzip zip icu-dev libzip-dev sqlite-dev \
    && docker-php-ext-install intl zip pcntl pdo_sqlite \
    && docker-php-ext-enable intl zip pcntl pdo_sqlite

COPY --from=composer:2@sha256:ca11f643d7791ebdd90a34fc20caf11380d434007ae539a9ea75cd9212e13dca /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# ------------------------------------------------------------------
# deps — Composer install (all deps incl. dev so the test stage can reuse)
# ------------------------------------------------------------------
FROM base AS deps

COPY customer-invoice-api/composer.json customer-invoice-api/composer.lock ./

RUN --mount=type=cache,target=/root/.composer/cache \
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts --no-plugins

# ------------------------------------------------------------------
# build — frontend assets (Vite)
# ------------------------------------------------------------------
FROM node:22-alpine@sha256:0e1754758c2df4cfa3e68d59e2e1b7374bb7fd9ea6328eaaacf15cc934af8202 AS build
WORKDIR /var/www/html

COPY customer-invoice-api/package.json ./
RUN --mount=type=cache,target=/root/.npm \
    npm install --no-audit --no-fund --legacy-peer-deps 2>/dev/null || true

COPY customer-invoice-api/ ./
RUN npm run build 2>/dev/null || true
RUN mkdir -p /var/www/html/public/build

# ------------------------------------------------------------------
# test — run the PHPUnit suite inside the container
# ------------------------------------------------------------------
FROM deps AS test

COPY customer-invoice-api/ ./

ENV DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    CACHE_DRIVER=array \
    SESSION_DRIVER=array \
    QUEUE_CONNECTION=sync \
    MAIL_MAILER=array \
    TELESCOPE_ENABLED=false

RUN composer dump-autoload --optimize --no-interaction --no-scripts \
    && php artisan key:generate --force --ansi 2>/dev/null || true \
    && php artisan package:discover --ansi 2>/dev/null || true \
    && php artisan migrate --force 2>/dev/null || true

RUN php vendor/phpunit/phpunit/phpunit --no-coverage

# ------------------------------------------------------------------
# runtime — slim php-fpm image without dev tooling
# ------------------------------------------------------------------
FROM base AS runtime

COPY --from=deps /var/www/html/vendor ./vendor
COPY customer-invoice-api/ ./
COPY --from=build /var/www/html/public/build ./public/build

# Prune dev dependencies for a lean runtime image
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader --no-scripts --no-plugins \
    && composer dump-autoload --optimize --no-interaction --no-dev --no-scripts 2>/dev/null || true \
    && addgroup -g 1000 laravel && adduser -D -G laravel -u 1000 laravel \
    && mkdir -p /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views /var/www/html/bootstrap/cache /run/nginx \
    && chown -R laravel:laravel /var/www/html \
    && chown -R laravel:laravel /run/nginx

COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh /usr/local/bin/laravel-entrypoint
RUN chmod +x /usr/local/bin/laravel-entrypoint

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD php -r "file_exists('/var/www/html/vendor/autoload.php') && exit(0);" || exit 1

EXPOSE 9000

USER laravel

ENTRYPOINT ["laravel-entrypoint"]
CMD ["php-fpm"]