# PTO Tracker - Docker Setup

This document explains how to run the PTO Tracker application using Docker.

## Prerequisites

- Docker
- Docker Compose

## Quick Start

1. **Clone the repository and navigate to the project directory:**
   ```bash
   cd pto_tracker
   ```

2. **Run the setup script:**
   ```bash
   ./docker-setup.sh
   ```

   This script will:
   - Create a `.env` file with Docker-specific configuration
   - Build and start all Docker containers
   - Generate the Laravel application key
   - Run database migrations
   - Optionally run database seeders

3. **Access the application:**
   - Web application: http://localhost:8000
   - Database: localhost:3306
   - Redis: localhost:6379

## Manual Setup

If you prefer to set up manually:

1. **Create environment file:**
   ```bash
   cp .env.example .env
   ```

2. **Update the `.env` file with Docker settings:**
   ```env
   DB_HOST=db
   DB_PORT=3306
   DB_DATABASE=pto_tracker
   DB_USERNAME=pto_user
   DB_PASSWORD=root
   REDIS_HOST=redis
   ```

3. **Build and start containers:**
   ```bash
   docker-compose up -d --build
   ```

4. **Install dependencies and setup Laravel:**
   ```bash
   # Install PHP dependencies
   docker-compose exec app composer install

   # Install Node.js dependencies
   docker-compose exec app npm install

   # Build assets
   docker-compose exec app npm run prod

   # Generate application key
   docker-compose exec app php artisan key:generate

   # Run migrations
   docker-compose exec app php artisan migrate

   # Run seeders (optional)
   docker-compose exec app php artisan db:seed
   ```

## Docker Services

The application consists of the following services:

- **app**: PHP 7.2-FPM with Laravel application
- **webserver**: Nginx web server
- **db**: MySQL 5.7 database
- **redis**: Redis cache server

## Useful Commands

### Container Management
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# View logs
docker-compose logs -f

# View logs for specific service
docker-compose logs -f app
```

### Laravel Commands
```bash
# Run artisan commands
docker-compose exec app php artisan [command]

# Examples:
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker
docker-compose exec app php artisan route:list
```

### Database
```bash
# Access MySQL
docker-compose exec db mysql -u pto_user -p pto_tracker

# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback
```

### Asset Compilation
```bash
# Install Node.js dependencies
docker-compose exec app npm install

# Build assets for production
docker-compose exec app npm run prod

# Watch for changes (development)
docker-compose exec app npm run dev
```

## Configuration Files

- `docker-compose.yml`: Main Docker Compose configuration
- `Dockerfile`: PHP application container definition
- `docker/nginx/conf.d/app.conf`: Nginx configuration
- `docker/php/local.ini`: PHP configuration
- `docker/mysql/my.cnf`: MySQL configuration

## Troubleshooting

### Common Issues

1. **Permission errors:**
   ```bash
   sudo chown -R $USER:$USER storage bootstrap/cache
   ```

2. **Database connection issues:**
   - Ensure the database container is running: `docker-compose ps`
   - Check database logs: `docker-compose logs db`

3. **Port conflicts:**
   - Change ports in `docker-compose.yml` if 8000, 3306, or 6379 are in use

4. **Container won't start:**
   - Check logs: `docker-compose logs [service-name]`
   - Rebuild containers: `docker-compose up -d --build`

### Reset Everything
```bash
# Stop and remove all containers, networks, and volumes
docker-compose down -v

# Remove all images
docker-compose down --rmi all

# Start fresh
./docker-setup.sh
```

## Development Workflow

1. **Making code changes:**
   - Edit files in your local directory
   - Changes are automatically reflected in the container due to volume mounting

2. **Adding new dependencies:**
   ```bash
   # PHP dependencies
   docker-compose exec app composer require [package]

   # Node.js dependencies
   docker-compose exec app npm install [package]
   ```

3. **Running tests:**
   ```bash
   docker-compose exec app php artisan test
   ```

## Production Considerations

For production deployment:

1. Update `.env` file with production settings
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Configure proper database credentials
4. Set up SSL certificates
5. Configure proper logging
6. Use Docker secrets for sensitive data

## Support

If you encounter issues, check the logs and ensure all prerequisites are met. The application should be accessible at http://localhost:8000 once properly configured. 