#!/bin/bash
set -e

echo "Starting Docker Entrypoint..."

if [ -f /app/.env.docker ]; then
    echo "Syncing .env.docker to .env..."
    cp /app/.env.docker /app/.env
fi

if [ -f /app/docker/supervisord.conf ]; then
    cp /app/docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
fi

# Wait for PostgreSQL
if [ -n "$DB_HOST" ]; then
    echo "Waiting for PostgreSQL at $DB_HOST:${DB_PORT:-5432}..."
    until nc -z -v -w30 "$DB_HOST" "${DB_PORT:-5432}"; do
        echo "PostgreSQL is unavailable - sleeping..."
        sleep 2
    done
    echo "PostgreSQL is ready!"
fi

# Wait for Redis
if [ -n "$REDIS_HOST" ]; then
    echo "Waiting for Redis at $REDIS_HOST:${REDIS_PORT:-6379}..."
    until nc -z -v -w30 "$REDIS_HOST" "${REDIS_PORT:-6379}"; do
        echo "Redis is unavailable - sleeping..."
        sleep 2
    done
    echo "Redis is ready!"
fi

cd /app

if [ ! -d "/app/vendor/laravel/octane" ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Ensure storage link exists
php artisan storage:link --force || true

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

echo "Starting Supervisor..."
exec "$@"
