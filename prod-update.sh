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

# Best effort: the installed vendor/ may not run on this PHP (e.g. Laravel 6
# on PHP 8.4) or may not match freshly pulled code. The 503 page is
# pre-rendered so it is served while vendor/ is being reinstalled.
enter_maintenance() {
    [ "$DOWN" = 1 ] && return 0
    if [ ! -f "vendor/autoload.php" ]; then
        warn "vendor/ not installed yet; maintenance mode after composer install."
        return 0
    fi
    info "Putting application in maintenance mode..."
    if php artisan down --render="errors::503" --retry=60; then
        DOWN=1
    else
        warn "Installed code/vendor cannot run artisan here; continuing, will retry after composer install."
    fi
}

on_error() {
    error "Deployment failed (line $1)."
    if [ "$DOWN" = 1 ]; then
        error "The application is still in maintenance mode. Fix the problem and rerun ./prod-update.sh (or run: php artisan up)."
    fi
}
trap 'on_error $LINENO' ERR

enter_maintenance

# Pull latest code. If this script itself changed, run the new version so a
# release's deploy steps always come from that release.
info "Pulling latest code from git..."
SCRIPT_HASH_BEFORE="$(git hash-object "$0")"
git pull origin master
if [ "$(git hash-object "$0")" != "$SCRIPT_HASH_BEFORE" ] && [ -z "${PTO_UPDATE_REEXEC:-}" ]; then
    info "prod-update.sh changed; re-running the updated script..."
    trap - ERR
    PTO_UPDATE_REEXEC=1 PTO_UPDATE_DOWN="$DOWN" exec "$0" "$@"
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

echo ""
echo "========================================="
echo -e "   ${GREEN}Deployment Complete!${NC}"
echo "========================================="
echo ""
