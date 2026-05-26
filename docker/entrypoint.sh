#!/usr/bin/env bash
set -e

cd /var/www/html

# Generate APP_KEY on first run
if [ ! -f storage/.app_key_generated ]; then
    php artisan key:generate --force || true
    touch storage/.app_key_generated
fi

# Wait for DB and migrate
echo "Running migrations..."
php artisan migrate --force || true

# Cache config/routes/views for prod
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Ensure storage symlink exists
php artisan storage:link || true

# Fix permissions on mounted volume
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

exec "$@"
