FROM php:8.4-fpm-alpine3.23 AS base

# Dependencias del sistema
RUN apk update && apk upgrade --no-cache \
    && apk add --no-cache \
    postgresql-dev \
    zip unzip \
    libzip-dev \
    nginx \
    supervisor

RUN docker-php-ext-install pdo pdo_pgsql zip fileinfo

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan config:clear \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 8080

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

CMD ["/entrypoint.sh"]