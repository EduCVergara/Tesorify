#!/bin/sh
set -e

php artisan config:clear || true
php artisan cache:clear || true

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php artisan key:generate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true
php artisan migrate --force

php-fpm -D
nginx -g 'daemon off;'
