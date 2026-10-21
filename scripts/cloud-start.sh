#!/usr/bin/env bash
# Cursor Cloud start — runs when an agent machine boots so the Desktop tab can
# reach the app at http://localhost:8000. Ensures Docker is up, repairs the two
# known .env gotchas if an .env is present, and brings the compose stack up.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

# shellcheck source=cloud-ensure-docker.sh
source "$(dirname "${BASH_SOURCE[0]}")/cloud-ensure-docker.sh"

log() { echo ">>> $*"; }
ok() { echo "    $*"; }

DB_PATH_IN_CONTAINER="/var/www/database/database.sqlite"

ensure_docker

# If no install has run yet there is no .env; hand off to the full installer so
# the Desktop tab still comes up with a working app.
if [[ ! -f .env ]]; then
    log "No .env found; running full install (scripts/cloud-install.sh)..."
    exec bash "$(dirname "${BASH_SOURCE[0]}")/cloud-install.sh"
fi

# Repair the known gotchas in case a stale/incorrect .env was restored.
log "Verifying .env gotchas..."
sed -i "s#^DB_DATABASE=.*#DB_DATABASE=${DB_PATH_IN_CONTAINER}#" .env
grep -qE '^DB_DATABASE=' .env || printf 'DB_DATABASE=%s\n' "${DB_PATH_IN_CONTAINER}" >> .env
grep -qE '^SESSION_DOMAIN=' .env \
    && sed -i 's#^SESSION_DOMAIN=.*#SESSION_DOMAIN="localhost"#' .env \
    || printf 'SESSION_DOMAIN="localhost"\n' >> .env

# Make sure the SQLite file exists before the app starts.
mkdir -p database
[[ -f database/database.sqlite ]] || touch database/database.sqlite

log "Starting containers (./pto up)..."
./pto up

# Wait for the app container and fix storage perms so requests don't 500.
for _ in $(seq 1 60); do
    if docker_cmd inspect -f '{{.State.Running}}' pto_tracker_app 2>/dev/null | grep -q true; then
        break
    fi
    sleep 2
done
docker_cmd exec pto_tracker_app chmod -R 777 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
# php-fpm runs as www-data; SQLite needs a writable file and directory.
docker_cmd exec pto_tracker_app sh -c 'chmod 777 /var/www/database && chmod 666 /var/www/database/database.sqlite' 2>/dev/null || true

# A snapshot built before the Laravel 13 / PHP 8.4 upgrade still has the PHP
# 7.2 image and a Laravel 6 vendor/. Rebuild and resync so the app boots.
if ! docker_cmd exec pto_tracker_app php -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' 2>/dev/null; then
    log "app container is not on PHP 8.4; rebuilding image (./pto rebuild)..."
    ./pto rebuild
    docker_cmd exec pto_tracker_app chmod -R 777 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
fi

if ! cmp -s composer.lock vendor/composer/installed.lock 2>/dev/null; then
    log "vendor/ does not match composer.lock; running ./pto composer install..."
    ./pto composer install --no-interaction
    docker_cmd exec pto_tracker_app cp composer.lock vendor/composer/installed.lock
fi

ok "App should be available at http://localhost:8000"
