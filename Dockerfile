FROM php:8.3-fpm

WORKDIR /var/www/html

ENV DEBIAN_FRONTEND=noninteractive
ENV PHP_OPCACHE_ENABLE=1
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_MAX_ACCELERATED_FILES=10000
ENV PHP_OPCACHE_MEMORY_CONSUMPTION=128
ENV PHP_OPCACHE_INTERNED_STRINGS_BUFFER=16
ENV COMPOSER_ALLOW_SUPERUSER=1

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
    libgd-dev \
    default-mysql-client \
    nginx

# Install PHP extensions - separating this to ensure gd is available for composer
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip opcache && \
    docker-php-ext-enable gd opcache && \
    pecl install redis && \
    docker-php-ext-enable redis

# Verify that gd extension is available
RUN php -m | grep gd || (echo "GD extension failed to install" && exit 1)

# Install composer and nodejs
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock* ./
COPY package.json package-lock.json* ./

# Install PHP dependencies first (without post-autoload-dump scripts which require artisan)
RUN composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader --no-scripts

COPY . .

# Now run composer scripts after copying all files including artisan
RUN composer dump-autoload --no-dev --optimize && \
    npm install --no-audit --no-fund && \
    npm run build && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache && \
    php artisan storage:link || true && \
    chmod -R 775 storage bootstrap/cache && \
    chown -R www-data:www-data /var/www/html

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8080

CMD ["/usr/local/bin/entrypoint.sh"]

