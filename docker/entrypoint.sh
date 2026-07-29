#!/bin/bash
set -euo pipefail

cd /var/www/html

log() { echo "[entrypoint] $*"; }

# ---------------------------------------------------------------------------
# Writable directories (named volumes mount in empty and owned by root)
# ---------------------------------------------------------------------------
mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R app:app storage bootstrap/cache
chmod -R ug+rw storage bootstrap/cache

# ---------------------------------------------------------------------------
# APP_KEY - generate one only if none was provided
# ---------------------------------------------------------------------------
if [ -z "${APP_KEY:-}" ]; then
    log "APP_KEY kosong - generate key sementara (set APP_KEY di .env agar session/enkripsi stabil)"
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
fi

# ---------------------------------------------------------------------------
# Wait for the database (external mysql8 container)
# ---------------------------------------------------------------------------
if [ "${WAIT_FOR_DB:-true}" = "true" ] && [ "${DB_CONNECTION:-mysql}" = "mysql" ]; then
    DB_WAIT_TIMEOUT="${DB_WAIT_TIMEOUT:-60}"
    log "Menunggu database ${DB_HOST:-mysql8}:${DB_PORT:-3306} (timeout ${DB_WAIT_TIMEOUT}s)"
    waited=0
    until mysqladmin ping -h"${DB_HOST:-mysql8}" -P"${DB_PORT:-3306}" --silent >/dev/null 2>&1; do
        if [ "$waited" -ge "$DB_WAIT_TIMEOUT" ]; then
            log "WARNING: database belum merespons setelah ${DB_WAIT_TIMEOUT}s - lanjut tetap start"
            break
        fi
        sleep 2
        waited=$((waited + 2))
    done
fi

# ---------------------------------------------------------------------------
# Storage symlink (public/storage -> storage/app/public)
# ---------------------------------------------------------------------------
if [ ! -e public/storage ]; then
    php artisan storage:link --quiet || log "storage:link dilewati"
fi

# ---------------------------------------------------------------------------
# Migrations - opt-in via RUN_MIGRATIONS=true
# ---------------------------------------------------------------------------
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    log "Menjalankan migration"
    php artisan migrate --force
fi

# ---------------------------------------------------------------------------
# Framework caches
# ---------------------------------------------------------------------------
php artisan config:clear --quiet || true
if [ "${APP_ENV:-production}" != "local" ]; then
    log "Membangun cache config/route/view"
    php artisan config:cache --quiet || log "config:cache gagal - lanjut tanpa cache"
    php artisan route:cache  --quiet || log "route:cache gagal - lanjut tanpa cache"
    php artisan view:cache   --quiet || log "view:cache gagal - lanjut tanpa cache"
fi

chown -R app:app storage bootstrap/cache

log "Siap. Menjalankan: $*"
exec "$@"
