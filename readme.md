# PTO Tracker

Help Keep Track of Paid Time Off (PTO)

## Local Development Setup (Docker)

### Quick Start

```bash
./pto up        # Start the application
./pto fresh     # Seed the database
./pto build     # Build front-end assets
```

Then visit http://localhost:8000

### Available Commands

| Command | Description |
|---------|-------------|
| `./pto up` | Start all containers |
| `./pto down` | Stop and remove all containers |
| `./pto restart` | Restart all containers |
| `./pto start` | Start stopped containers |
| `./pto stop` | Stop running containers |
| `./pto ssh` | SSH into the app container |
| `./pto tinker` | Run Laravel Tinker |
| `./pto artisan <cmd>` | Run any Artisan command |
| `./pto composer <cmd>` | Run any Composer command |
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

- **PHP**: 7.2 (matches production)
- **Laravel**: 6.x
- **Database**: SQLite
- **Web Server**: Nginx
- **Node**: 18 (for asset compilation)

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

Requires composer and npm installed locally.

```bash
composer install
npm install
touch database/database.sqlite
php artisan migrate
gulp
php artisan serve
```
</details>
