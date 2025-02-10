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

**Note**: requires older versions of node than you probably have or you will get compilation errors.
If you get errors running `npm install`, you can install nvm (`brew install nvm`) and then install a lower node version such as `nvm install 10`
You may also need to run `npm install` with the `--legacy-peer-deps` flag.
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