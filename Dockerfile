FROM php:8.3-fpm

# Install dependencies untuk Laravel
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        intl \
        bcmath \
        gd

RUN pecl install redis \
    && docker-php-ext-enable redis
RUN docker-php-ext-install opcache

    # Install Node.js & npm (misal versi 20)
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

# Install vendor tanpa menjalankan script Laravel
RUN composer install --no-dev --prefer-dist --no-interaction --no-scripts

# Generate APP_KEY
CMD ["sh", "-c", "php artisan key:generate --ansi && php-fpm"]

# Install npm dependencies untuk Vite/Laravel Mix
RUN npm install

# Build frontend (Vite)
RUN npm run build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
