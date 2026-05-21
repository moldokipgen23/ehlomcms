# Ehlom OS — Laravel app container for Bunny Magic Containers.
FROM php:8.4-fpm-alpine

# System packages + PHP extensions (pdo_mysql, gd for PDFs, intl, zip, bcmath…).
RUN apk add --no-cache \
        nginx supervisor nodejs npm git \
        icu-dev libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev oniguruma-dev \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring bcmath gd zip exif intl opcache \
    && apk del .build-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP deps first (better build caching).
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

# App code + frontend build.
COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && npm ci && npm run build && rm -rf node_modules \
    && chown -R www-data:www-data storage bootstrap/cache

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
