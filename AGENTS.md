# AI Agent Context

This file provides context for AI coding assistants working on this project.

## Project Overview

PTO Tracker is a Laravel application for managing employee Paid Time Off requests. It's an internal tool used by a small team.

## Tech Stack

| Component | Version | Notes |
|-----------|---------|-------|
| PHP | 8.4 | `php:8.4-fpm-trixie` image; servers need 8.4.27+ (PHP2Shell fix) |
| Laravel | 13.x | Laravel 11+ application structure (`bootstrap/app.php`) |
| PHPUnit | 12 | Tests use `#[Test]` attributes (docblock `@test` is not supported) |
| Database | SQLite | Simple file-based DB |
| Frontend | Vue 2 + Bootstrap 3 | Legacy stack |
| Asset Build | Laravel Mix 6 | Webpack-based |

## Development Commands

All Docker operations use the `./pto` CLI script:

```bash
./pto up          # Start containers (rebuilds the app image if the Dockerfile changed)
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

- `bootstrap/app.php` - Routing, middleware aliases, exception config (no HTTP/console kernels)
- `bootstrap/providers.php` - Service providers (`AppServiceProvider` maps event listeners and policies)
- `app/` - Laravel application code
  - `Http/Controllers/` - Request handlers
  - `Traits/` - Reusable model traits (Filterable, Taggable, etc.)
  - `Events/` & `Listeners/` - Event-driven features
  - `Mail/` - Email templates
- `database/factories/` - Class-based model factories (`Model::factory()`)
- `database/seeders/` - Test data seeders (`Database\Seeders` namespace)
- `resources/views/` - Blade templates (forms use `spatie/laravel-html`: `html()->text(...)`)
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

1. **PHP 8.4.27+ on servers** - `composer.json` requires `^8.4` (platform pinned to 8.4.27); `prod-update.sh` and `./pto deploy` refuse PHP < 8.4.
2. **Event discovery is off** - listeners are mapped in `AppServiceProvider::$listen`. Turning discovery on registers them twice (duplicate PTO emails); `UpgradeRegressionTest` guards this.
3. **JSON dates use `Y-m-d H:i:s`** - models use `App\Traits\SerializesDatesAsDateTimeStrings`; the Vue calendar (moment.js) and the API rely on it. Use it on new models.
4. **`TrimStrings` / `ConvertEmptyStringsToNull` are disabled** - forms post empty strings into NOT NULL columns.
5. **Legacy `.env` names** - `MAIL_DRIVER`, `MAIL_ENCRYPTION`, `QUEUE_DRIVER`, `CACHE_DRIVER`, `BROADCAST_DRIVER`, `APP_LOG_LEVEL` are fallbacks in `config/*.php`; deployed instances still use them.
6. **Frontend is legacy** - Vue 2 + Bootstrap 3 + Laravel Mix 6 with committed builds in `public/`.
7. **Forms go through `App\Support\Html`** - it replaces `Spatie\Html\Html` in the container and escapes input values, textarea contents and select labels (spatie writes textarea contents raw). Keep using `html()->...`; never `->html($userValue)` / `->value()` on a textarea directly.
8. **No CORS** - `config/cors.php` has no paths; the token API is server-to-server.

## Deploying

`./pto deploy` (Teleport) checks the session and the server's PHP (8.4+ required), then on the server enters maintenance mode (best effort), runs `git pull origin master` and the pulled `prod-update.sh`. Manual equivalent on the server: `git pull origin master && ./prod-update.sh`.

### First deploy onto a server still running the Laravel 6 release

1. Upgrade PHP on the server to 8.4.27+ (php-fpm, cli and the extensions in `readme.md`) and point php-fpm/nginx at it.
2. From an up-to-date checkout of this release run `./pto deploy`. Do **not** run the server's existing `./prod-update.sh` first: that Laravel 6 copy dies at `artisan down` on PHP 8.4. If someone already did, recover with `git pull origin master && ./prod-update.sh` on the server.
3. The new `prod-update.sh` tolerates the leftover Laravel 6 `vendor/` and caches: it deletes the old framework caches, runs `composer install`, only then enters maintenance mode for certain, migrates and rebuilds caches.

`scripts/rehearse-first-deploy.sh` rehearses these paths in Docker against a server built from the pre-upgrade release (needs the app image and `php:7.2-cli`); run it after changing `prod-update.sh` or `./pto deploy`.

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
./pto artisan make:command CommandName   # auto-discovered from app/Console/Commands
```
Schedule it in `routes/console.php` with `Schedule::command(...)`.

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
- Laravel Debugbar is a dev dependency (appears in browser when APP_DEBUG=true; absent with `composer install --no-dev`)
- Logs are in `storage/logs/laravel.log`

## Code Style

- PSR-12 for PHP
- Use Eloquent ORM, avoid raw SQL
- Keep controllers thin, use traits for shared logic
- Blade templates with minimal PHP logic

## Cursor Cloud specific instructions

### Automated environment scripts

Install and boot for Cloud Agents are automated and are the source of truth for setup. `.cursor/environment.json` wires them up:

- `scripts/cloud-ensure-docker.sh` — shared bootstrap that installs/starts Docker with the nested-pod settings (`fuse-overlayfs` storage driver + `iptables-legacy`).
- `scripts/cloud-install.sh` — `install` step: ensures Docker, writes/repairs `.env`, creates the SQLite file, then `./pto up`, checks the app container is on PHP 8.4 (rebuilds otherwise), `./pto composer install`, `./pto artisan key:generate` (only if `APP_KEY` empty), `./pto fresh`, `./pto build`.
- `scripts/cloud-start.sh` — `start` step: ensures Docker, repairs the `.env` gotchas, and `./pto up` so `http://localhost:8000` is reachable from the Desktop tab. If the snapshot predates the PHP 8.4 upgrade it rebuilds the image, and it reruns `./pto composer install` whenever `vendor/` was installed from a different `composer.lock` (marker: `vendor/composer/installed.lock`).

The manual steps below document what these scripts do; prefer running the scripts.

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
- Laravel 11+ reads `CACHE_STORE`, `QUEUE_CONNECTION`, `MAIL_MAILER`, `BROADCAST_CONNECTION`, `LOG_LEVEL`; the old `*_DRIVER` / `APP_LOG_LEVEL` names still work as fallbacks.

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
- Regular users are also seeded; check `database/seeders/UserSeeder.php`.
- `APP_ENV=local` skips the Google SSO middleware, so the calendar opens without a Google login.

### Test mail driver

`phpunit.xml` forces `MAIL_MAILER=array` (which wins over a legacy `MAIL_DRIVER`), so tests never send real mail regardless of `.env`. Use `Mail::fake()` in tests that need to assert on sent mail. All 148 tests are expected to pass.

### vendor/ ownership

Composer runs inside the container as root, so `vendor/` is root-owned on the host. Use `./pto composer ...` (or `sudo`) rather than editing it from the host.
