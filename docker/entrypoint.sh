#!/bin/sh
set -e

cd /var/www/html

# Generate APP_KEY only if one was not supplied via env.
if [ -z "$APP_KEY" ]; then
    echo "[entrypoint] No APP_KEY set — generating one (set APP_KEY in Bunny env to keep it stable)."
    php artisan key:generate --force
fi

# Wait for the database sidecar to accept connections.
echo "[entrypoint] Waiting for database at ${DB_HOST:-mariadb}:${DB_PORT:-3306}..."
TRIES=0
until php -r '
    $h=getenv("DB_HOST")?:"mariadb"; $p=getenv("DB_PORT")?:"3306";
    $u=getenv("DB_USERNAME")?:"root"; $pw=getenv("DB_PASSWORD")?:"";
    try { new PDO("mysql:host=$h;port=$p", $u, $pw); exit(0); } catch (Throwable $e) { exit(1); }
' 2>/dev/null; do
    TRIES=$((TRIES + 1))
    if [ "$TRIES" -ge 60 ]; then
        echo "[entrypoint] Database not reachable after 2 minutes — aborting."
        exit 1
    fi
    sleep 2
done
echo "[entrypoint] Database is up."

# Schema + storage symlink.
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

# Production caches.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure runtime dirs are writable (persistent volume may mount empty).
chown -R www-data:www-data storage bootstrap/cache

echo "[entrypoint] Starting php-fpm, nginx and the scheduler."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
