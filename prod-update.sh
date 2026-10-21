#!/bin/bash
#
# Production Deployment Script for PTO Tracker
# Run this from within the production server at /var/www/laravel/pto_tracker
#
# Deploying:
#   ./pto deploy              (runs this script remotely via Teleport SSH)
#
# Manual deployment:
#   1. Authenticate with Teleport (if session expired):
#        tsh login
#   2. SSH into the production host:
#        tsh ssh continued_lead@lcg-east2-pto.lacallegroup.com
#   3. Navigate to the project, pull, and run the (now current) script:
#        cd /var/www/laravel/pto_tracker
#        git pull origin master && ./prod-update.sh
#
# Works from any previous release, including a server still holding the
# Laravel 6 code/vendor: nothing that must succeed runs through artisan until
# composer has installed the vendor/ that matches the pulled code.
#

set -e  # Exit on any error

# Absolute path, so re-running the updated script also works when started as
# `bash prod-update.sh` (a bare name would be looked up in PATH).
SCRIPT_PATH="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/$(basename "${BASH_SOURCE[0]}")"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Helper functions
info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Ensure we're in the right directory
if [ ! -f "artisan" ]; then
    error "artisan file not found. Please run this script from the Laravel project root."
    exit 1
fi

if [ ! -f "composer.phar" ]; then
    error "composer.phar not found. Please ensure composer.phar exists in the project root."
    exit 1
fi

echo ""
echo "========================================="
echo "   PTO Tracker Production Deployment"
echo "========================================="
echo ""

# Refuse to deploy onto a PHP that cannot run the app, before touching anything.
PHP_VERSION="$(php -r 'echo PHP_VERSION;')"
if ! php -r 'exit(PHP_VERSION_ID >= 80400 ? 0 : 1);'; then
    error "PHP 8.4+ is required (found ${PHP_VERSION}). Aborting before maintenance mode."
    exit 1
fi
# CVE-2026-109618 / CVE-2026-109619 ("PHP2Shell") are fixed in 8.4.27+ and 8.5.12+.
if ! php -r '$v = PHP_VERSION_ID; exit(($v >= 80427 && $v < 80500) || $v >= 80512 ? 0 : 1);'; then
    warn "PHP ${PHP_VERSION} predates the PHP2Shell fix (8.4.27+ / 8.5.12+). Patch PHP on this server."
fi

DOWN="${PTO_UPDATE_DOWN:-0}"

# The site as the web server serves it: APP_URL (or PTO_HEALTHCHECK_URL) with
# its host pinned to this machine, so nginx + php-fpm are exercised.
HEALTHCHECK_URL="${PTO_HEALTHCHECK_URL:-$(grep -m1 -E '^APP_URL=' .env 2>/dev/null | cut -d= -f2- | sed -E 's/[[:space:]]+#.*$//; s/^["'"'"']//; s/["'"'"']$//; s#/+$##')/login}"

site_status() {
    if ! command -v curl > /dev/null 2>&1; then
        echo "unknown (curl not installed)"
        return
    fi
    local scheme host port
    scheme="${HEALTHCHECK_URL%%://*}"
    host="${HEALTHCHECK_URL#*://}"; host="${host%%/*}"
    port="${host##*:}"; host="${host%%:*}"
    [ "$port" = "$host" ] && { [ "$scheme" = "https" ] && port=443 || port=80; }
    curl -sk -o /dev/null -w '%{http_code}' --max-time 15 --resolve "${host}:${port}:127.0.0.1" "$HEALTHCHECK_URL" 2>/dev/null || true
}

# Static 503 page for when artisan cannot run yet (Laravel 6 vendor/ on PHP
# 8.4, or new code on the old vendor/). public/index.php serves
# storage/framework/maintenance.php before loading anything else, and
# `artisan up` removes it together with the down marker.
static_maintenance() {
    mkdir -p storage/framework || return 1
    cat > storage/framework/maintenance.php <<'PHP' || return 1
<?php
// Written by prod-update.sh while dependencies are reinstalled; removed by `php artisan up`.
http_response_code(503);
header('Retry-After: 60');
header('Content-Type: text/html; charset=UTF-8');
echo '<!DOCTYPE html><html><head><title>Be right back.</title></head><body><h1>Be right back.</h1><p>PTO Tracker is being updated.</p></body></html>';
exit;
PHP
    echo '{"except":[],"redirect":null,"retry":60,"refresh":null,"secret":null,"status":503,"template":null}' > storage/framework/down
}

# Best effort until composer has installed the vendor/ matching the code.
enter_maintenance() {
    [ "$DOWN" = 1 ] && return 0
    if [ -f "vendor/autoload.php" ]; then
        info "Putting application in maintenance mode..."
        if php artisan down --render="errors::503" --retry=60; then
            DOWN=1
            return 0
        fi
        warn "Installed code/vendor cannot run artisan here."
    fi
    if static_maintenance; then
        warn "Serving a static maintenance page until dependencies are installed."
        DOWN=1
    else
        warn "Could not write a maintenance page; continuing, will retry after composer install."
    fi
}

on_error() {
    local status
    status="$(site_status)"
    error "Deployment failed (line $1)."
    if [ "$DOWN" = 1 ]; then
        error "The site is in maintenance mode: users get the maintenance page (HTTP ${status} from ${HEALTHCHECK_URL})."
        error "Fix the problem and rerun ./prod-update.sh; only run 'php artisan up' once the app works."
    elif [ "$status" = "200" ]; then
        error "The site still answers HTTP 200 at ${HEALTHCHECK_URL}, but this release is only partly deployed. Fix and rerun ./prod-update.sh."
    else
        error "THE SITE IS DOWN: ${HEALTHCHECK_URL} returns HTTP ${status}. Fix the problem and rerun ./prod-update.sh."
    fi
}
trap 'on_error $LINENO' ERR

enter_maintenance

# Pull latest code. If this script itself changed, run the new version so a
# release's deploy steps always come from that release.
info "Pulling latest code from git..."
SCRIPT_HASH_BEFORE="$(git hash-object "$SCRIPT_PATH")"
git pull origin master
if [ "$(git hash-object "$SCRIPT_PATH")" != "$SCRIPT_HASH_BEFORE" ] && [ -z "${PTO_UPDATE_REEXEC:-}" ]; then
    info "prod-update.sh changed; re-running the updated script ($SCRIPT_PATH)..."
    trap - ERR
    PTO_UPDATE_REEXEC=1 PTO_UPDATE_DOWN="$DOWN" exec bash "$SCRIPT_PATH" "$@"
fi

# Composer's package:discover boots the new code, which must not read the
# previous release's cached config (a Laravel 6 config cache lists providers
# that no longer exist). Caches are rebuilt below.
info "Removing the previous release's framework caches..."
rm -f bootstrap/cache/config.php bootstrap/cache/services.php bootstrap/cache/packages.php \
    bootstrap/cache/events.php bootstrap/cache/compiled.php bootstrap/cache/routes*.php

info "Installing composer dependencies..."
php composer.phar install --no-dev --optimize-autoloader --no-interaction

enter_maintenance
if [ "$DOWN" != 1 ]; then
    error "Could not enter maintenance mode with the freshly installed dependencies."
    exit 1
fi

# Run database migrations
info "Running database migrations..."
php artisan migrate --force

# Clear and rebuild caches
info "Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

info "Rebuilding caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Normalize storage permissions so the php-fpm user (www-data) can always
# create the nested cache buckets it needs at runtime. Artisan commands run
# here as the deploy user, which would otherwise leave deploy-user-owned dirs
# that www-data can't write into — the cause of intermittent 500s in the
# rate-limiter/file cache (e.g. the /api/* throttle middleware). The setgid
# bit makes new subdirectories inherit the www-data group going forward.
info "Normalizing storage permissions..."
sudo chgrp -R www-data storage bootstrap/cache
sudo chmod -R g+rwX storage bootstrap/cache
sudo find storage bootstrap/cache -type d -exec chmod g+s {} \;

# Bring application back online
info "Bringing application back online..."
php artisan up
DOWN=0

# The CLI check above says nothing about the PHP that nginx/php-fpm runs.
# Composer's platform check makes the app return 500 on PHP < 8.4, so a 200
# here means the web server runs a supported PHP and the release boots.
info "Checking ${HEALTHCHECK_URL} through the web server..."
if command -v pgrep > /dev/null 2>&1; then
    for fpm in $(pgrep -a -f 'php-fpm: master' 2>/dev/null | grep -oE 'php/[0-9]+\.[0-9]+' | cut -d/ -f2 | sort -u); do
        case "$fpm" in
            8.4|8.5|8.[6-9]|9.*) ;;
            *) warn "A php-fpm ${fpm} master is still running; make sure nginx does not use it." ;;
        esac
    done
fi
STATUS="$(site_status)"
if [ "$STATUS" != "200" ]; then
    error "Post-deploy check failed: ${HEALTHCHECK_URL} returned HTTP ${STATUS} (expected 200)."
    error "THE SITE IS NOT WORKING. Likely causes: nginx/php-fpm still on PHP < 8.4, or the app errors (see storage/logs/laravel.log)."
    error "Set PTO_HEALTHCHECK_URL if APP_URL does not reach this server's nginx."
    exit 1
fi
info "Web server answered HTTP 200."

echo ""
echo "========================================="
echo -e "   ${GREEN}Deployment Complete!${NC}"
echo "========================================="
echo ""
