#!/usr/bin/env bash
#
# Deploy / redeploy GenzeeCms to /opt/apps/LaraCms.
#
# SAFETY: This script only ever touches files under /opt/apps/LaraCms,
# /etc/nginx/sites-available/genzeelogics.conf (+ its sites-enabled symlink),
# /etc/php/8.2/fpm/pool.d/genzeelogics.conf, and
# /etc/supervisor/conf.d/genzeelogics-ssr.conf. It never edits
# /home/wardah/frappe-bench/**, never restarts the frappe-bench-* supervisor
# groups, never changes the system Node version or `nvm alias default`, and
# only ever reloads (never restarts) nginx/php-fpm so other sites on this
# box are not interrupted.
#
# Run as a user with sudo access, from anywhere:
#   bash deploy.sh
#
# First run vs redeploy: the script detects whether the app is already
# migrated/built and adjusts (e.g. won't re-seed the DB if it's already
# seeded). Safe to re-run after every `git pull`.

set -eo pipefail

APP_DIR="/opt/apps/LaraCms"
DOMAIN="genzeelogics.com"
REPO_URL="https://github.com/RafiqueKhattak/GenzeeCms.git"
PHP_FPM_VERSION="8.2"
PHP_FPM_POOL_SOCK="/run/php/genzeelogics.sock"

bold() { printf '\033[1m%s\033[0m\n' "$1"; }
step() { echo; bold "==> $1"; }

# ---------------------------------------------------------------------------
step "Safety check: confirm ERPNext's system Node is untouched"
# ---------------------------------------------------------------------------
SYSTEM_NODE_VERSION="$(/usr/bin/node -v 2>/dev/null || echo 'MISSING')"
if [ "$SYSTEM_NODE_VERSION" != "v12.22.9" ]; then
    echo "REFUSING TO CONTINUE: /usr/bin/node reports '$SYSTEM_NODE_VERSION', expected v12.22.9."
    echo "This suggests the system Node install changed unexpectedly, which could break"
    echo "Frappe ERPNext's node-socketio process. Stop and investigate before deploying."
    exit 1
fi
echo "OK — system Node is still v12.22.9 (untouched)."

if command -v supervisorctl >/dev/null 2>&1; then
    FRAPPE_BEFORE="$(sudo supervisorctl status 2>/dev/null | grep -c '^frappe-bench-' || true)"
fi

# ---------------------------------------------------------------------------
step "Fetch/update the code"
# ---------------------------------------------------------------------------
if [ -d "$APP_DIR/.git" ]; then
    cd "$APP_DIR"
    git fetch origin main
    git reset --hard origin/main
else
    sudo mkdir -p "$APP_DIR"
    sudo chown "$(whoami)":"$(whoami)" "$APP_DIR"
    git clone "$REPO_URL" "$APP_DIR"
    cd "$APP_DIR"
fi

# ---------------------------------------------------------------------------
step "Activate Node 20 via nvm (.nvmrc) — never the system Node"
# ---------------------------------------------------------------------------
export NVM_DIR="$HOME/.nvm"
# shellcheck disable=SC1091
[ -s "$NVM_DIR/nvm.sh" ] && source "$NVM_DIR/nvm.sh"
if ! command -v nvm >/dev/null 2>&1; then
    echo "REFUSING TO CONTINUE: nvm not found for this user. Install/verify nvm and its"
    echo "v20.20.2 Node before re-running — do NOT fall back to the system Node."
    exit 1
fi
nvm use
NODE_ACTIVE="$(node -v)"
if [[ "$NODE_ACTIVE" != v20* ]]; then
    echo "REFUSING TO CONTINUE: active Node is '$NODE_ACTIVE' after 'nvm use', expected v20.x."
    exit 1
fi
echo "OK — using Node $NODE_ACTIVE via nvm."

# ---------------------------------------------------------------------------
step "PHP dependencies"
# ---------------------------------------------------------------------------
composer install --no-dev --optimize-autoloader --no-interaction

# ---------------------------------------------------------------------------
step "Environment file"
# ---------------------------------------------------------------------------
if [ ! -f "$APP_DIR/.env" ]; then
    cp "$APP_DIR/deploy/.env.production.example" "$APP_DIR/.env"
    echo "Created .env from deploy/.env.production.example — EDIT IT NOW with real DB"
    echo "credentials, then re-run this script. Stopping here on first run."
    exit 1
fi

php artisan key:generate --force --no-interaction 2>&1 | grep -v "already set" || true

# ---------------------------------------------------------------------------
step "Database"
# ---------------------------------------------------------------------------
php artisan migrate --force

# Only seed once — safe to run repeatedly since AdminUserSeeder/SettingsSeeder
# no-op if the admin user / settings already exist.
php artisan db:seed --force

php artisan storage:link || true

# ---------------------------------------------------------------------------
step "Frontend build (client + SSR bundles)"
# ---------------------------------------------------------------------------
npm ci
npm run build

# ---------------------------------------------------------------------------
step "Laravel production caches"
# ---------------------------------------------------------------------------
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---------------------------------------------------------------------------
step "File permissions"
# ---------------------------------------------------------------------------
WEB_USER="www-data"
sudo chown -R "$(whoami)":"$WEB_USER" "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
sudo chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# ---------------------------------------------------------------------------
step "PHP-FPM pool (dedicated, isolated from other sites)"
# ---------------------------------------------------------------------------
sudo cp "$APP_DIR/deploy/php-fpm-pool.conf" "/etc/php/${PHP_FPM_VERSION}/fpm/pool.d/genzeelogics.conf"
sudo systemctl reload "php${PHP_FPM_VERSION}-fpm"
echo "OK — php${PHP_FPM_VERSION}-fpm reloaded (not restarted)."

# ---------------------------------------------------------------------------
step "Nginx server block (replaces old genzeelogics.conf, keeps SSL certs)"
# ---------------------------------------------------------------------------
sudo cp "$APP_DIR/deploy/nginx-genzeelogics.conf" "/etc/nginx/sites-available/genzeelogics.conf"
sudo ln -sf "/etc/nginx/sites-available/genzeelogics.conf" "/etc/nginx/sites-enabled/genzeelogics.conf"
sudo nginx -t
sudo systemctl reload nginx
echo "OK — nginx reloaded (not restarted); other sites on this box were not touched."

# ---------------------------------------------------------------------------
step "Inertia SSR process (supervisor, isolated from frappe-bench-* groups)"
# ---------------------------------------------------------------------------
NODE_BIN_DIR="$(dirname "$(command -v node)")"
sed "s#{{NODE_BIN_DIR}}#${NODE_BIN_DIR}#g; s#{{APP_DIR}}#${APP_DIR}#g; s#{{DEPLOY_USER}}#$(whoami)#g" \
    "$APP_DIR/deploy/supervisor-inertia-ssr.conf.template" \
    | sudo tee /etc/supervisor/conf.d/genzeelogics-ssr.conf >/dev/null
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart genzeelogics-ssr || sudo supervisorctl start genzeelogics-ssr

if command -v supervisorctl >/dev/null 2>&1 && [ -n "${FRAPPE_BEFORE:-}" ]; then
    FRAPPE_AFTER="$(sudo supervisorctl status 2>/dev/null | grep -c '^frappe-bench-' || true)"
    if [ "$FRAPPE_BEFORE" != "$FRAPPE_AFTER" ]; then
        echo "WARNING: frappe-bench-* program count changed ($FRAPPE_BEFORE -> $FRAPPE_AFTER)."
        echo "Investigate immediately with: sudo supervisorctl status"
    else
        echo "OK — frappe-bench-* supervisor programs unaffected ($FRAPPE_AFTER running)."
    fi
fi

step "Done"
echo "Visit https://${DOMAIN} to verify. Check SSR logs with:"
echo "  sudo supervisorctl tail -f genzeelogics-ssr"
echo "Check app logs with:"
echo "  tail -f ${APP_DIR}/storage/logs/laravel.log"
