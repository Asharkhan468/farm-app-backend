FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip curl libpq-dev zip \
    && docker-php-ext-install pdo pdo_pgsql

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN php artisan config:clear || true
RUN php artisan cache:clear || true

EXPOSE 10000

CMD php artisan serve --host=0.0.0.0 --port=10000