#!/bin/sh
set -e

if [ -f artisan ]; then
    mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
    chmod -R 777 storage bootstrap/cache
fi

mkdir -p /tmp/laravel-views
chmod 1777 /tmp/laravel-views

if [ -d /var/www/public-dist ] && [ -z "$(ls -A /var/www/html/public 2>/dev/null)" ]; then
    cp -a /var/www/public-dist/. /var/www/html/public/
fi

exec "$@"
