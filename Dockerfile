FROM php:8.4-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    postgresql-dev \
    icu-dev \
    zip \
    libzip-dev

RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    gd \
    intl \
    mbstring \
    zip \
    bcmath \
    exif

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --optimize-autoloader --no-interaction

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
