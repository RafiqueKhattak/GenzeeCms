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

> **Important**: This script **replaces the old genzeetools static site** with
> the new Laravel GenzeeCms app. The existing nginx config at
> `/etc/nginx/sites-available/genzeelogics.conf` will be overwritten, but SSL
> certificates (`/etc/letsencrypt/live/genzeelogics.com/`) are preserved. The
> site will go live immediately when the script finishes — there is no separate
> "flip switch" step.

1. **Verify the database exists** (it should already):

   ```bash
   mysql -u root -p -e "SHOW DATABASES LIKE 'laracms';"
   ```

   If you see it listed, great — the script will use it. If not, create it:

   ```bash
   mysql -u root -p -e "CREATE DATABASE laracms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

   Also create a dedicated MySQL user for this app (never share with Frappe):

   ```bash
   mysql -u root -p -e "CREATE USER 'laracms'@'localhost' IDENTIFIED BY 'pick-a-strong-password';"
   mysql -u root -p -e "GRANT ALL PRIVILEGES ON laracms.* TO 'laracms'@'localhost';"
   ```

2. **Run the deploy script** (from anywhere, or cd to `/opt/apps/LaraCms` first):

   ```bash
   cd /opt/apps/LaraCms
   bash deploy/deploy.sh
   ```

   On the **very first run**, the script will stop after copying
   `deploy/.env.production.example` to `.env`. You must edit it and set real values:

   - `DB_PASSWORD` — the password you set for the `laracms` user above
   - `ADMIN_PASSWORD` — initial password for the admin login (change it after
     first login from the admin panel; the seeder prints/stores it nowhere else)
   - Confirm `APP_URL` is `https://genzeelogics.com`
   - Confirm `DB_DATABASE=laracms` and `DB_USERNAME=laracms`

   Then **re-run the same command** — this time it will continue:
   - Composer/npm dependencies
   - Database migrations + seeding (creates admin user + site settings)
   - Frontend build (client + SSR bundles)
   - Laravel caches
   - PHP-FPM pool + socket
   - Nginx config (replaces genzeetools, keeps SSL)
   - SSR supervisor process

3. **Verify immediately after the script finishes**:

   ```bash
   curl -I https://genzeelogics.com/                # expect 200
   curl -I https://genzeelogics.com/tools/          # expect 200
   curl -I https://genzeelogics.com/admin           # expect 200 (or 302 to login)
   sudo supervisorctl status genzeelogics-ssr       # expect RUNNING
   sudo supervisorctl status | grep frappe-bench    # compare against the count you noted earlier — must be unchanged
   ```

   Open the site in a browser and view source (Ctrl+U) on a tool/blog page —
   you should see a real `<title>`, `<link rel="canonical">`, and
   `<script type="application/ld+json">` already present in the raw HTML
   (proof SSR is working, not just client-rendered).

## Optional: country data for the admin Analytics page

`/admin/analytics` shows a per-country breakdown, but it deliberately never
reads the visitor's IP address. The country code comes from a header set by
whatever CDN or proxy sits in front of the app — `CF-IPCountry`,
`CloudFront-Viewer-Country`, `X-Geo-Country` or `X-Country-Code`.

With the current nginx-only setup no such header exists, so every view is
recorded with an unknown country and the page shows a note saying so.
Everything else on that page (views, top pages, referrers, bots) works
regardless. To populate it, either:

- **Put Cloudflare in front of the domain** — the free plan sets
  `CF-IPCountry` automatically, nothing to change in this app; or
- **Install nginx's GeoIP2 module** and add to the server block:
  ```nginx
  proxy_set_header X-Geo-Country $geoip2_data_country_code;
  fastcgi_param   HTTP_X_GEO_COUNTRY $geoip2_data_country_code;
  ```

## Optional: keyword suggestions from the news API

`/admin/keywords` always fetches BBC News's official Business and Technology
RSS feeds (`feeds.bbci.co.uk`) — no key needed, nothing to configure. An
earlier candidate, the unofficial `bbc-news-api.vercel.app` wrapper, was
evaluated and rejected: its Business and Technology sections both returned
the same generic top-of-homepage stories rather than topic-scoped content,
which would have fed mislabeled text into the relevance filter.

It can also pull from NewsAPI.org if `NEWS_API_KEY` is set in `.env`, for
extra coverage. Get a free key from <https://newsapi.org> — it is an API key
issued at signup, not an account login, and nothing else is shared with it.
Without a key the page still fetches BBC headlines; you can also always add
keywords manually (for example from Google Trends).

```env
NEWS_API_KEY=your-free-key-here
```

## Laravel scheduler (one-time cron setup)

`posts:publish-due` (flips a scheduled post's status to `published` once its
publish time passes — see `routes/console.php`) only runs if the standard
Laravel scheduler cron entry exists for this app. Add it once, for the
`wardah` user, completely separate from any Frappe cron entries:

```bash
crontab -l   # check what's already there first
crontab -e   # add this line if it's not already present:
* * * * * cd /opt/apps/LaraCms && php artisan schedule:run >> /dev/null 2>&1
```

Verify what's due to run with `php artisan schedule:list` from
`/opt/apps/LaraCms`. This is a manual, one-time step — `deploy.sh`
deliberately does not touch crontab itself.

## Redeploying (every time after the first)

```bash
cd /opt/apps/LaraCms
bash deploy/deploy.sh
```

Safe to re-run any time. It will:
- Fetch the latest code from GitHub and reset to `origin/main`
- Reinstall composer/npm dependencies (as needed)
- Re-run migrations (no-op if nothing new)
- Rebuild the frontend + SSR bundle
- Restart the `genzeelogics-ssr` supervisor program only (never touches `frappe-bench-*`)
- Reload nginx/php-fpm only (never restarts, so other sites stay up)

> **Uncommitted server-side changes will be discarded** by `git reset --hard`
> — this script assumes `/opt/apps/LaraCms` only ever tracks what's in the
> repo. Edit only `.env` and things explicitly in `.gitignore` (`storage/`,
> `vendor/`, `node_modules/`, build output). Never hand-edit code files in
> the app directory.

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
