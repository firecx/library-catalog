FROM php:8.2-fpm

WORKDIR /app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer.json ./
RUN composer install --no-interaction

COPY . /app/

CMD ["php-fpm", "-F"]