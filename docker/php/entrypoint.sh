#!/bin/bash
set -e

# Wait for DB to be ready (extra safety)
echo "Waiting for database..."
until php -r "new PDO('mysql:host=${DB_HOST:-db};port=${DB_PORT:-3306};dbname=${DB_DATABASE}', '${DB_USERNAME:-root}', '${DB_PASSWORD}');" 2>/dev/null; do
    sleep 2
done
echo "Database ready."

# Run migrations
php artisan migrate --force

# Cache config for performance
php artisan config:cache
php artisan route:cache

# Storage link (ignore error if already exists)
php artisan storage:link || true

exec "$@"
