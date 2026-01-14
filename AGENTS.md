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
./pto fresh       # Reset database with seeds
./pto test        # Run PHPUnit tests
./pto build       # Compile frontend assets
```

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
