#!/bin/bash
#
# Production Deployment Script for PTO Tracker
# Run this from within the production server at /var/www/laravel/pto_tracker
#
# Usage: ./prod-update.sh
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

# Put application in maintenance mode
info "Putting application in maintenance mode..."
php artisan down --message="Updating application. Back shortly!" --retry=60

# Pull latest code
info "Pulling latest code from git..."
git pull origin master

# Install composer dependencies
info "Installing composer dependencies..."
php composer.phar install --no-dev --optimize-autoloader --no-interaction

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

# Bring application back online
info "Bringing application back online..."
php artisan up

echo ""
echo "========================================="
echo -e "   ${GREEN}Deployment Complete!${NC}"
echo "========================================="
echo ""
