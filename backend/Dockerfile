FROM php:8.4-cli-alpine

RUN apk add --no-cache \
    git curl libzip-dev zip unzip postgresql-dev \
    oniguruma-dev freetype-dev libjpeg-turbo-dev libpng-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring zip gd bcmath

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload --optimize

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]