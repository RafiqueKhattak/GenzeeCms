# GenzeeCms

Laravel 12 + Breeze + Inertia + Vue 3 CMS powering **[genzeelogics.com](https://genzeelogics.com)**
— 46 free in-browser calculators, a blog, a news section, and a full admin
panel to manage all of it. This project replaces the site's previous static
HTML + Node/MySQL CMS setup (formerly the `genzeetools` repo) with zero loss
of content and zero SEO/Search-Console regression.

> **Working on this repo with Claude Code?** Read [`CLAUDE.md`](./CLAUDE.md)
> first — it has the git-identity rule, production server layout, and
> Node-version isolation rules that are load-bearing for this project.

## Stack

- **Laravel 12**, PHP 8.2+
- **Breeze** (Vue + Inertia starter kit)
- **Vue 3** (Composition API), **Inertia.js**, **Tailwind CSS**
- **Server-Side Rendering (SSR)** — required, not optional (see below)
- **TipTap** rich text editor in the admin panel
- SQLite for local dev (`database/database.sqlite`), swap `DB_CONNECTION`
  for MySQL/Postgres in production via `.env`

## Why SSR matters here

The legacy site was fully static HTML, and its URLs are already indexed by
Google with specific titles, canonicals, and JSON-LD. To keep that parity,
every public page must contain fully-rendered content (title, meta,
canonical, structured data) in the **raw HTML response**, not just after
client-side JavaScript runs. That's what SSR gives us. Practically, this
means:

- `resources/js/ssr.js` is a real, required entry point — `npm run build`
  builds both the client bundle (`public/build`) and the SSR bundle
  (`bootstrap/ssr`).
- A persistent Node process must run **`php artisan inertia:start-ssr`**
  alongside PHP-FPM in production. If it's down, Inertia silently falls back
  to client-only rendering — the site still works for visitors, but loses
  the SEO benefit this whole setup exists for. Treat it as a required
  service, not an optional optimization.
- After every `npm run build`, the running `inertia:start-ssr` process must
  be restarted — it does not hot-reload the new bundle.
- Any Vue component using a browser-only API (`window`, `fetch`,
  `navigator.clipboard`, timers) must guard it (`typeof window !==
  'undefined'`, or only run it inside `onMounted()`), or it will crash the
  SSR Node process. See `CLAUDE.md` for two real incidents this happened.

## Project structure (the parts that aren't stock Laravel/Breeze)

```
app/Console/Commands/ImportLegacyContent.php   Import tool from the legacy static site (see below)
app/Support/HtmlPageParser.php                 Tiny DOM-based scraper used by the importer
app/Http/Controllers/Site/*                    Public-facing controllers (Home/Tools/Post/Page/Seo)
app/Http/Controllers/Admin/*                   Admin panel controllers
app/Services/PolicyChecker/*                   AdSense content-policy checker (rule-based; LLM-swappable)
app/Models/*                                   Tool, Post, Category, Tag, Page, Media, Setting,
                                                User (+role), ActivityLog, Redirect
resources/js/Pages/Public/*                    Public site pages (Home, Tools, Blog, News, StaticPage)
resources/js/Pages/Admin/*                     Admin panel pages
resources/js/Components/Calculators/*          All 46 calculators, one Vue component each,
                                                resolved at runtime via registry.js + CalculatorMount.vue
resources/js/Components/Public/*               SeoHead, ToolTile, CalculatorMount
resources/js/Layouts/PublicLayout.vue          Public site chrome (header/nav/footer) — reuses the
                                                legacy site's own CSS (public/assets/css/style.css)
resources/js/Layouts/AuthenticatedLayout.vue   Breeze's layout, extended with admin nav — the admin
                                                panel deliberately reuses Breeze/Tailwind components
                                                rather than a bespoke admin theme
routes/web.php / routes/admin.php              Public routes / admin routes (prefix `admin`, role-gated)
```

## Content model

- **Tool**: one per calculator (`/tools/{slug}/`). Has FAQs (`ToolFaq`,
  hasMany), related tools (self-referencing `belongsToMany` via
  `tool_related`), a `component` field naming the Vue component that
  renders its interactive calculator (see `Components/Calculators/registry.js`).
- **Post**: blog and news share one table (`type` column: `blog`|`news`),
  `/blog/{slug}/` and `/news/{slug}/`. Has tags (`belongsToMany`), a category,
  scheduling (`status`: draft/scheduled/published + `published_at`).
- **Page**: the fixed set of static pages (about/contact/privacy-policy/
  disclaimer/terms/editorial) — editable body, no create/delete (the set is
  fixed to match the legacy site's URL structure).
- **Setting**: key/value store (`Setting::get('key', $default)` /
  `Setting::set(...)`) for site identity, SEO defaults, Analytics, AdSense —
  all editable from `/admin/settings`.
- **Redirect**: `from_path` → `to_path` (301/302), managed at
  `/admin/redirects`, applied centrally via a `NotFoundHttpException`
  render handler in `bootstrap/app.php`.

## Re-importing legacy content

If the legacy `genzeetools` static site checkout is ever available again
(e.g. to recover something missed), the importer is idempotent
(`updateOrCreate` throughout) and safe to re-run:

```bash
php artisan content:import /path/to/genzeetools/checkout
```

It reads `data/tools.json`, `data/articles.json`, `data/news.json` for
structured metadata, and scrapes each corresponding static HTML file for
meta title/description/canonical/OG image and JSON-LD (FAQPage → `ToolFaq`
rows, Article/NewsArticle → post body/dates).

## Local development

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed              # creates the admin user + default settings
php artisan storage:link         # needed for media library uploads

npm run build                    # or `npm run dev` while iterating on the client bundle only —
                                  # SSR still needs a full `npm run build` + SSR restart to pick up changes
php artisan serve --port=8000
php artisan inertia:start-ssr    # separate terminal/process — required, see "Why SSR matters" above
```

Default admin login is seeded by `database/seeders/AdminUserSeeder.php`
(`admin@genzeelogics.com` by default — override via `ADMIN_EMAIL`/
`ADMIN_PASSWORD` env vars before seeding; the seeder prints a generated
password once if you don't set one, and it is not recoverable after that —
reset via `php artisan tinker` if lost).

## Calculators

Each of the 46 tools has its own Vue component under
`resources/js/Components/Calculators/`, ported from the legacy site's
vanilla-JS files and verified against the original worked examples (loan
EMI, crypto ROI, salary tax slabs, etc.). `CalculatorMount.vue` resolves the
right component at runtime from the `Tool.component` DB field via
`registry.js`; a tool with no matching entry falls back to a `ComingSoon`
placeholder rather than breaking the page.

## AdSense content policy checker

`/admin/posts/create` (and edit) shows a live, debounced score as the editor
types, checking against Google's publisher content policies (prohibited
content, thin content, placeholder text, missing meta/image/tags, clickbait
titles, duplicated sentences). It's rule-based today
(`App\Services\PolicyChecker\RuleBasedPolicyChecker`), bound via
`config('policy-checker.driver')` behind `ContentPolicyCheckerInterface` —
a future LLM-backed checker can implement the same interface and be
switched in without touching the controller or Vue panel. The UI always
makes clear this is an automated screen, not a Google decision.

## Deployment

See `CLAUDE.md` for the production server layout — this app shares a server
with an unrelated Frappe ERPNext installation on Node 12, and deployment
must never touch that app's Node version, Supervisor config, or files.
