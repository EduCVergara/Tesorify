#!/bin/sh
set -e

# Create .env from environment variables if not present
if [ ! -f /var/www/html/.env ]; then
    cat > /var/www/html/.env <<EOF
APP_NAME=${APP_NAME:-Tesorify}
APP_ENV=${APP_ENV:-production}
APP_KEY=${APP_KEY:-}
APP_DEBUG=${APP_DEBUG:-false}
APP_URL=${APP_URL:-http://localhost}
DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_PORT=${DB_PORT:-3306}
DB_DATABASE=${DB_DATABASE:-tesorify}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}
SESSION_DRIVER=${SESSION_DRIVER:-database}
CACHE_STORE=${CACHE_STORE:-database}
QUEUE_CONNECTION=${QUEUE_CONNECTION:-database}
LOG_CHANNEL=${LOG_CHANNEL:-stack}
LOG_LEVEL=${LOG_LEVEL:-debug}
EOF
fi

php artisan config:clear || true
php artisan cache:clear || true

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    php artisan key:generate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true

# Try migrations but don't block startup
php artisan migrate --force 2>&1 | head -20 || echo "Migration failed or skipped - will retry"

# Start services
php-fpm -D
sleep 1

# Run migrations in background if they failed
(php artisan migrate --force 2>&1 || true) &

nginx -g 'daemon off;'

