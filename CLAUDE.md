# CLAUDE.md — Context for Claude Code sessions on this repo

This file is read automatically at the start of every session. It exists so
that after `/clear` (or in a brand-new session), Claude does not need to
re-derive project context, server layout, feature set, or the hard safety
rules below. If the user just says "look at this repo" or asks for
improvement suggestions, this file plus the "Full feature reference" and
"Codebase map" sections below should be enough to answer without further
exploration.

## What this project is

**GenzeeCms** is a Laravel 12 + Breeze + Inertia + Vue 3 rewrite of
**genzeelogics.com**, a static site (previously in the `genzeetools` repo)
offering 46 free in-browser calculators, a blog, a news section, and static
pages (about/contact/privacy-policy/disclaimer/terms/editorial). The goal of
the migration was: full feature parity with zero SEO/Search-Console
regression, plus a full admin CMS (WordPress-Pro-style) to manage everything
without touching code or Git.

**Current status: LIVE IN PRODUCTION** at `https://genzeelogics.com`, cut
over from the old static site on 1 Aug 2026. All 10 migration phases below
are complete. The site is currently in the post-cutover Google Search
Console re-indexing window — see `docs/gsc-indexing-checklist.md` for the
manual indexing schedule and current progress.

The migration was done in phases, all complete:

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
   slugs, plus a 410 Gone status for permanently-removed URLs, wired
   centrally via a `NotFoundHttpException` render handler in
   `bootstrap/app.php` so it catches both "no route matched" and "route
   matched but record not found" 404s
9. Full QA pass: all 127 content pages' titles/canonicals compared against
   the original site, all 137 sitemap URLs verified 200, full regression
   pass on public site + admin panel
10. Production deployment/cutover: dedicated nginx server block + PHP-FPM
    pool, Supervisor-managed SSR process (`genzeelogics-ssr`), DNS cutover,
    HTTPS via existing Let's Encrypt cert, Google Search Console sitemap
    resubmission and manual re-indexing in progress

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

### 2. This Laravel app (GenzeeCms) — deploys here, LIVE

- Path: `/opt/apps/LaraCms/`
- Node version: **v20.20.2, via nvm**, selected by a `.nvmrc` file already
  present in that directory
- PHP 8.2.30 is installed
- Nginx 1.18.0 serves this app via a dedicated server block (see
  `deploy/nginx-genzeelogics.conf`) — it also serves other sites, don't
  touch server blocks that aren't this app's
- Dedicated PHP-FPM pool (`deploy/php-fpm-pool.conf`), socket
  `/run/php/genzeelogics.sock`
- SSR runs as its own Supervisor program, `genzeelogics-ssr` (see
  `deploy/supervisor-inertia-ssr.conf.template`) — isolated from the
  `frappe-bench-*` program group
- Domain `genzeelogics.com` is live and pointed at this app, HTTPS via the
  existing Let's Encrypt certificate at
  `/etc/letsencrypt/live/genzeelogics.com/`
- The user's `~/.bashrc` defines a `cd()` function that auto-activates the
  right nvm version via `.nvmrc` whenever you `cd` into a directory that has
  one — so a plain `cd /opt/apps/LaraCms && npm run build` picks up Node 20
  automatically, as long as you're in an interactive shell that sources
  `.bashrc`. Non-interactive SSH commands (`ssh host 'command'`) do **not**
  source `.bashrc` by default — either use `nvm use` explicitly or run
  through a login/interactive shell, and verify with `node -v` before
  relying on it.
- Redeploys: `deploy/deploy.sh` is idempotent and safe to re-run (git
  fetch/reset, composer install, migrate, npm build, cache rebuild, asset
  permissions, PHP-FPM/nginx reload, SSR restart). It self-checks that
  `/usr/bin/node` is still v12.22.9 and that the `frappe-bench-*` Supervisor
  program count is unchanged before/after, aborting rather than risking
  Frappe. See `deploy/README.md` for the full runbook.

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

- Laravel 12, Breeze (Vue + Inertia stack), Vue 3 Composition API, Tailwind
  CSS 3 (public site reuses the legacy site's own hand-written CSS at
  `public/assets/css/style.css` rather than being fully Tailwind-styled —
  the admin panel is fully Tailwind/Breeze).
- SQLite locally (`database/database.sqlite`), MySQL in production
  (`laracms` DB on the server).
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
  only those legacy prefixes (`app/Http/Middleware/ForceTrailingSlash.php`,
  `PUBLIC_PREFIXES` const) — it must never touch `/admin`, `/login`,
  `/profile`, etc. (already fixed once; re-check this if you add new public
  top-level sections).
- Any component with a `setTimeout`/`watch(..., {immediate:true})` +
  browser-only API (`window`, `fetch`, `navigator.clipboard`) **must** guard
  with `typeof window !== 'undefined'` or only run inside `onMounted()` —
  otherwise it crashes the whole SSR Node process (happened three times
  already: password generator, policy-check panel, the 410 Gone page reading
  an Inertia prop that isn't shared outside the normal middleware pipeline).
  `onMounted` itself is already SSR-safe (no-op on server) — the danger is
  specifically scheduled timers, eagerly-fired watchers, and reading
  `usePage().props.*` in code paths that can run before `HandleInertiaRequests`
  has shared its props (e.g. rendering from an exception handler — see the
  410 Gone section below).
- Content policy checker: `App\Services\PolicyChecker\*`, bound via
  `config/policy-checker.php` (`driver` key, default `rule-based`). Add a
  new implementation of `ContentPolicyCheckerInterface` and change the
  driver to introduce an LLM-backed checker later.
- Admin panel lives under `/admin`, protected by the `admin` middleware
  (role `admin` or `editor`) and `admin.role` (role `admin` only, for
  Users/Settings). Public self-registration is removed — accounts are
  created from `/admin/users` only.

## Full feature reference — Public website (genzeelogics.com)

- **Homepage (`/`)** — `Site\HomeController` → `Public/Home.vue`. Featured
  tools/posts, site intro.
- **Tools directory (`/tools/`)** and **tool page (`/tools/{slug}/`)** —
  `Site\ToolController` → `Public/Tools/Index.vue` / `Show.vue`. Each tool
  page renders: the interactive calculator (via `CalculatorMount.vue`,
  resolving `Tool.component` through `Components/Calculators/registry.js`),
  a rich-text guide/article below it (`Tool.guide_content`), an FAQ block
  (`ToolFaq` rows → also emitted as FAQPage JSON-LD), and a related-tools
  strip (self-referencing `tool_related` pivot).
- **46 calculators**, one Vue component each under
  `resources/js/Components/Calculators/` — finance/tax (loan, compound/
  simple interest, salary tax for UK/UAE/KSA/India/US/Canada/Pakistan, VAT,
  sales tax, zakat, crypto profit, BNPL cost, student loan, savings goal,
  margin, discount, tip, percentage), conversion/utility (unit, currency,
  salary, land area, gold weight, lakh-crore, number-to-words, date
  difference, due date, GPA, BMI, calorie, ideal weight), text/dev tools
  (word counter, case converter, Base64 encoder, CSV→JSON, QR code
  generator, password generator, invoice generator), and image tools
  (compressor, format converter, image→PDF). A tool whose `component` field
  has no match in `registry.js` falls back to the `ComingSoon` placeholder
  instead of breaking the page — this is the mechanism for shipping a new
  Tool DB row before its calculator UI is ready.
- **Blog (`/blog/`, `/blog/{slug}/`)** and **News (`/news/`,
  `/news/{slug}/`)** — `Site\PostController`, both backed by the single
  `posts` table (`type` column). Blog and news share the same admin editor,
  policy checker, and JSON-LD (Article/NewsArticle) logic.
- **Static pages** (`/about/`, `/contact/`, `/privacy-policy/`,
  `/disclaimer/`, `/terms/`, `/editorial/`) — `Site\PageController` →
  `Public/StaticPage.vue`, backed by the `pages` table. This set is fixed
  (route `whereIn` constraint) to match the legacy URL structure; there is
  no admin UI to add/remove a static page, only to edit the six that exist.
- **SEO endpoints** — `Site\SeoController`: `/sitemap.xml` (dynamic, all
  published tools/posts/pages + core index pages), `/robots.txt`
  (disallows `/admin/`, points at the sitemap), `/ads.txt` (content driven
  by the `ads_txt_content` setting).
- **Per-page SEO** — `Components/Public/SeoHead.vue` renders title (with
  the site's `meta_title_suffix`), meta description, canonical URL, OG
  tags, and (per content type) JSON-LD. Every public page must use this
  component, not a bare Inertia `<Head>`, both for SEO consistency and
  because it's what pulls in the legacy `style.css` stylesheet.
- **410 Gone handling** — for retired URLs that should never come back
  (as opposed to a 301/302 redirect to a replacement). See "Redirects & 410
  Gone" below.
- **Google Analytics** — injected site-wide from the `google_analytics_id`
  setting, guarded for SSR (browser-only, runs client-side only).

## Full feature reference — Admin panel (`/admin`)

All admin pages reuse Breeze's `AuthenticatedLayout.vue` + Tailwind
components — there is deliberately no separate bespoke admin theme.
Every mutating admin action is recorded to `ActivityLog` via
`ActivityLog::record($action, $description, $subject)`.

- **Dashboard** (`Admin\DashboardController` → `Admin/Dashboard.vue`) —
  counts (tools, published tools, blog posts, news posts, drafts,
  scheduled, users), 10 most recent activity log entries, 5 most recently
  updated posts.
- **Tools CRUD** (`Admin\ToolController`, resource except `show`) — fields:
  category, slug, title, icon, `component` (the Vue component name to
  render — must match a key in `registry.js` or the tool falls back to
  ComingSoon), short description, guide content (TipTap rich text), a
  comma-separated keywords field (stored as JSON array), meta title/
  description, OG image, status (draft/published — publishing stamps
  `published_at`), display order, an inline repeatable FAQ editor
  (question/answer pairs, replaces all FAQs on every save), and a related-
  tools multi-select (self-referencing pivot with order).
- **Posts CRUD** (`Admin\PostController`, resource except `show`, shared by
  blog and news) — fields: type (blog/news), category, slug (unique per
  type, not globally), title, excerpt, body (TipTap), featured image, meta
  title/description, canonical override, OG image, status (draft/
  scheduled/published — scheduled requires `published_at`, publishing with
  no date stamps now), tags (free-text, `firstOrCreate`-synced against the
  `tags` table by slug). The **AdSense policy checker** (see below) runs
  live in this form.
- **Pages** (`Admin\PageController`, edit-only — index + edit + update, no
  create/delete since the six static pages are fixed) — title, body
  (TipTap), meta title/description.
- **Categories** (`Admin\CategoryController`) — shared across tool/blog/
  news via a `type` column; name, tagline, description; slug and `order`
  auto-derived. Simple modal-style CRUD from one index page
  (`Admin/Categories/Index.vue`), no separate form page.
- **Media library** (`Admin\MediaController`) — upload (images: jpg/jpeg/
  png/gif/webp/svg; video: mp4/webm/mov; 20MB max), stored under
  `storage/app/public/media/{Y}/{m}` via the `public` disk, with alt text.
  Used both as a standalone library (`/admin/media`) and as an image picker
  inside TipTap editors.
- **Users** (`Admin\UserController`, `admin` role only) — name, email,
  role (`admin`|`editor`), active flag, password. Self-deletion is blocked.
  `EnsureAdminAccess` middleware lets both `admin` and `editor` into
  `/admin`; `EnsureAdminRole` further restricts Users/Settings to `admin`
  only. There is no public registration — accounts exist only via this
  screen.
- **Settings** (`Admin\SettingController`, `admin` role only) — single-page
  editor for every `Setting` key, grouped: identity (site name/tagline/
  URL, logo/favicon upload), SEO (meta title suffix, default meta
  description, default OG image), analytics (Google Analytics ID, Search
  Console verification string), ads (AdSense publisher ID, raw `ads.txt`
  content), contact email. `Setting::get()`/`Setting::set()` cache
  individual keys forever (`Cache::rememberForever`), invalidated on write
  — **if a setting looks stale after a raw DB edit, clear the
  `setting.{key}` cache entry**, the UI update path already does this
  correctly.
- **Redirects** (`Admin\RedirectController`) — see "Redirects & 410 Gone"
  below.
- **Activity log** (`Admin\ActivityController`, read-only) — full history
  of every create/update/delete/upload across the panel.
- **AdSense policy checker** (`Admin\PolicyCheckController`, JSON endpoint
  used by the Posts form) — see below.

## Redirects & 410 Gone

`Redirect` model (`redirects` table: `from_path`, `to_path` nullable,
`status_code`), managed at `/admin/redirects`
(`resources/js/Pages/Admin/Redirects/Index.vue`):

- **301/302** — normal redirect, `to_path` required.
- **410 Gone** — for URLs that are permanently retired and should be
  actively dropped from Google's index rather than redirected anywhere;
  `to_path` is null.

Applied centrally, not per-route: a `NotFoundHttpException` render handler
in `bootstrap/app.php` looks up `Redirect::where('from_path', $path)` for
every 404 (this covers both "no route matched" and "route matched but the
underlying record wasn't found", e.g. a deleted `/tools/{slug}/`). A 410
match renders `Public/Gone.vue` with a 410 status; anything else redirects.
**Gotcha already hit once:** this handler runs outside the normal
Inertia middleware pipeline, so `HandleInertiaRequests::share()` never ran
and shared props like `site` are undefined — the 410 branch manually calls
`Inertia::share(app(HandleInertiaRequests::class)->share($request))` before
rendering. If you add another exception-handler-rendered Inertia page,
you need the same manual share call or it will crash the SSR process the
same way (`Cannot read properties of undefined (reading 'url')` was the
exact error).

## AdSense content policy checker

`App\Services\PolicyChecker\RuleBasedPolicyChecker` (bound via
`ContentPolicyCheckerInterface`, driver selected by
`config('policy-checker.driver')`, default `rule-based`) runs a live,
debounced check as the editor types in the Posts form (`/admin/posts/create`
and edit), POSTing to `/admin/policy-check`. Checks performed, each pass/
warn/fail:

- **Prohibited content** — keyword screen for adult/violence/weapons/
  drugs/hacking/counterfeit/unlicensed-gambling content (short,
  high-precision list, `fail` if matched).
- **Placeholder text** — "lorem ipsum", "TODO:", "TBD", etc. left in.
- **Content length** — `fail` under 150 words, `warn` under 400.
- **Title quality** — empty, ALL CAPS, repeated punctuation (`!!!`, `??`),
  or over ~70 chars.
- **Meta description** — missing or over 160 chars.
- **Featured image** — missing.
- **Categorisation** — no category and no tags.
- **Heading structure** — long post (300+ words) with no H2/H3.
- **Repetition/originality** — a sentence repeated 3+ times making up over
  20% of all sentences.
- **Summary/excerpt** — news posts specifically, missing excerpt.

Score starts at 100, `-30` per fail, `-8` per warn, clamped 0-100. Status:
`not_approvable` if any fail, else `approvable` (score ≥ 85) or
`needs_work`. The UI must always make clear this is an automated heuristic
screen, not a Google decision — it does not gate publishing.

To swap in an LLM-backed checker later: implement
`ContentPolicyCheckerInterface::check(PolicyCheckRequest): PolicyCheckResult`
in a new class, bind it, and set `POLICY_CHECKER_DRIVER` — no controller or
Vue changes needed.

## Data model reference

| Model | Table | Key fields | Relationships |
|---|---|---|---|
| `Tool` | `tools` | slug, title, icon, `component`, short_description, guide_content (rich text), keywords (json), meta_title/description, og_image, status, order, published_at | `belongsTo Category`, `hasMany ToolFaq` (ordered), `belongsToMany Tool` (self, via `tool_related`, ordered) |
| `ToolFaq` | `tool_faqs` | question, answer, order | `belongsTo Tool` |
| `Post` | `posts` | `type` (blog\|news), slug (unique per type), title, excerpt, body, featured_image, meta_title/description, canonical_override, og_image, status (draft\|scheduled\|published), published_at, views, adsense_score, adsense_issues (json) | `belongsTo Category`, `belongsTo User` (author), `belongsToMany Tag` |
| `Page` | `pages` | slug (fixed set of 6), title, body, meta_title/description | none |
| `Category` | `categories` | `type` (tool\|blog\|news), name, slug, tagline, description, order — unique on `(type, slug)` | `hasMany Tool`/`Post` (by type) |
| `Tag` | `tags` + `taggables`/`post_tag` | name, slug | `belongsToMany Post` |
| `Media` | `media` | disk, path, type (image\|video), mime_type, size, alt_text, uploaded_by | `belongsTo User` (uploader) |
| `Setting` | `settings` | `key` (string PK), value, group — accessed via `Setting::get()`/`::set()`/`::group()`, cached forever per key | none |
| `User` | `users` | name, email, password, `role` (admin\|editor), is_active | `hasMany Post` (as author), `hasMany ActivityLog` |
| `ActivityLog` | `activity_logs` | user_id, action, subject_type/id (polymorphic-ish, not a formal morph), description | `belongsTo User` |
| `Redirect` | `redirects` | from_path (unique), to_path (nullable), status_code (301\|302\|410) | none |

Note: `taggables` (polymorphic tag pivot from an earlier design) and
`post_tag` (the one actually used by `Post::tags()`) both exist — `Tag`
belongs-to-many is wired through `post_tag`. Don't add new taggable models
via `taggables` without checking which pivot the code path actually uses.

## Codebase map

```
app/Console/Commands/ImportLegacyContent.php   Legacy content importer (php artisan content:import {path})
app/Support/HtmlPageParser.php                 DOM-based scraper used by the importer
app/Http/Controllers/Site/*                    Public controllers: Home, Tools, Post (blog+news), Page, Seo
app/Http/Controllers/Admin/*                   Admin controllers: Dashboard, Tools, Posts, Pages, Categories,
                                                Media, Users, Settings, Redirects, Activity, PolicyCheck
app/Http/Middleware/ForceTrailingSlash.php     301s legacy public prefixes to trailing-slash form
app/Http/Middleware/HandleInertiaRequests.php  Shares auth/ziggy/site props on every Inertia request
app/Http/Middleware/EnsureAdminAccess.php      Gate: role admin|editor, is_active, for all of /admin
app/Http/Middleware/EnsureAdminRole.php        Gate: role admin only, for Users/Settings
app/Services/PolicyChecker/*                   AdSense content-policy checker (rule-based; LLM-swappable)
app/Models/*                                   Tool, ToolFaq, Post, Page, Category, Tag, Media, Setting,
                                                User (+role), ActivityLog, Redirect
bootstrap/app.php                              Route/middleware registration + the 410/redirect exception handler
routes/web.php                                 Public routes (all trailing-slash legacy content routes)
routes/admin.php                               Admin routes, prefix `admin`, middleware auth+verified+admin
resources/js/Pages/Public/*                    Home, Tools/{Index,Show}, Blog/{Index,Show}, News/{Index,Show},
                                                StaticPage, Gone (410)
resources/js/Pages/Admin/*                     Dashboard, Tools, Posts, Pages, Categories, Media, Users,
                                                Settings, Redirects, Activity
resources/js/Components/Calculators/*.vue      All 46 calculators, one component each
resources/js/Components/Calculators/registry.js  Maps Tool.component -> Vue component (lazy import)
resources/js/Components/Public/CalculatorMount.vue  Resolves + mounts the right calculator, ComingSoon fallback
resources/js/Components/Public/SeoHead.vue     Title/meta/canonical/OG/JSON-LD — use on every public page
resources/js/Components/Public/ToolTile.vue    Tool card used in tools index / related-tools strips
resources/js/Layouts/PublicLayout.vue          Public chrome, reuses legacy public/assets/css/style.css
resources/js/Layouts/AuthenticatedLayout.vue   Breeze layout + admin nav — admin panel deliberately reuses this
resources/js/ssr.js                            SSR entry point (built by `npm run build`, alongside client bundle)
config/policy-checker.php                      `driver` key for the content policy checker binding
deploy/*                                       deploy.sh + nginx/php-fpm/supervisor configs + runbook (see below)
docs/gsc-indexing-checklist.md                 Post-cutover manual Search Console indexing checklist/tracker
```

## Patterns for adding new features

**New calculator/tool:**
1. Create a `Tool` row via `/admin/tools/create` (or a migration/seeder) —
   set `component` to a PascalCase name, e.g. `MortgageCalculator`.
2. Build `resources/js/Components/Calculators/MortgageCalculator.vue`. Any
   browser-only API must be `onMounted()`-gated (SSR safety, see above).
3. Add `MortgageCalculator: () => import('./MortgageCalculator.vue'),` to
   `registry.js`.
4. Until step 2-3 are done, the Tool page renders fine with the
   `ComingSoon` placeholder — so the DB row can ship ahead of the UI.
5. Add the tool to `docs/gsc-indexing-checklist.md` and request indexing
   once published, if SEO parity/visibility matters for it.

**New admin-managed content type** (following the existing Tools/Posts
pattern): migration + model with `fillable`/casts, a resource controller
under `app/Http/Controllers/Admin/` calling `ActivityLog::record(...)` on
every mutation, `Route::resource(...)` (or explicit routes, see
`routes/admin.php`) inside the `admin` (or `admin.role`) middleware group,
and `Index.vue`/`Form.vue` under `resources/js/Pages/Admin/{Name}/` reusing
`AuthenticatedLayout`.

**New Setting:** add a default in `database/seeders/SettingsSeeder.php`
(runs via `firstOrCreate`, safe to re-run), add the field to the validation
array + `groupFor()` in `Admin\SettingController`, add the input to
`Admin/Settings/Edit.vue`, and if it needs to reach the public site, add it
to the `site` array in `HandleInertiaRequests::share()`.

**New public top-level URL section** (like tools/blog/news): add routes in
`routes/web.php`, and if it should use trailing-slash legacy-style URLs,
add its prefix to `ForceTrailingSlash::PUBLIC_PREFIXES` — otherwise it's
safe to leave off and it'll behave like a normal Laravel route (no
trailing slash, no admin-style auth).

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

## When asked for improvement suggestions

The user regularly asks for recommendations on both the public site and
the admin panel. Base those on the actual current state (this file +
reading the relevant controllers/components), not on generic CMS advice —
e.g. check what's already covered above (SSR safety, the policy checker,
the redirect/410 system, the Setting cache) before proposing something
that already exists in a different form. Flag both quick wins (a missing
validation, an N+1 query, a UX gap in a specific admin form) and larger
architectural options (e.g. the LLM-backed policy checker slot, image
optimization/CDN, full-text search on posts/tools), and be explicit about
trade-offs and effort, since this is a solo-maintained production site
sharing a server with unrelated production infrastructure (see "Production
server" above) — prefer low-risk, incremental changes over rearchitecting.
