FROM php:8.5-cli-bookworm

# Set working directory
WORKDIR /app

# Prevent interactive prompts during apt
ENV DEBIAN_FRONTEND=noninteractive

# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    git \
    unzip \
    zip \
    curl \
    netcat-openbsd \
    openssl \
    supervisor \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libssl-dev \
    libcurl4-openssl-dev \
    python3 \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions: pdo_pgsql, pgsql, pcntl, bcmath, gd, zip, sockets, openssl
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        pgsql \
        pcntl \
        bcmath \
        gd \
        zip \
        sockets \
    && docker-php-ext-enable openssl 2>/dev/null || true

# Install PECL extensions: phpredis & swoole
RUN pecl install redis \
    && docker-php-ext-enable redis

RUN printf "\n" | pecl install swoole \
    && docker-php-ext-enable swoole

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy tuned PHP runtime configuration
COPY docker/php.ini /usr/local/etc/php/php.ini

# Copy only dependency manifests first for better layer caching
COPY composer.json composer.lock /app/

# Install Composer dependencies (baked into the image)
RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts \
    && md5sum /app/composer.lock | awk '{print $1}' > /app/vendor/.composer_lock_hash

# Copy the rest of the application files
COPY . /app

# Run post-install scripts now that full app code is present
RUN composer run-script post-autoload-dump --no-interaction || true

# Ensure correct permissions for storage and bootstrap/cache
# storage/app is used by passport:keys when key env vars are not set
RUN mkdir -p storage/app storage/logs bootstrap/cache /var/log/supervisor \
    && chmod -R 775 storage bootstrap/cache

# Copy Supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expose Swoole Octane port
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
