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

# vendor/ is baked into the image at build time — no composer install needed at runtime.
# Re-run package discovery to ensure all service providers are registered correctly.
echo "Running package discovery..."
php artisan package:discover --ansi

# Ensure storage link exists
php artisan storage:link --force || true

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Generate Passport encryption keys if they are not provided via env vars.
# When PASSPORT_PRIVATE_KEY / PASSPORT_PUBLIC_KEY are set in .env.docker the keys
# are loaded directly from the environment and no files are needed.
# If those vars are empty we generate the key files once into storage/app/.
if [ -z "$PASSPORT_PRIVATE_KEY" ] || [ -z "$PASSPORT_PUBLIC_KEY" ]; then
    echo "Passport env keys not set — generating key files..."
    php artisan passport:keys --force
else
    echo "Passport keys loaded from environment variables."
fi

# Create the Passport personal-access client if it does not already exist.
# --no-interaction ensures this is idempotent across container restarts.
# Create the Passport personal-access client if it does not already exist.
# We check the DB first to avoid duplicates across container restarts.
echo "Ensuring Passport personal access client exists..."
CLIENT_COUNT=$(php artisan tinker --execute="echo \Laravel\Passport\Client::where('grant_types', 'LIKE', '%personal_access%')->where('provider', 'users')->count();" 2>/dev/null | tr -dc '0-9')
if [ -z "$CLIENT_COUNT" ] || [ "$CLIENT_COUNT" -eq 0 ]; then
    echo "No personal access client found — creating one..."
    php artisan passport:client --personal --name="Mini-ERP Personal Access Client" --provider=users --no-interaction || true
else
    echo "Personal access client already exists (count: $CLIENT_COUNT) — skipping."
fi

echo "Starting Supervisor..."
exec "$@"
