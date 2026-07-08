FROM php:8.3-fpm

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_MAX_ACCELERATED_FILES=10000
ENV PHP_OPCACHE_MEMORY_CONSUMPTION=128
ENV PHP_OPCACHE_INTERNED_STRINGS_BUFFER=16

RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zlib1g-dev \
    default-mysql-client \
    nginx \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock* ./
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader

COPY package.json package-lock.json* ./
RUN npm install --no-audit --no-fund

COPY . .

RUN npm run build \
    && (php artisan config:cache || true) \
    && (php artisan route:cache || true) \
    && (php artisan view:cache || true) \
    && (php artisan storage:link || true) \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["/usr/local/bin/entrypoint.sh"]
