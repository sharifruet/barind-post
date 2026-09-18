# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Barind Post is a Bengali (Bangla) news portal built on **CodeIgniter 4** (PHP 8.1+). It has a public-facing site and an admin CMS in the same codebase, sharing one MySQL database. Default locale is `bn` (Bengali), timezone `Asia/Dhaka`.

## Commands

```bash
composer install                      # install PHP dependencies
php spark serve                       # run dev server (or use Docker, below)
php spark migrate                     # run pending migrations
php spark db:seed RoleSeeder          # seed roles
composer test                         # run phpunit test suite
vendor/bin/phpunit tests/unit/HealthTest.php   # run a single test file
vendor/bin/phpunit --filter testName           # run a single test method
docker compose exec app vendor/bin/phpunit --no-coverage   # same, inside the PHP 8.1 container
```

Tests live in `tests/unit` (helpers) and `tests/feature` (HTTP via `FeatureTestTrait`). They run
in `ENVIRONMENT=testing`, where the DB group is SQLite in memory with no schema, so tests must be
**database-free** unless tagged `@group needs-db` (excluded in CI). The automation API key for
tests is set in `phpunit.xml.dist` (`automation.apiKey = phpunit-test-key`). GitHub Actions
(`.github/workflows/ci.yml`) runs `composer audit`, `php -l` over `app/` and `phpunit --exclude-group needs-db` on
every push/PR. `composer.json` → `config.audit.ignore` lists six codeigniter4/framework advisories (fixed in 4.7.4,
which needs PHP ≥ 8.2 — Docker and the documented server requirement are 8.1) with the reason each is not exploitable
here; delete that block when the framework is upgraded. Any *new* advisory still fails CI.

### Docker

```bash
docker-compose up -d
```
Runs the app on `http://localhost` (Apache) and MySQL 8.0 on host port `3307`. The `db` service auto-imports `dbscript.sql` on first boot via `docker-entrypoint-initdb.d`.

## Database: one source of truth (since 2026-09-18)

**Migrations are the schema.** `app/Database/Migrations/2026-09-18-000000_baseline_schema.php` applies `app/Database/Schema/baseline.sql` (the complete schema as of that date, all `CREATE TABLE IF NOT EXISTS`, MySQL 8 / utf8mb4) and every later change is a new migration file. Sample content lives in `app/Database/Schema/sample_data.sql`, loaded by `SampleDataSeeder` (refuses to run on a database that already has news/users).

**`dbscript.sql` is generated, never edited.** Fresh installs still import it (Docker `docker-entrypoint-initdb.d`, cPanel phpMyAdmin), so after any migration run `php spark schema:dump` to regenerate it from the migrated database (`SHOW CREATE TABLE` per table + the sample data). Workflow for a schema change:

```bash
php spark make:migration AddFooToNews      # write forge/SQL in up()/down()
php spark migrate                          # apply locally (Docker: docker compose exec app php spark migrate)
php spark schema:dump                      # regenerate dbscript.sql — commit both
```
Then add the equivalent SQL to `DATABASE_UPDATES.md` for production, which is updated by hand through phpMyAdmin unless SSH is available (`php spark migrate` there). `tests/unit/SchemaBaselineTest.php` fails if code references a table the baseline doesn't define.

History worth knowing: before this, `dbscript.sql` was hand-maintained alongside partial migrations that had never run anywhere (two of them were unparsable PHP), and it once contained a MariaDB-only `ALTER ... ADD COLUMN IF NOT EXISTS` that made MySQL's CLI import stop halfway — "tables missing after a fresh install" was the only symptom. The old script also `DROP`ped `images`/`contacts` before recreating them; the baseline is non-destructive. Existing databases (local Docker, production) were all created from the old script; running `php spark migrate` on them is a no-op that just records the baseline, after which incremental migrations apply normally.

Key tables: `users`, `roles`, `news`, `categories`, `tags`, `news_tags`, `images`, `reporter_roles`, `user_reporter_roles`, plus a sports-events domain (`sports_events`, `sports_participants`, `sports_matches`, `sports_event_entries`, `sports_match_events`, `sports_venues`) and `prayer_times`/`cities` for the prayer-times feature. Charset is `utf8mb4` / `utf8mb4_unicode_ci` throughout — required for Bengali text.

## Authentication & authorization

No auth library/Shield — auth is hand-rolled session state, enforced by filters:

- `App\Filters\AdminAuthFilter` (alias `adminauth`, applied to `admin` and `admin/*` in `app/Config/Filters.php`) requires `session('logged_in')` and a role in `['admin', 'editor', 'sub-editor', 'reporter']`, else redirects to `/login` (JSON 401 for AJAX callers).
- `App\Filters\RoleFilter` (alias `role`) restricts individual routes: `['filter' => 'role:admin']` or `'role:admin,editor,sub-editor'` in `Routes.php` — used for users/roles/reporter-roles/sports-events/incoming (no reporters), and photo cards, featured/breaking toggles and logs (admin only). Denied: flash + redirect to `/admin`, or JSON 403 for AJAX. **Add the `role:` option to any new admin-only route; the controllers no longer check roles for these areas.**
- `App\Filters\ApiKeyFilter` guards the `api/v1` automation routes (Bearer token).
- `BaseAdminController::initController` keeps a defensive copy of the login check but redirects via `RedirectException` (never `exit`, which would kill the test runner).
- `Auth::attemptLogin` (`app/Controllers/Auth.php`) verifies against `UserModel` and sets `session()->set(['user_id', 'user_name', 'user_role', 'logged_in'])`. It is throttled with CI4's `Throttler` (5 attempts/min per IP+email, 30/min per IP); the buckets live in `writable/throttle/`, deliberately outside `writable/cache/`, because `purge_public_cache()` empties the cache dir on every news change and must not reset login counters.
- `BaseAdminController::initController` (`app/Controllers/BaseAdminController.php`) is the gate for the whole admin area: any controller extending it requires `session('logged_in')` and `session('user_role')` in `['admin', 'editor', 'sub-editor', 'reporter']`, else redirects to `/login`.
- **Global filters** (`app/Config/Filters.php`): `forcehttps` (acts only when `Config\App::$forceGlobalSecureRequests` is true — App.php's constructor sets it for `ENVIRONMENT === 'production'` with an `https://` base URL), `honeypot` (injects a hidden field into every POST form and rejects submissions that fill it — keep `name="honeypot"` free), `csrf`, and `secureheaders` after (X-Frame-Options SAMEORIGIN, nosniff, Referrer-Policy same-origin…). `CI_ENVIRONMENT` is `development` in Docker (`docker-compose.yml`) and must be `production` in the server `.env`.
- **CSRF is on globally** (`app/Config/Filters.php`, cookie-based, one token per session — `Security::$regenerate = false` on purpose, because admin pages fire many AJAX POSTs from one page load). Every POST `<form>` must contain `<?= csrf_field() ?>`; AJAX needs no per-call change because both layouts put the token in `<meta name="csrf-token">` and install a small `fetch`/`XMLHttpRequest` shim that adds the `X-CSRF-TOKEN` header to same-origin non-GET requests. Excluded: `api/v1/*` (Bearer auth) and `news/view/*` (`sendBeacon` can't send headers). A new POST endpoint called from a `<form>` without the field, or from JS outside those layouts, will get a 403.
- What remains inline in controllers is business logic, not access control: reporters can only save drafts and only edit/delete their own articles (`AdminNews`), and `AdminIncoming`/`AdminPhotoCards::generatePhotoCard` keep a JSON-friendly role check as belt and braces.

## Two document roots (Docker vs production)

The same repo is served two different ways, and this has already broken production once:

| | document root | `FCPATH` | front controller |
|---|---|---|---|
| Docker | `<repo>/public` | `<repo>/public/` | `public/index.php` |
| cPanel (live) | `<repo>` | `<repo>/` | `index.php` at the repo root (tracked, with `require FCPATH . 'app/Config/Paths.php'`) |

So a file shipped in `public/` is `/<path>` locally but `/public/<path>` on the live site — which is
why `public/uploads/...` appears in stored image paths and in `.htaccess` rewrites. **Never hardcode
either shape**: use `asset_url('assets/css/theme.css')` (`app/Helpers/slug_helper.php`), which checks
`FCPATH.$path` then `FCPATH.'public/'.$path` and appends `?v=<mtime>`. `tests/unit/AssetUrlTest.php`
covers both layouts and asserts the shipped stylesheets/icons exist.

## Routing & controllers

All routes are declared explicitly in `app/Config/Routes.php` (no auto-routing) — grouped by feature area. Admin controllers are one per feature, all extending `BaseAdminController`: `Admin` (dashboard, logs), `AdminNews`, `AdminCategories`, `AdminTags`, `AdminKickers`, `AdminUsers` (users + roles), `AdminReporterRoles`, `AdminContacts`, `AdminPhotoCards`, `AdminPrayerTimes`, `AdminIncoming` (automation drafts), `AdminSportsEvents`; plus image handling (`ImageUpload.php`), prayer times (`PrayerTimes.php`), the automation API (`Api/NewsController.php`) and the public site (`PublicSite.php`). When adding an admin feature: add routes under `// Admin ...` in `Routes.php` (with a `role:` filter if not every newsroom role may use it), extend `BaseAdminController`, and add a sidebar link in `app/Views/admin/sidebar.php`.

Views are split into `app/Views/admin/*` and `app/Views/public/*`, each with their own `layout.php`/`header.php`/`footer.php`. Public-facing text is Bengali; the admin UI is English.

## Feature docs

Several root-level markdown files document specific features in more depth than inline comments — consult them before modifying that area rather than re-deriving the design from scratch: `REPORTER_ROLES_IMPLEMENTATION.md`, `IMAGE_UPLOAD_FEATURES.md`, `PHOTO_CARD_FEATURE.md`, `LANGUAGE_SETTINGS.md`, `SEO_CONFIGURATION.md`, `DATABASE_UPDATES.md`, `DEPLOYMENT.md` (cPanel deployment steps).

## Automation API (n8n)

`app/Controllers/Api/NewsController.php` exposes a token-authenticated `api/v1` route
group (`ApiKeyFilter`, config in `app/Config/Automation.php`, key/author-id set via
`automation.apiKey`/`automation.authorId` in `.env`) for an external n8n instance to
create draft news articles — separate from the session-based `/admin` auth. It always
creates `status: draft` (never publishes) and dedupes on `source_url`/`content_hash`.
See `N8N_NEWS_AUTOMATION_PLAN.md` for the full design, current status, and open
decisions; `docker-compose.n8n.yml` runs n8n itself as a separate local stack.

## Notable non-standard bits

- Image uploads for news go through `ImageUpload::upload` (AJAX, stores metadata in the `images` table) rather than CI4's native upload flow embedded in the news form; images are reusable across articles and selected via a modal (see `IMAGE_UPLOAD_FEATURES.md`).
- Photo Card Generation (`Admin::generatePhotoCard`) uses PHP's GD extension to composite social-share images server-side — if editing it, GD must be enabled.
- `app/Libraries/StandingCalculator.php` computes sports standings (`football_group`, `cricket_points` rules) from match data on the fly rather than storing precomputed standings.
- Sports events, prayer times, and reporter-roles are each fairly self-contained vertical features (own models, own controller(s), own view subfolder) — safe to reason about in isolation from the core news/admin flow.
- The news editor is the CKEditor 5 *classic* CDN build (no General HTML Support), so any custom element/class in article HTML is silently stripped the next time the article is saved. The "Link a story" feature therefore stores a related-story reference as a plain `<blockquote><p><strong>আরও পড়ুন:</strong> <a href="/news/{slug}">…</a></p></blockquote>` (JS in `news_form.php`, results from `Admin::newsSearch` at `/admin/news/search`), and `render_article_body()` in `app/Helpers/text_helper.php` turns that exact shape into `<aside class="related-inline">` on the public article page. Keep the JS markup and the helper's regex in sync if either changes.
- **Public pages are cached** (`cachePage(60)` in `PublicSite::home/section/news/tag`). `NewsModel` purges the cache after every insert/update/delete via `purge_public_cache()` (`app/Helpers/cache_helper.php`); anything that changes `news` through the query builder instead of the model must call it explicitly. View counting is a JS beacon (`POST /news/view/{id}`) for the same reason — don't count views inside a cached action.
- **The pipeline may publish, but only if the server allows it**: `automation.autoPublish` (off by default) plus every gate in `Api\NewsController::publishGateFailures()` — minimum word count, not a suspected duplicate, has a subtitle or key points, has a `source_url`. A failed gate files the article as a draft and returns the reasons in `publish_gates`; it is never an error. Adding a gate means adding a case to `tests/unit/PublishGateTest.php`.
- **Automation run reports**: n8n posts one row per run/crash to `POST /api/v1/automation/runs` (`Api\AutomationController`, table `automation_runs`); the latest is the "Last collection run" banner on `/admin/incoming`. Cross-outlet duplicates are flagged at intake (`NewsModel::findSimilar` + `App\Libraries\TitleSimilarity` on `source_title`/`title`) into `news.possible_duplicate_of` — a badge in the queue, never an automatic merge. Photo-less articles get a generated `og:image` from `/og/{id}.png` (`App\Libraries\PhotoCard`, the same renderer as the admin Photo Card tool, cached in `writable/og/`).
- **Automation drafts are triaged in `/admin/incoming`** (`AdminIncoming`): every draft with a `source_url`. Discard = `status archived` (never delete: `source_url` must survive for dedup). `news.suggested_image_url` is a source-page image *suggestion* shown only there; "Use image" copies it into `public/uploads/news/` + `images` like a manual upload. The pipelines must never set `image_url` themselves.
- **Stories without a photo are text-first cards, never placeholders.** Every automation draft arrives image-less (74/74 at the time of writing), so a dummy image would repeat down the whole page. `news_card_widget.php` adds `story--text-first` when there is no image (sizes lead/feature/medium only) and renders up to 2–4 key points, which appear *nowhere else* — an accent rule and a larger headline do the rest (`.story--text-first` in `theme.css`). When the row has no subtitle the points become the summary, so the excerpt line is suppressed to avoid printing them twice. `home.php` also promotes the newest photo-bearing story into the lead slot, falling back to a text-first lead (two-column points) when none has one.
- On `news`, `subtitle` is the one-sentence standfirst (also the card excerpt and SEO/social description) and `lead_text` holds the **key points, one per line**, rendered as an "এক নজরে" box on the article page; a single-line `lead_text` is treated as a legacy intro paragraph. Helpers: `key_points()`, `story_excerpt()`, `seo_description()` in `app/Helpers/text_helper.php`; migration notes in `DATABASE_UPDATES.md`.
