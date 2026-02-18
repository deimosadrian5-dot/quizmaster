FROM composer:2

RUN apk add --no-cache sqlite sqlite-dev oniguruma-dev \
    && docker-php-ext-install pdo_sqlite mbstring

WORKDIR /app
COPY . .

RUN printf "APP_NAME=QuizMaster\nAPP_ENV=production\nAPP_KEY=base64:JI1lz1T02QxYhbcGC7yNDy6WtvTltIUe5VSMcUXiB6w=\nAPP_DEBUG=false\nDB_CONNECTION=sqlite\nCACHE_DRIVER=file\nSESSION_DRIVER=file\n" > .env

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data \
    && chmod -R 775 storage bootstrap/cache \
    && touch database/database.sqlite \
    && php artisan migrate --force \
    && php artisan db:seed --force \
    && php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

ENV PORT=10000
EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=$PORT
