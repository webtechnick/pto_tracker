# PTO Tracker

Help Keep Track of Paid Time Off (PTO)

## Local Development Setup (Docker)

### Quick Start

```bash
./pto up                 # Build the PHP 8.4 image (first run) and start the stack
./pto composer install   # Install PHP dependencies into vendor/
./pto artisan key:generate   # Only if APP_KEY in .env is empty
./pto fresh              # Migrate and seed the database
```

Built front-end assets are committed in `public/`; run `./pto build` only after
changing anything in `resources/assets`.

Then visit http://localhost:8000

### Available Commands

| Command | Description |
|---------|-------------|
| `./pto up` | Start all containers (rebuilds the app image if the Dockerfile changed) |
| `./pto down` | Stop and remove all containers |
| `./pto restart` | Restart all containers |
| `./pto start` | Start stopped containers |
| `./pto stop` | Stop running containers |
| `./pto ssh` | SSH into the app container |
| `./pto tinker` | Run Laravel Tinker |
| `./pto artisan <cmd>` | Run any Artisan command |
| `./pto composer <cmd>` | Run any Composer command |
| `./pto npm <cmd>` | Run any npm command in the node container |
| `./pto fresh` | Run migrate:fresh --seed |
| `./pto test` | Run PHPUnit tests |
| `./pto build` | Build front-end assets |
| `./pto rebuild` | Rebuild Docker containers |

### Examples

```bash
./pto artisan make:model Post       # Create a new model
./pto artisan migrate               # Run migrations
./pto composer require package/name # Install a package
./pto test --filter testMethod      # Run specific test
```

## Environment

- **PHP**: 8.4 (`php:8.4-fpm-trixie` image, currently 8.4.27)
- **Laravel**: 13.x
- **PHPUnit**: 12
- **Database**: SQLite
- **Web Server**: Nginx
- **Node**: 18 (asset compilation only; not needed on servers)

## Server Requirements

- PHP **8.4.27+** (or 8.5.12+), the first releases with the fix for
  CVE-2026-109618 / CVE-2026-109619. `prod-update.sh` refuses to run on
  PHP < 8.4 and warns below those patch levels.
- PHP extensions: `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `mbstring`,
  `openssl`, `pdo`, `pdo_sqlite` (or `pdo_mysql`), `session`, `tokenizer`,
  `xml`. On Debian/Ubuntu packages: `php8.4-fpm php8.4-cli php8.4-sqlite3
  php8.4-mbstring php8.4-xml php8.4-curl` (plus `php8.4-mysql` for MySQL).
- Composer 2.x as `composer.phar` in the project root.
- nginx with PHP-FPM, document root `public/`.
- Node is **not** required on servers: built assets are committed.

### First deploy on a new server

```bash
git clone <repo> /var/www/laravel/pto_tracker && cd /var/www/laravel/pto_tracker
cp .env.example .env            # then fill in the instance settings
php composer.phar install --no-dev --optimize-autoloader
php artisan key:generate        # only for a brand new instance
./prod-update.sh                # migrate, cache config/routes/views, fix perms
```

After that, `./pto deploy` (Teleport) runs `prod-update.sh` remotely. It first
checks that the server is on PHP 8.4+.

Existing instance `.env` files keep working: the Laravel 6 variable names
(`MAIL_DRIVER`, `MAIL_ENCRYPTION`, `QUEUE_DRIVER`, `CACHE_DRIVER`,
`BROADCAST_DRIVER`, `APP_LOG_LEVEL`) are still read as fallbacks for
`MAIL_MAILER`, `MAIL_SCHEME`, `QUEUE_CONNECTION`, `CACHE_STORE`,
`BROADCAST_CONNECTION` and `LOG_LEVEL`.

## Production Setup

### Google SSO Configuration

You'll need a Google SSO app. Get your client ID here:
https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid

Update `.env` with your credentials:

```env
GOOGLE_CLIENT_ID=your-client-id
GOOGLE_SECRET=your-secret
GOOGLE_REDIRECT=http://localhost:8000/login/google/callback
GOOGLE_DOMAIN=yourdomain.com
```

Authorize the redirect URL in Google Console:
https://console.developers.google.com/apis

### Creating First Admin

1. Register a user at http://localhost:8000/register
2. SSH into the container: `./pto ssh`
3. Run tinker: `php artisan tinker`
4. Promote to admin: `App\User::first()->update(['role' => 'admin']);`
5. Log out and back in

---

## Legacy Setup Methods

<details>
<summary>Vagrant (deprecated)</summary>

Requires composer, vagrant, and virtualbox installed.

```bash
composer install
vagrant up
vagrant ssh
cd code
touch database/database.sqlite
php artisan migrate
```
</details>

<details>
<summary>Local PHP (deprecated)</summary>

Requires PHP 8.4, composer and npm installed locally.

```bash
composer install
npm install
touch database/database.sqlite
php artisan migrate
gulp
php artisan serve
```
</details>
