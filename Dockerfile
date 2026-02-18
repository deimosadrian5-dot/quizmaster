FROM composer:2 AS composer

FROM php:8.2-cli

# Install extensions one step at a time for reliability
RUN apt-get update \
    && apt-get install -y git curl zip unzip sqlite3 libsqlite3-dev libxml2-dev liboniguruma-dev \
    && docker-php-ext-install pdo_sqlite mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# Get composer from composer image
COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup database and cache
RUN touch database/database.sqlite \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]
