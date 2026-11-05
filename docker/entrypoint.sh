#!/bin/sh
set -eu

mkdir -p \
    /var/lib/laravel \
    storage/app/private \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    if [ ! -s /var/lib/laravel/app.key ]; then
        php artisan key:generate --show > /var/lib/laravel/app.key
    fi

    APP_KEY="$(cat /var/lib/laravel/app.key)"
    export APP_KEY
fi

php artisan migrate --force

chown -R www-data:www-data \
    /var/lib/laravel \
    storage \
    bootstrap/cache

exec "$@"
