FROM php:8.2-fpm

RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json ./
RUN composer install --no-interaction

COPY . /app/

CMD ["php-fpm", "-F"]