#!/usr/bin/env bash
# Cursor Cloud install/update for PTO Tracker — idempotent, runs during each
# environment Build. Brings up the Dockerized dev stack via the ./pto CLI and
# prepares the database and frontend assets so future agents can run tests and
# browse the app immediately.
set -euo pipefail
cd "$(dirname "${BASH_SOURCE[0]}")/.."

# shellcheck source=cloud-ensure-docker.sh
source "$(dirname "${BASH_SOURCE[0]}")/cloud-ensure-docker.sh"

log() { echo ">>> $*"; }
ok() { echo "    $*"; }

# Absolute SQLite path *inside the container* (the repo is mounted at /var/www).
# Using the relative path that docker-setup.sh writes breaks migrations.
DB_PATH_IN_CONTAINER="/var/www/database/database.sqlite"

# Create .env with the Docker/SQLite-friendly defaults, or repair the two
# known gotchas (DB_DATABASE path and SESSION_DOMAIN) if it already exists.
ensure_env() {
    if [[ ! -f .env ]]; then
        log "Creating .env for Dockerized SQLite dev stack..."
        cat > .env <<EOF
APP_NAME=PTO_Tracker
APP_ENV=local
APP_KEY=
APP_DEBUG=true
LOG_LEVEL=debug
APP_URL=http://localhost:8000

APP_DEPARTMENT="IT"

ALWAYS_ON_CALL_NAME="Some Name"
ALWAYS_ON_CALL_NUMBER="(123) 456-7890"

TAG_MODEL=App\\Tag

SESSION_DOMAIN="localhost"

DB_CONNECTION=sqlite
DB_DATABASE=${DB_PATH_IN_CONTAINER}

BROADCAST_CONNECTION=log
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
EOF
        ok ".env created"
        return
    fi

    log "Repairing known .env gotchas..."

    # DB_DATABASE must be the absolute in-container path.
    if grep -qE '^DB_DATABASE=' .env; then
        sed -i "s#^DB_DATABASE=.*#DB_DATABASE=${DB_PATH_IN_CONTAINER}#" .env
    else
        printf 'DB_DATABASE=%s\n' "${DB_PATH_IN_CONTAINER}" >> .env
    fi

    # DB_CONNECTION must be sqlite for this stack.
    if grep -qE '^DB_CONNECTION=' .env; then
        sed -i 's#^DB_CONNECTION=.*#DB_CONNECTION=sqlite#' .env
    else
        printf 'DB_CONNECTION=sqlite\n' >> .env
    fi

    # SESSION_DOMAIN must be "localhost" WITHOUT a port, or cookies break (419).
    if grep -qE '^SESSION_DOMAIN=' .env; then
        sed -i 's#^SESSION_DOMAIN=.*#SESSION_DOMAIN="localhost"#' .env
    else
        printf 'SESSION_DOMAIN="localhost"\n' >> .env
    fi

    ok ".env checked"
}

ensure_sqlite_file() {
    mkdir -p database
    if [[ ! -f database/database.sqlite ]]; then
        touch database/database.sqlite
        log "Created database/database.sqlite"
    fi
    chmod 664 database/database.sqlite 2>/dev/null || true
}

# Bring the compose stack up; rebuild from scratch if a plain up fails.
ensure_stack_up() {
    log "Starting containers (./pto up)..."
    if ! ./pto up; then
        log "./pto up failed; rebuilding from scratch (./pto rebuild)..."
        ./pto rebuild
    fi

    # Wait for the app (php-fpm) container to be running.
    for _ in $(seq 1 60); do
        if docker_cmd inspect -f '{{.State.Running}}' pto_tracker_app 2>/dev/null | grep -q true; then
            ok "app container is running"
            break
        fi
        sleep 2
    done
}

# Snapshots built before the Laravel 13 upgrade carry a PHP 7.2 app image.
# ./pto up builds when the Dockerfile changes, but force a clean rebuild if the
# running container is still on an older PHP.
ensure_php_version() {
    if docker_cmd exec pto_tracker_app php -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);' 2>/dev/null; then
        ok "app container runs PHP $(docker_cmd exec pto_tracker_app php -r 'echo PHP_VERSION;')"
        return
    fi

    log "app container is not on PHP 8.4; rebuilding image (./pto rebuild)..."
    ./pto rebuild
    ensure_stack_up
}

fix_storage_permissions() {
    docker_cmd exec pto_tracker_app chmod -R 777 /var/www/storage /var/www/bootstrap/cache 2>/dev/null || true
    # php-fpm runs as www-data; SQLite needs to write the file and create its
    # journal next to it in the bind-mounted (host-owned) database/ directory.
    docker_cmd exec pto_tracker_app sh -c 'chmod 777 /var/www/database && chmod 666 /var/www/database/database.sqlite' 2>/dev/null || true
}

main() {
    ensure_docker
    ensure_env
    ensure_sqlite_file
    ensure_stack_up
    ensure_php_version
    fix_storage_permissions

    log "Installing PHP dependencies (./pto composer install)..."
    ./pto composer install --no-interaction
    # Marker cloud-start.sh uses to detect a vendor/ that is out of date.
    docker_cmd exec pto_tracker_app cp composer.lock vendor/composer/installed.lock

    # Generate APP_KEY only when it is empty.
    if grep -qE '^APP_KEY=\s*$' .env; then
        log "Generating APP_KEY (./pto artisan key:generate)..."
        ./pto artisan key:generate --no-interaction
    else
        ok "APP_KEY already set"
    fi

    fix_storage_permissions

    log "Migrating & seeding database (./pto fresh)..."
    ./pto fresh

    # ./pto build runs a one-off `node` container (docker compose run node npm
    # run prod). It does not install node_modules first, so install them
    # explicitly or Laravel Mix ("mix") won't be found.
    log "Installing frontend dependencies (./pto npm install)..."
    ./pto npm install

    log "Building frontend assets (./pto build)..."
    ./pto build

    ok "Cloud install complete. App available at http://localhost:8000"
}

main "$@"
