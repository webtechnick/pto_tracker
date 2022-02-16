# PTO Tracker

Help Keep Track of Paid Time Off (PTO)

## Local Setup

### New way (recommended, requires composer, vagrant and virtualbox installed)

```
composer install
vagrant up
```

SSH into vagrant box to run artisan commands
```
vagrant ssh
cd code
touch database/database.sqlite
php artisan migrate
php artisan tinker
```


### Old way (requires composer, and npm)

```
composer install
npm install
touch database/database.sqlite
php artisan migrate
gulp
php artisan serve
```

## Production Setup

You'll need a google SSO app to connect to or use the one setup already for Continued LLC.

https://developers.google.com/identity/gsi/web/guides/get-google-api-clientid

Update the `.env` with Google SSO client ID, secret, redirect, and restricted email domain. Example:

```
GOOGLE_CLIENT_ID=very-long-key
GOOGLE_SECRET=super-secret-key
GOOGLE_REDIRECT=http://localhost:8000/login/google/callback
GOOGLE_DOMAIN=continued.com
```

Make sure to authorize the redirect URL in your google SSO app.

https://console.developers.google.com/apis


## Creating First Admin

1. Regsiter a user via the registration feature http://localhost:8000/register
2. SSH into your vagrant/production box `vagrant ssh && cd code`
3. Run artisan tinker `php artisan tinker`
4. Promote your user to admin role `App\User::first()->update(['role' => 'admin']);`
5. Log out and back in.