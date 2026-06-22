#!/bin/sh
set -e

cd /var/www/html

# Ensure a .env file exists. It's gitignored so the production image ships
# without one; on Bunny we rely on injected environment variables for real
# config, but several artisan commands (notably key:generate, config:cache)
# refuse to run without a writable .env present. Bunny's env vars override
# anything in this file, so the contents only act as a placeholder.
if [ ! -f .env ]; then
    echo "[entrypoint] No .env present — bootstrapping a minimal one from .env.example."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        touch .env
    fi
    chown www-data:www-data .env
fi

# Generate APP_KEY only if one was not supplied via env. key:generate edits
# .env in place and refuses to run if it can't find an APP_KEY= line, so we
# add a blank one defensively in case the .env.example we copied didn't
# include it.
if [ -z "$APP_KEY" ]; then
    if ! grep -q '^APP_KEY=' .env; then
        echo '' >> .env
        echo 'APP_KEY=' >> .env
    fi
    echo "[entrypoint] No APP_KEY set — generating one (set APP_KEY in Bunny env to keep it stable across redeploys)."
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
echo "[entrypoint] Migration files:"
find database/migrations -name '*.php' | sort
echo "[entrypoint] Running migrations..."
php artisan migrate --force
echo "[entrypoint] Done migrating."
php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder --force
php artisan storage:link 2>/dev/null || true

# Production caches.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure runtime dirs are writable (persistent volume may mount empty).
chown -R www-data:www-data storage bootstrap/cache

echo "[entrypoint] Starting php-fpm, nginx and the scheduler."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
