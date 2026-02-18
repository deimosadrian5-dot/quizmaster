FROM composer:2 AS composer

FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update \
    && apt-get install -y git curl zip unzip sqlite3 libsqlite3-dev libxml2-dev liboniguruma-dev \
    && docker-php-ext-install pdo_sqlite mbstring xml \
    && rm -rf /var/lib/apt/lists/*

# Get composer from composer image
COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Create .env for build-time artisan commands
RUN printf "APP_NAME=QuizMaster\n\
APP_ENV=production\n\
APP_KEY=base64:JI1lz1T02QxYhbcGC7yNDy6WtvTltIUe5VSMcUXiB6w=\n\
APP_DEBUG=false\n\
APP_URL=http://localhost\n\
DB_CONNECTION=sqlite\n\
LOG_CHANNEL=stack\n\
CACHE_DRIVER=file\n\
SESSION_DRIVER=file\n\
QUEUE_CONNECTION=sync\n" > .env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Prepare storage & database (bake seeded data into image)
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data \
    && chmod -R 775 storage bootstrap/cache \
    && touch database/database.sqlite \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Render sets PORT env var dynamically
ENV PORT=10000
EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=$PORT
