# CLAUDE.md — Context for Claude Code sessions on this repo

This file is read automatically at the start of every session. It exists so
that after `/clear` (or in a brand-new session), Claude does not need to
re-derive project context, server layout, or the hard safety rules below.

## What this project is

**GenzeeCms** is a Laravel 12 + Breeze + Inertia + Vue 3 rewrite of
**genzeelogics.com**, a static site (previously in the `genzeetools` repo)
offering 46 free in-browser calculators, a blog, a news section, and static
pages (about/contact/privacy-policy/disclaimer/terms/editorial). The goal of
the migration was: full feature parity with zero SEO/Search-Console
regression, plus a full admin CMS (WordPress-Pro-style) to manage everything
without touching code or Git.

The migration was done in phases, all complete as of this writing:

1. DB schema + Eloquent models (Tools, Posts [blog/news], Categories, Tags,
   Pages, Media, Settings, Users/roles, ActivityLog, Redirects)
2. Legacy content import (`php artisan content:import {path}`) — pulled all
   46 tools, 40 blog posts, 41 news items and 6 static pages out of the old
   static HTML + `data/*.json`, preserving meta/canonical/OG/JSON-LD/FAQs
3. Public frontend (Inertia + Vue, **SSR enabled** — see below) at the exact
   legacy URLs with trailing slashes (`/tools/{slug}/`, `/blog/{slug}/`, etc.)
4. All 46 calculators ported from the legacy vanilla-JS files to native
   Vue 3 components (`resources/js/Components/Calculators/`), verified
   against the original worked examples
5. SEO parity: dynamic `/sitemap.xml`, `/robots.txt`, `/ads.txt`, per-page
   canonical/meta/OG/JSON-LD, Google Analytics — all settings-driven
6. Admin panel (`/admin`) reusing Breeze's `AuthenticatedLayout` and
   Tailwind components: Dashboard, Tools/Posts/Pages/Categories/Media/
   Users/Settings CRUD, rich text editor (TipTap), image/video uploads
7. Real-time AdSense policy checker in the post editor (rule-based today;
   architected behind `ContentPolicyCheckerInterface` so an LLM-backed
   checker can be swapped in later via `config('policy-checker.driver')`
   with no controller/frontend changes)
8. Redirect manager (`/admin/redirects`) — 301/302 redirects for retired
   slugs, wired centrally via a `NotFoundHttpException` render handler in
   `bootstrap/app.php` so it catches both "no route matched" and "route
   matched but record not found" 404s
9. Full QA pass: all 127 content pages' titles/canonicals compared against
   the original site, all 137 sitemap URLs verified 200, full regression
   pass on public site + admin panel

QA passed clean (127/127 canonicals matched exactly, 120/127 titles exact —
the other 7 differ only in `&` vs `&amp;` raw-HTML encoding in `<title>`,
which is a well-tolerated HTML parsing quirk with zero real-world impact,
not a data bug — verified by hand, not worth patching a third-party lib
over).

## Git identity — IMPORTANT, do not skip

The user has explicitly required that **no Claude/Anthropic name or icon
ever appears as a contributor on this repo**. The sandbox's global git
config defaults to `Claude <noreply@anthropic.com>`, which is WRONG for
this repo. Every commit must be made with:

```bash
GIT_AUTHOR_NAME="Wardah Server" GIT_AUTHOR_EMAIL="rafiquekhattak@protonmail.com" \
GIT_COMMITTER_NAME="Wardah Server" GIT_COMMITTER_EMAIL="rafiquekhattak@protonmail.com" \
git commit -m "..."
```

Do **not** add a `Co-Authored-By: Claude` trailer or a `Claude-Session:` link
to commits in this repo — that default behavior described elsewhere is
overridden by the user's explicit instruction for this project. Before
pushing, always double check: `git log -1 --format='%an <%ae>'` should show
`Wardah Server <rafiquekhattak@protonmail.com>`, never Claude.

Branch: work directly on `main` (the user's production deploy is linked to
`main`, not a feature branch — confirmed explicitly).

## Production server — READ CAREFULLY BEFORE TOUCHING

SSH: `wardah@113.203.192.145` (password given out-of-band to earlier
sessions — ask the user again if you don't have it, don't guess).

This server runs **two apps side by side**. One of them is irreplaceable
production data and must be treated as untouchable infrastructure:

### 1. Frappe ERPNext (v13) — DO NOT TOUCH

- Path: `/home/wardah/frappe-bench/`
- Node version: **v12.22.9, system-wide** (`/usr/bin/node`)
- Runs via Supervisor: `frappe-bench-web`, `frappe-bench-workers`,
  `frappe-bench-redis` groups — config at
  `/home/wardah/frappe-bench/config/supervisor.conf`
- Its node-socketio process hardcodes the path
  `/home/wardah/.nvm/versions/node/v12.22.9/bin/node`
- **Holds 5 years of production company data.** There is no acceptable
  reason to modify its config, restart its supervisor groups, or change
  any Node version it depends on. If a deployment step for the Laravel
  app seems to require touching anything under `frappe-bench/` or the
  system Node install, STOP and ask the user first — that almost
  certainly means something is misconfigured on the Laravel side, not
  that ERPNext genuinely needs to change.

### 2. This Laravel app (GenzeeCms) — deploys here

- Path: `/opt/apps/LaraCms/`
- Node version: **v20.20.2, via nvm**, selected by a `.nvmrc` file already
  present in that directory
- PHP 8.2.30 is installed
- Nginx 1.18.0 is already installed and running (also serves other sites —
  add a new server block, don't touch existing ones)
- Domain: `genzeelogics.com` is being pointed at this app
- The user's `~/.bashrc` defines a `cd()` function that auto-activates the
  right nvm version via `.nvmrc` whenever you `cd` into a directory that has
  one — so a plain `cd /opt/apps/LaraCms && npm run build` picks up Node 20
  automatically, as long as you're in an interactive shell that sources
  `.bashrc`. Non-interactive SSH commands (`ssh host 'command'`) do **not**
  source `.bashrc` by default — either use `nvm use` explicitly or run
  through a login/interactive shell, and verify with `node -v` before
  relying on it.

### Absolute rules for server work

- **Never** change the system-wide Node version (`/usr/bin/node`, v12.22.9).
- **Never** run `nvm alias default <anything>` — it must stay `12.22.9`.
- For all Laravel/Node work on this server, rely on `nvm use` or the
  `.nvmrc` in `/opt/apps/LaraCms/` — never install packages or run builds
  against the system Node.
- Before any command that touches Supervisor, nginx's existing sites, or
  anything under `frappe-bench/`, stop and confirm with the user.
- This app needs **Inertia SSR**, which means a persistent Node process
  (`php artisan inertia:start-ssr`) alongside PHP-FPM. It must run under
  Node 20 (via nvm) and be supervised (pm2, or its own Supervisor program
  block — NOT added to the existing `frappe-bench-*` Supervisor groups)
  so it survives reboots, kept completely separate from ERPNext's
  supervisor config.

## Architecture quick reference

- Laravel 12, Breeze (Vue + Inertia stack), Vue 3 Composition API, Tailwind.
- **SSR is required, not optional** — it's how canonical/meta/JSON-LD end up
  in the raw HTML response for Search Console parity with the old static
  site. `resources/js/ssr.js` is the SSR entry; `npm run build` builds both
  the client and SSR bundles. The SSR Node server must stay running
  (`php artisan inertia:start-ssr`) — if it's down, Inertia silently falls
  back to client-only rendering (page still works for users, but loses the
  SEO benefit, so don't treat a down SSR process as a non-issue).
- Ziggy (`route()` helper) needs its config explicitly passed to the SSR
  app (`props.initialPage.props.ziggy`, shared from
  `HandleInertiaRequests`) — this was a real bug fixed once already; if you
  see `Cannot read properties of undefined (reading '...')` coming from
  `ssr.js`, it's almost certainly a Ziggy-config-not-shared regression.
- Public content routes (`/`, `/tools/`, `/blog/`, `/news/`, and the static
  page slugs) use **trailing-slash URLs** to match the legacy site exactly;
  `ForceTrailingSlash` middleware 301s the non-slash form but is scoped to
  only those legacy prefixes — it must never touch `/admin`, `/login`,
  `/profile`, etc. (already fixed once; re-check this if you add new public
  top-level sections).
- Any component with a `setTimeout`/`watch(..., {immediate:true})` +
  browser-only API (`window`, `fetch`, `navigator.clipboard`) **must** guard
  with `typeof window !== 'undefined'` or only run inside `onMounted()` —
  otherwise it crashes the whole SSR Node process (happened twice already:
  password generator, policy-check panel). `onMounted` itself is already
  SSR-safe (no-op on server) — the danger is specifically scheduled timers
  and eagerly-fired watchers.
- Content policy checker: `App\Services\PolicyChecker\*`, bound via
  `config/policy-checker.php` (`driver` key, default `rule-based`). Add a
  new implementation of `ContentPolicyCheckerInterface` and change the
  driver to introduce an LLM-backed checker later.
- Admin panel lives under `/admin`, protected by the `admin` middleware
  (role `admin` or `editor`) and `admin.role` (role `admin` only, for
  Users/Settings). Public self-registration is removed — accounts are
  created from `/admin/users` only.

## Useful commands

```bash
# Local dev (already the pattern used throughout this project's history)
php artisan serve --port=8000
php artisan inertia:start-ssr     # required for SSR — see above
npm run build                     # rebuild client + SSR bundles; re-run
                                   # inertia:start-ssr after every build,
                                   # it does NOT hot-reload the SSR bundle

# Re-import legacy content if ever needed again
php artisan content:import /path/to/genzeetools/checkout

# Seed defaults (admin user + settings) on a fresh DB
php artisan db:seed
```

## Admin login

Seeded via `database/seeders/AdminUserSeeder.php`. Email defaults to
`admin@genzeelogics.com` (override with `ADMIN_EMAIL`/`ADMIN_PASSWORD` env
vars before seeding). If you don't have the current password, don't guess —
reset it via `php artisan tinker` (`User::where(...)->update(['password' =>
...])`) or ask the user.
