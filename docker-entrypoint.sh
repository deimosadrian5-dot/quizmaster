#!/bin/sh

# Ensure the persistent volume directory exists
mkdir -p /data

# If database doesn't exist on the persistent volume, create and seed it
if [ ! -f /data/database.sqlite ]; then
    echo "Creating fresh database..."
    touch /data/database.sqlite
    php artisan migrate --force
    php artisan db:seed --force
else
    echo "Database exists, running migrations..."
    php artisan migrate --force
fi

# Symlink the persistent database
ln -sf /data/database.sqlite /app/database/database.sqlite

# Clear and rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting QuizMaster..."
exec php artisan serve --host=0.0.0.0 --port=8080
