#!/bin/bash
set -e

# Install composer dependencies
composer install --no-dev --optimize-autoloader

# Create SQLite database
touch /opt/render/project/src/database/database.sqlite

# Run migrations and seed
php artisan migrate --force
php artisan db:seed --force

# Cache config
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Build complete!"
