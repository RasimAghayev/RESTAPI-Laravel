#!/bin/sh
set -e
cd /var/www/html

if [ ! -f .env ]; then
    cp .env.example .env
fi

APP_KEY_LINE=$(grep '^APP_KEY=' .env 2>/dev/null | cut -d= -f2- || true)
if [ -z "$APP_KEY_LINE" ]; then
    php artisan key:generate --force >/dev/null 2>&1 || true
fi

exec "$@"
