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
RUN echo "APP_NAME=QuizMaster\n\
APP_ENV=production\n\
APP_KEY=base64:JI1lz1T02QxYhbcGC7yNDy6WtvTltIUe5VSMcUXiB6w=\n\
APP_DEBUG=false\n\
APP_URL=http://localhost\n\
DB_CONNECTION=sqlite\n\
LOG_CHANNEL=stack\n\
CACHE_DRIVER=file\n\
SESSION_DRIVER=file\n\
QUEUE_CONNECTION=sync" > .env

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup database and cache
RUN mkdir -p database \
    && touch database/database.sqlite \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Render sets PORT dynamically (default 10000)
ENV PORT=10000
EXPOSE ${PORT}

# Use shell form so $PORT is expanded at runtime
CMD php artisan serve --host=0.0.0.0 --port=$PORT
