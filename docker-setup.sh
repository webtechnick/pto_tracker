#!/bin/bash

# Docker setup script for PTO Tracker

echo "Setting up PTO Tracker with Docker..."

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cat > .env << EOF
APP_NAME=PTO_Tracker
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_LOG_LEVEL=debug
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite

BROADCAST_DRIVER=log
CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_DRIVER=sync

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
EOF
    echo ".env file created!"
else
    echo ".env file already exists, skipping..."
fi

# Build and start containers
echo "Building and starting Docker containers..."
docker-compose up -d --build

# Create SQLite database file if it doesn't exist
echo "Setting up SQLite database..."
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Generate application key
echo "Generating application key..."
docker-compose exec app php artisan key:generate

# Run migrations
echo "Running database migrations..."
docker-compose exec app php artisan migrate

# Run seeders (optional)
read -p "Do you want to run database seeders? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "Running database seeders..."
    docker-compose exec app php artisan db:seed
fi

echo "Setup complete! Your application should be available at http://localhost:8000" 