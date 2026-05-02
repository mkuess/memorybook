FROM php:8.2-fpm-alpine

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
    libzip-dev \
    nodejs \
    npm

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
    exif \
    pcntl

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --optimize-autoloader --no-interaction --no-dev

RUN npm ci && npm run build && rm -rf node_modules

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
