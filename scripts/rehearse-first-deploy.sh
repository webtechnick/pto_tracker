#!/usr/bin/env bash
# Rehearse the first deploy of this release onto a server still running the
# Laravel 6 / PHP 7.2 release (master before the upgrade), in Docker.
#
# Each scenario builds a fresh "server": a clone of the old release with its
# Laravel 6 vendor/, migrated + seeded SQLite DB and config/route caches built
# on PHP 7.2, and the production-style .env. Its origin then moves to the new
# release and the server's PHP is "upgraded" (commands run in the PHP 8.4
# image):
#
#   A  ./pto deploy (real script, tsh stubbed) after an in-place PHP upgrade
#   B  code pulled first, then `bash prod-update.sh`
#   C  the old prod-update.sh run by hand (fails), then the documented recovery
#   D  ./pto deploy messages (Teleport hint only for auth failures)
#   E  `bash prod-update.sh` from a release whose script differs (self re-run)
#   F  web server still on PHP 7.2 -> post-deploy check fails loudly
#   G  first deploy failing in composer install -> static 503, clear message
#   G2 same, but no maintenance page can be written -> "THE SITE IS DOWN"
#
# Deploy commands run with `php -S` on localhost:80 standing in for nginx +
# php-fpm, so prod-update.sh's post-deploy check hits a real web server.
#
# Usage: scripts/rehearse-first-deploy.sh [NEW_REF] [OLD_REF]
#   NEW_REF  release to deploy (default HEAD; must be committed)
#   OLD_REF  release the server runs (default: merge-base with master)
# Requires docker, the app image (docker compose build app) and php:7.2-cli.
set -euo pipefail

REPO="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
NEW_SHA="$(git -C "$REPO" rev-parse "${1:-HEAD}")"
OLD_SHA="$(git -C "$REPO" rev-parse "${2:-$(git -C "$REPO" merge-base HEAD master)}")"
NEW_IMAGE="${PTO_NEW_IMAGE:-workspace-app}"
OLD_IMAGE="${PTO_OLD_IMAGE:-php:7.2-cli}"
APP_DIR=/var/www/laravel/pto_tracker
WORK="$(mktemp -d /tmp/pto-first-deploy.XXXXXX)"
APP_KEY='base64:ej+h3QVyL4hw/y5yKl79R3qKyG4OBUjUK8MOsC62Tug='

say() { echo -e "\n\033[1;34m=== $*\033[0m"; }
pass() { echo -e "\033[0;32mPASS\033[0m $*"; }
fail() { echo -e "\033[0;31mFAIL\033[0m $*"; FAILED=1; }
FAILED=0

cleanup() { docker run --rm -v "$WORK:$WORK" "$NEW_IMAGE" rm -rf "$WORK" >/dev/null 2>&1 || true; }
trap cleanup EXIT

# Run a command on the "server" (project mounted at $APP_DIR) with the given
# PHP image. Root inside the container; sudo is a pass-through shim.
on_server() {
    local image="$1" server="$2"; shift 2
    docker run --rm -i -v "$server:$APP_DIR" -v "$WORK/origin.git:$WORK/origin.git" -w "$APP_DIR" \
        "$image" bash -c 'printf "#!/bin/sh\nexec \"\$@\"\n" > /usr/local/bin/sudo; chmod +x /usr/local/bin/sudo
            command -v git >/dev/null && git config --global --add safe.directory "*"
            git config --global user.email deploy@example.com 2>/dev/null; git config --global user.name deploy 2>/dev/null
            '"$*"
}

# display_errors=0 as on production php-fpm (otherwise a fatal error is a 200).
WEB='php -d display_errors=0 -S 127.0.0.1:80 -t '"$APP_DIR"'/public > /dev/null 2>&1 & sleep 0.5;'

build_old_server() {
    local server="$WORK/$1"
    git -C "$WORK/origin.git" update-ref refs/heads/master "$OLD_SHA"
    git clone -q -b master "$WORK/origin.git" "$server"
    docker run --rm "$NEW_IMAGE" cat /usr/bin/composer > "$server/composer.phar"
    touch "$server/database/database.sqlite"
    cat > "$server/.env" <<EOF
APP_NAME=PTO_Tracker
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=http://localhost
APP_DEPARTMENT="IT"
TAG_MODEL=App\Tag
SESSION_DOMAIN="localhost"
MAIL_DRIVER=smtp # for laravel < 7
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_ENCRYPTION=tls
QUEUE_DRIVER=sync
CACHE_DRIVER=file
SESSION_DRIVER=file
DB_CONNECTION=sqlite
DB_DATABASE=${APP_DIR}/database/database.sqlite
API_TOKEN=rehearsal-token
EOF
    # Laravel 6 vendor/ from the old lock (installed with the new image's
    # composer, which has git), then everything else on PHP 7.2 as in prod.
    on_server "$NEW_IMAGE" "$server" 'composer install --no-dev --no-interaction --no-scripts --ignore-platform-reqs -q' >/dev/null
    on_server "$OLD_IMAGE" "$server" 'php artisan package:discover -q && php artisan migrate --force -q &&
        php artisan tinker --execute="App\Employee::forceCreate([\"name\" => \"Legacy Employee\", \"color\" => \"white\", \"bgcolor\" => \"green\"]); App\Holiday::forceCreate([\"title\" => \"Legacy Holiday\", \"date\" => \"2026-12-25\"]);" &&
        php artisan config:cache -q && php artisan route:cache -q && php artisan --version'
    git -C "$WORK/origin.git" update-ref refs/heads/master "$NEW_SHA"
}

verify_server() {
    local name="$1" server="$WORK/$1" expected_sha="${2:-$NEW_SHA}"
    local out
    out="$(on_server "$NEW_IMAGE" "$server" '
        php artisan --version
        [ -f storage/framework/down ] && echo "STILL-DOWN" || echo "LIVE"
        echo "HEAD $(git rev-parse HEAD)"
        php -S 127.0.0.1:8099 -t public > /dev/null 2>&1 &
        sleep 1
        echo "login $(curl -s -o /dev/null -w "%{http_code}" http://127.0.0.1:8099/login)"
        echo "employees $(curl -s -H "Authorization: Bearer rehearsal-token" http://127.0.0.1:8099/api/employees)"
        echo "holidays $(curl -s -H "Authorization: Bearer rehearsal-token" "http://127.0.0.1:8099/api/holidays?start_date=2026-01-01&end_date=2026-12-31")"
        php -r "require \"vendor/autoload.php\"; \$a = require \"bootstrap/app.php\"; \$a->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap(); echo \"mail \", config(\"mail.default\"), \" \", (string) \$a[\"mail.manager\"]->mailer()->getSymfonyTransport(), \"\n\";"
    ' || true)"
    echo "$out"
    grep -q "Laravel Framework 13\." <<<"$out" && pass "$name: Laravel 13 installed" || fail "$name: not on Laravel 13"
    grep -q "^LIVE" <<<"$out" && pass "$name: out of maintenance mode" || fail "$name: left in maintenance mode"
    grep -q "^HEAD $expected_sha" <<<"$out" && pass "$name: new release checked out" || fail "$name: wrong commit"
    grep -q "^login 200" <<<"$out" && pass "$name: /login 200" || fail "$name: /login not 200"
    grep -q '"name":"Legacy Employee"' <<<"$out" && pass "$name: existing employee data served by API" || fail "$name: employee data missing"
    grep -q '"title":"Legacy Holiday"' <<<"$out" && pass "$name: existing holiday data served by API" || fail "$name: holiday data missing"
    grep -q "^mail smtp smtp://smtp.sendgrid.net:587" <<<"$out" && pass "$name: production mail .env -> smtp://smtp.sendgrid.net:587" || fail "$name: mail config wrong"
}

say "Releases: server runs ${OLD_SHA:0:7}, deploying ${NEW_SHA:0:7}"
git clone -q --bare "$REPO" "$WORK/origin.git"

# --- A: PHP upgraded in place, then ./pto deploy ---------------------------
say "A: PHP upgraded in place on a master server, then ./pto deploy"
build_old_server A
mkdir -p "$WORK/bin"
cat > "$WORK/bin/tsh" <<EOF
#!/usr/bin/env bash
# Teleport stub: "status" succeeds, "ssh host cmd" runs cmd on server A (PHP 8.4).
case "\$1" in
    status) exit 0 ;;
    ssh) shift 2; exec docker run --rm -v "$WORK/A:$APP_DIR" -v "$WORK/origin.git:$WORK/origin.git" -w / "$NEW_IMAGE" \
        bash -c 'printf "#!/bin/sh\nexec \"\\\$@\"\n" > /usr/local/bin/sudo; chmod +x /usr/local/bin/sudo; git config --global --add safe.directory "*"; $WEB '"\$*" ;;
esac
exit 2
EOF
chmod +x "$WORK/bin/tsh"
if PATH="$WORK/bin:$PATH" PROD_USERNAME=deploy PROD_HOST=pto-rehearsal "$REPO/pto" deploy; then
    pass "A: ./pto deploy exited 0"
else
    fail "A: ./pto deploy failed"
fi
verify_server A

# --- B: code pulled first, then the new prod-update.sh ---------------------
say "B: code pulled first (Laravel 6 vendor/ + caches left behind), then ./prod-update.sh"
build_old_server B
if on_server "$NEW_IMAGE" "$WORK/B" "$WEB"' git pull -q origin master && bash prod-update.sh'; then
    pass "B: prod-update.sh exited 0"
else
    fail "B: prod-update.sh failed"
fi
verify_server B

# --- C: old prod-update.sh run by hand, then recovery ------------------------
say "C: the server's old prod-update.sh run by hand on PHP 8.4, then the documented recovery"
build_old_server C
if on_server "$NEW_IMAGE" "$WORK/C" './prod-update.sh'; then
    fail "C: old prod-update.sh unexpectedly succeeded"
else
    pass "C: old prod-update.sh fails on PHP 8.4 (expected; it cannot be fixed retroactively)"
fi
if on_server "$NEW_IMAGE" "$WORK/C" "$WEB"' git pull -q origin master && ./prod-update.sh'; then
    pass "C: recovery (git pull origin master && ./prod-update.sh) exited 0"
else
    fail "C: recovery failed"
fi
verify_server C

# --- D: Teleport hint only for auth failures ---------------------------------
say "D: ./pto deploy messages"
printf '#!/bin/sh\n[ "$1" = status ] && exit 1\nexit 0\n' > "$WORK/bin/tsh"
out="$(PATH="$WORK/bin:$PATH" PROD_USERNAME=deploy PROD_HOST=pto-rehearsal "$REPO/pto" deploy 2>&1 || true)"
grep -q "tsh login" <<<"$out" && pass "D: expired Teleport session -> tsh login hint" || fail "D: no tsh login hint: $out"
printf '#!/bin/sh\n[ "$1" = status ] && exit 0\ncase "$*" in *PHP_VERSION_ID*) echo 80427; exit 0;; esac\necho "remote failure"; exit 1\n' > "$WORK/bin/tsh"
out="$(PATH="$WORK/bin:$PATH" PROD_USERNAME=deploy PROD_HOST=pto-rehearsal "$REPO/pto" deploy 2>&1 || true)"
grep -q "Deployment failed on pto-rehearsal" <<<"$out" && ! grep -q "tsh login" <<<"$out" \
    && pass "D: remote script failure -> no Teleport hint" || fail "D: wrong failure message: $out"
printf '#!/bin/sh\n[ "$1" = status ] && exit 0\necho 70234\n' > "$WORK/bin/tsh"
out="$(PATH="$WORK/bin:$PATH" PROD_USERNAME=deploy PROD_HOST=pto-rehearsal "$REPO/pto" deploy 2>&1 || true)"
grep -q "runs PHP 70234" <<<"$out" && pass "D: PHP 7.2 server refused before deploying" || fail "D: PHP check: $out"


# --- E: self re-run when started as `bash prod-update.sh` -------------------
say "E: bash prod-update.sh on this release, pulling a next release whose script differs (self re-run)"
git clone -q "$WORK/origin.git" "$WORK/E" -b master
git -C "$WORK/E" checkout -q -B master "$NEW_SHA"
cp "$WORK/A/.env" "$WORK/E/.env"; cp "$WORK/A/composer.phar" "$WORK/E/composer.phar"; touch "$WORK/E/database/database.sqlite"
on_server "$NEW_IMAGE" "$WORK/E" 'composer install -q --no-dev --no-interaction && php artisan migrate --force -q &&
    php artisan tinker --execute="App\Employee::forceCreate([\"name\" => \"Legacy Employee\", \"color\" => \"white\", \"bgcolor\" => \"green\"]); App\Holiday::forceCreate([\"title\" => \"Legacy Holiday\", \"date\" => \"2026-12-25\"]);"'
git clone -q "$WORK/origin.git" "$WORK/next" -b master
git -C "$WORK/next" checkout -q "$NEW_SHA"
printf '\necho "NEXT-RELEASE-SCRIPT-RAN"\n' >> "$WORK/next/prod-update.sh"
git -C "$WORK/next" -c user.email=r@example.com -c user.name=r commit -qam "next release"
git -C "$WORK/next" push -q origin HEAD:refs/heads/master
NEXT_SHA="$(git -C "$WORK/next" rev-parse HEAD)"
out="$(on_server "$NEW_IMAGE" "$WORK/E" "$WEB"' bash prod-update.sh' 2>&1)" && rc=0 || rc=$?
echo "$out" | grep -E "INFO|WARN|ERROR|NEXT-RELEASE" || true
[ "$rc" = 0 ] && pass "E: bash prod-update.sh exited 0" || fail "E: bash prod-update.sh exited $rc"
grep -q "re-running the updated script (/var/www/laravel/pto_tracker/prod-update.sh)" <<<"$out" \
    && pass "E: re-ran the updated script by absolute path" || fail "E: no self re-run"
grep -q "^NEXT-RELEASE-SCRIPT-RAN" <<<"$out" && pass "E: the pulled script's steps ran" || fail "E: pulled script did not run"
verify_server E "$NEXT_SHA"
git -C "$WORK/origin.git" update-ref refs/heads/master "$NEW_SHA"

# --- F: web server still on PHP 7.2 -----------------------------------------
say "F: CLI upgraded to PHP 8.4 but the web server still runs PHP 7.2"
build_old_server F
docker run -d --rm --name pto-rehearsal-web72 -v "$WORK/F:$APP_DIR" -w "$APP_DIR" "$OLD_IMAGE" \
    php -d display_errors=0 -S 127.0.0.1:80 -t "$APP_DIR/public" > /dev/null
out="$(docker run --rm -i --network container:pto-rehearsal-web72 -v "$WORK/F:$APP_DIR" -v "$WORK/origin.git:$WORK/origin.git" -w "$APP_DIR" "$NEW_IMAGE" \
    bash -c 'printf "#!/bin/sh\nexec \"\$@\"\n" > /usr/local/bin/sudo; chmod +x /usr/local/bin/sudo; git config --global --add safe.directory "*"; git pull -q origin master && bash prod-update.sh' 2>&1)" && rc=0 || rc=$?
docker rm -f pto-rehearsal-web72 > /dev/null 2>&1 || true
echo "$out" | grep -E "ERROR|Web server answered" || true
[ "$rc" != 0 ] && pass "F: deploy exits non-zero ($rc)" || fail "F: deploy reported success with the web server on PHP 7.2"
grep -q "Post-deploy check failed: http://localhost/login returned HTTP 500" <<<"$out" \
    && pass "F: post-deploy check reports HTTP 500" || fail "F: no post-deploy failure message"

# --- G: first deploy fails in composer install ------------------------------
say "G: first deploy fails during composer install"
build_old_server G
printf '<?php fwrite(STDERR, "simulated composer failure\\n"); exit(1);\n' > "$WORK/G/composer.phar"
out="$(on_server "$NEW_IMAGE" "$WORK/G" "$WEB"' git pull -q origin master && bash prod-update.sh; echo "rc=$?"; echo "site $(curl -s -o /tmp/b -w "%{http_code}" http://localhost/login) $(grep -o "Be right back" /tmp/b | head -1)"' 2>&1)"
echo "$out" | grep -E "ERROR|WARN|^rc=|^site" || true
grep -q "^rc=1" <<<"$out" && pass "G: deploy exits 1" || fail "G: wrong exit code"
grep -q "users get the maintenance page (HTTP 503" <<<"$out" && pass "G: error says the site is in maintenance (HTTP 503)" || fail "G: unclear failure message"
grep -q "^site 503 Be right back" <<<"$out" && pass "G: site serves the static 503 page, not a 500" || fail "G: site not in maintenance"

say "G2: first deploy fails during composer install and no maintenance page can be written"
build_old_server G2
printf '<?php exit(1);\n' > "$WORK/G2/composer.phar"
out="$(on_server "$NEW_IMAGE" "$WORK/G2" "$WEB"' git pull -q origin master && mkdir -p storage/framework/maintenance.php && bash prod-update.sh; echo "rc=$?"' 2>&1)"
echo "$out" | grep -E "ERROR|^rc=" || true
grep -q "THE SITE IS DOWN: http://localhost/login returns HTTP 500" <<<"$out" \
    && pass "G2: error says THE SITE IS DOWN (HTTP 500)" || fail "G2: unclear failure message"

say "Result"
if [ "$FAILED" = 0 ]; then
    echo -e "\033[0;32mAll first-deploy scenarios passed.\033[0m"
else
    echo -e "\033[0;31mSome scenarios failed.\033[0m"
    exit 1
fi
