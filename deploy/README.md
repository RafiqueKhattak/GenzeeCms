# Deploying GenzeeCms to the production server

This folder contains everything needed to deploy **GenzeeCms** to
`/opt/apps/LaraCms` on the server that also runs **Frappe ERPNext**
(untouchable production data, Node v12.22.9, Supervisor groups
`frappe-bench-*`). Read `../CLAUDE.md` first if you haven't — it explains
why this deploy is deliberately paranoid about isolation.

Everything here only ever touches:
- `/opt/apps/LaraCms/**`
- `/etc/nginx/sites-available/genzeelogics.com` (+ its `sites-enabled` symlink)
- `/etc/php/8.2/fpm/pool.d/genzeelogics.conf`
- `/etc/supervisor/conf.d/genzeelogics-ssr.conf`

It never edits `/home/wardah/frappe-bench/**`, never restarts
`frappe-bench-*` supervisor groups, never changes the system Node version
or `nvm alias default`, and only ever **reloads** (never restarts)
nginx/php-fpm so other sites on the box keep running.

## Prerequisites (check once, before the first deploy)

Run these as the `wardah` user (or whichever deploy user has sudo):

```bash
/usr/bin/node -v          # must print v12.22.9 — do not touch this
nvm --version              # nvm must be installed for this user
nvm ls                     # v20.20.2 should be listed (installed already per .nvmrc)
php -v                     # 8.2.x
nginx -v                   # 1.18.x
composer --version
sudo supervisorctl status | grep frappe-bench   # note how many are running, for comparison later
mysql -u root -p -e "SELECT 1;"                 # confirms MySQL access for creating the app DB
```

If `nvm ls` doesn't show v20.20.2, install it **for this user only**,
without touching the default alias:

```bash
nvm install 20.20.2
# do NOT run: nvm alias default 20.20.2   <-- this would break Frappe on next login
```

`cd /opt/apps/LaraCms` (once the repo is cloned) will then auto-select
v20.20.2 via `.nvmrc` in interactive shells.

## First-time deploy

1. **Create a dedicated MySQL database and user** (never reuse Frappe's):

   ```bash
   mysql -u root -p -e "CREATE DATABASE genzeelogics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p -e "CREATE USER 'genzeelogics'@'localhost' IDENTIFIED BY 'pick-a-strong-password';"
   mysql -u root -p -e "GRANT ALL PRIVILEGES ON genzeelogics.* TO 'genzeelogics'@'localhost';"
   ```

2. **Run the deploy script**:

   ```bash
   bash <(curl -fsSL https://raw.githubusercontent.com/RafiqueKhattak/GenzeeCms/main/deploy/deploy.sh)
   ```

   or, if you'd rather clone first and run locally:

   ```bash
   git clone https://github.com/RafiqueKhattak/GenzeeCms.git /opt/apps/LaraCms
   cd /opt/apps/LaraCms
   bash deploy/deploy.sh
   ```

   On the very first run, the script will stop right after copying
   `deploy/.env.production.example` to `.env`, because you need to fill in
   real values first — most importantly:

   - `DB_PASSWORD` (the password you set in step 1)
   - `ADMIN_PASSWORD` (used once by the seeder to create the admin login —
     change it from the admin panel after first login)
   - Double check `APP_URL` is `https://genzeelogics.com`

   Edit `/opt/apps/LaraCms/.env`, then **re-run the same command** — this
   time it will proceed through migrations, seeding, the frontend build,
   nginx/php-fpm/supervisor setup, and finish.

3. **DNS**: make sure `genzeelogics.com` and `www.genzeelogics.com` A
   records already point at this server's IP before moving on — certbot's
   HTTP validation (next step) needs that to succeed.

4. **Add HTTPS** (one-time, after the first successful deploy):

   ```bash
   sudo apt-get install -y certbot python3-certbot-nginx   # if not already installed
   sudo certbot --nginx -d genzeelogics.com -d www.genzeelogics.com
   ```

   Certbot edits `/etc/nginx/sites-available/genzeelogics.com` in place to
   add the HTTPS server block and redirect — it only touches this site's
   file, not other sites' configs.

5. **Verify**:

   ```bash
   curl -I https://genzeelogics.com/                # expect 200
   curl -I https://genzeelogics.com/tools/           # expect 200
   sudo supervisorctl status genzeelogics-ssr        # expect RUNNING
   sudo supervisorctl status | grep frappe-bench     # compare against the count you noted earlier — must be unchanged
   ```

   Open the site in a browser and view source (Ctrl+U) on a tool/blog
   page — you should see a real `<title>`, `<link rel="canonical">`, and
   `<script type="application/ld+json">` already present in the raw HTML
   (proof SSR is actually working, not just client-rendered).

## Redeploying (every time after the first)

```bash
cd /opt/apps/LaraCms
bash deploy/deploy.sh
```

Safe to re-run any time — it does `git fetch && git reset --hard
origin/main`, reinstalls dependencies, re-runs migrations (no-op if
nothing new), rebuilds the frontend, and restarts only the
`genzeelogics-ssr` supervisor program (never `frappe-bench-*`).

> **Uncommitted server-side changes will be discarded** by `git reset
> --hard` — this script assumes `/opt/apps/LaraCms` only ever tracks what's
> in the repo. Don't hand-edit files inside it outside of `.env` and
> anything explicitly `.gitignore`d (storage, `.env`, `vendor`,
> `node_modules`, build output).

## Troubleshooting

**Site loads but view-source shows no canonical/JSON-LD (SSR not working):**
```bash
sudo supervisorctl status genzeelogics-ssr
sudo supervisorctl tail -f genzeelogics-ssr        # stdout
sudo supervisorctl tail -f genzeelogics-ssr stderr
```
Common cause: the SSR process is serving a stale bundle after a build that
didn't restart it — `deploy.sh` always restarts it, so this should only
happen if you ran `npm run build` by hand outside the script.

**500 errors after deploy:**
```bash
tail -100 /opt/apps/LaraCms/storage/logs/laravel.log
tail -50 /opt/apps/LaraCms/storage/logs/php-fpm-error.log
```

**nginx `-t` fails during deploy:** the script stops before reloading, so
the previously-working config (if any) stays live — other sites are never
at risk. Fix the syntax error in `deploy/nginx-genzeelogics.conf` in the
repo, commit, and re-run.

**Any doubt about Frappe/ERPNext being affected:** stop and check
`sudo supervisorctl status` — every `frappe-bench-*` program should show
the same state as before you started. `/usr/bin/node -v` should always
still report `v12.22.9`. If either looks wrong, stop immediately and
investigate before doing anything else — do not try to "fix" ERPNext
yourself; escalate to the project owner.

## Files in this folder

| File | Installed to | Purpose |
|---|---|---|
| `deploy.sh` | (run directly) | Orchestrates the whole deploy, with safety checks |
| `nginx-genzeelogics.conf` | `/etc/nginx/sites-available/genzeelogics.com` | Isolated server block for this site only |
| `php-fpm-pool.conf` | `/etc/php/8.2/fpm/pool.d/genzeelogics.conf` | Dedicated PHP-FPM pool, own socket |
| `supervisor-inertia-ssr.conf.template` | `/etc/supervisor/conf.d/genzeelogics-ssr.conf` | Keeps the SSR Node process running, pinned to Node 20 via `PATH`, never grouped with `frappe-bench-*` |
| `.env.production.example` | `/opt/apps/LaraCms/.env` (first run only) | Production environment template — edit before first deploy |
