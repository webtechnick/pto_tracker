# AI Agent Context

This file provides context for AI coding assistants working on this project.

## Project Overview

PTO Tracker is a Laravel application for managing employee Paid Time Off requests. It's an internal tool used by a small team.

## Tech Stack

| Component | Version | Notes |
|-----------|---------|-------|
| PHP | 7.2 | **Production constraint** - cannot upgrade yet |
| Laravel | 6.x | LTS version compatible with PHP 7.2 |
| Database | SQLite | Simple file-based DB |
| Frontend | Vue 2 + Bootstrap 3 | Legacy stack |
| Asset Build | Laravel Mix 6 | Webpack-based |

## Development Commands

All Docker operations use the `./pto` CLI script:

```bash
./pto up          # Start containers
./pto down        # Stop containers
./pto rebuild     # Rebuild from scratch
./pto ssh         # Shell into app container
./pto artisan     # Run artisan commands
./pto composer    # Run composer commands
./pto npm         # Run npm commands (install, update, audit, etc.)
./pto fresh       # Reset database with seeds
./pto test        # Run PHPUnit tests
./pto build       # Compile frontend assets
```

> **AI Agent Note:** When running `./pto` commands, always use `required_permissions: ["all"]` to ensure Docker networking and container access work correctly.

## Architecture

### Directory Structure

- `app/` - Laravel application code
  - `Http/Controllers/` - Request handlers
  - `Traits/` - Reusable model traits (Filterable, Taggable, etc.)
  - `Events/` & `Listeners/` - Event-driven features
  - `Mail/` - Email templates
- `database/seeds/` - Test data seeders
- `resources/views/` - Blade templates
- `resources/assets/` - Vue components and SCSS

### Key Models

- `User` - Authentication, has role (admin/user)
- `Employee` - Staff members who take PTO
- `PaidTimeOff` - PTO requests with dates
- `Holiday` - Company holidays
- `Tag` - Categorization system

### Authentication

- Google SSO for production
- Standard Laravel auth for local dev
- Admin role grants full access

## Known Constraints

1. **PHP 7.2** - Production server runs 7.2.34, cannot upgrade without DevOps
2. **Laravel 6.x** - LTS version, last to support PHP 7.2
3. **Debian Buster** - Docker base image uses archived repos (EOL)
4. **All Laravel 5.x/6.x/7.x are EOL** - Security patches not available

## Testing

```bash
./pto test                        # Run all tests
./pto test --filter MethodName    # Run specific test
./pto test tests/Unit             # Run directory
```

Test database uses SQLite in-memory.

## Common Tasks

### Add a new Artisan command
```bash
./pto artisan make:command CommandName
```

### Create migration
```bash
./pto artisan make:migration create_table_name
./pto artisan migrate
```

### Clear caches
```bash
./pto artisan config:clear
./pto artisan cache:clear
./pto artisan view:clear
```

### Debug
- Laravel Debugbar is installed (appears in browser when APP_DEBUG=true)
- Logs are in `storage/logs/laravel.log`

## Code Style

- PSR-12 for PHP
- Use Eloquent ORM, avoid raw SQL
- Keep controllers thin, use traits for shared logic
- Blade templates with minimal PHP logic

## Cursor Cloud specific instructions

### Docker prerequisite

The development environment runs entirely in Docker. Docker must be installed and running before any `./pto` commands work. On Cloud Agent VMs, Docker requires `fuse-overlayfs` storage driver and `iptables-legacy` (see the Dockerfile-in-Docker setup in the system instructions).

Start the Docker daemon in a tmux session before proceeding:

```bash
# In a tmux session:
dockerd &
```

### First-time setup sequence

```bash
./pto up                       # Build images & start containers
./pto composer install         # Install PHP dependencies (dev included)
./pto artisan key:generate     # Generate APP_KEY (only if .env APP_KEY is empty)
./pto fresh                    # Migrate & seed database
./pto build                    # Compile frontend assets (Vue 2 + SCSS)
```

### `.env` configuration gotchas

- `DB_DATABASE` must be an **absolute path** inside the container: `/var/www/database/database.sqlite` (not the relative `database/database.sqlite` from `docker-setup.sh`).
- `SESSION_DOMAIN` must be `"localhost"` (without port). Setting it to `"localhost:8000"` breaks cookie/session handling and causes 419 CSRF errors.
- The project was missing `config/logging.php` (required by Laravel 6's LogManager). A standard single-channel config has been added; without it the app returns 500 on every request.

### Running services

| Service | Container | Port | Notes |
|---------|-----------|------|-------|
| PHP-FPM (app) | `pto_tracker_app` | 9000 (internal) | Laravel app server |
| Nginx (webserver) | `pto_tracker_nginx` | 8000 → 80 | App URL: `http://localhost:8000` |
| Redis | `pto_tracker_redis` | 6379 | Optional (drivers default to file/sync) |
| Node | `pto_tracker_node` | — | Asset watcher, auto-starts with `./pto up` |

### Storage permissions

After `./pto up`, fix permissions if you see "Permission denied" errors for `storage/logs`:

```bash
docker exec pto_tracker_app chmod -R 777 /var/www/storage /var/www/bootstrap/cache
```

### Test credentials (seeded)

- Admin: `nick.baker@continued.com` / `secret`
- Regular users are also seeded; check `database/seeds/UserSeeder.php`.

### Known test failure

`Tests\Feature\WorkingWithEmployeesTest::employee_can_remove_their_own_future_pto` fails (pre-existing, not environment-related). 91/92 tests pass.
