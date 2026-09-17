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
(`.github/workflows/ci.yml`) runs `php -l` over `app/` and `phpunit --exclude-group needs-db` on
every push/PR.

### Docker

```bash
docker-compose up -d
```
Runs the app on `http://localhost` (Apache) and MySQL 8.0 on host port `3307`. The `db` service auto-imports `dbscript.sql` on first boot via `docker-entrypoint-initdb.d`.

## Database: two sources of truth

This project has **both** CodeIgniter migrations (`app/Database/Migrations/`) **and** a monolithic `dbscript.sql` at the repo root, and they are not always kept in sync. `dbscript.sql` is the schema used to seed Docker/local/prod databases from scratch (including sample data); migrations exist for a few later, incremental changes. When adding a schema change, check whether the project convention at that time was "add it to `dbscript.sql`" or "write a migration" by looking at how the most recent related feature was done (e.g. see `DATABASE_UPDATES.md` and `REPORTER_ROLES_IMPLEMENTATION.md`, which both describe schema changes applied directly via SQL rather than migrations). Don't assume `php spark migrate` alone reflects the live schema.

`dbscript.sql` is run through the MySQL CLI on a fresh install (e.g. via Docker's `docker-entrypoint-initdb.d`), which **stops at the first error** — one bad statement silently truncates everything after it, with no obvious symptom beyond "some tables are just missing" later on. This already happened once: a stray `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` (MariaDB syntax, invalid on real MySQL) partway through the file was silently skipping the back half of the script — `reporter_roles`, `user_reporter_roles`, `cities`, `prayer_times`, and the whole sports-events table family — on every fresh install, including this one, until it was found and removed. If tables that are clearly defined in `dbscript.sql` seem to be missing from a live database, suspect this pattern (a broken statement earlier in the file) before assuming they're migration-only.

Also: the two oldest migrations (`2024-06-01-000000_create_roles.php`, `…000001_create_users.php`) declared classes named after their timestamps (`class 2024_06_01_…`), which PHP cannot parse — so `php spark migrate` could never have run on any install until they were renamed (`CreateRoles`, `CreateUsers`) on 2026-09-17. Treat the migration history as never having been applied anywhere; `dbscript.sql` is what every existing database came from.

Key tables: `users`, `roles`, `news`, `categories`, `tags`, `news_tags`, `images`, `reporter_roles`, `user_reporter_roles`, plus a sports-events domain (`sports_events`, `sports_participants`, `sports_matches`, `sports_event_entries`, `sports_match_events`, `sports_venues`) and `prayer_times`/`cities` for the prayer-times feature. Charset is `utf8mb4` / `utf8mb4_unicode_ci` throughout — required for Bengali text.

## Authentication & authorization

There is no filter-based auth for the admin area (the only filter, `app/Filters/ApiKeyFilter.php`, guards the `api/v1` automation routes) and no auth library/Shield — admin auth is hand-rolled session state:

- `Auth::attemptLogin` (`app/Controllers/Auth.php`) verifies against `UserModel` and sets `session()->set(['user_id', 'user_name', 'user_role', 'logged_in'])`. It is throttled with CI4's `Throttler` (5 attempts/min per IP+email, 30/min per IP); the buckets live in `writable/throttle/`, deliberately outside `writable/cache/`, because `purge_public_cache()` empties the cache dir on every news change and must not reset login counters.
- `BaseAdminController::initController` (`app/Controllers/BaseAdminController.php`) is the gate for the whole admin area: any controller extending it requires `session('logged_in')` and `session('user_role')` in `['admin', 'editor', 'sub-editor', 'reporter']`, else redirects to `/login`.
- **CSRF is on globally** (`app/Config/Filters.php`, cookie-based, one token per session — `Security::$regenerate = false` on purpose, because admin pages fire many AJAX POSTs from one page load). Every POST `<form>` must contain `<?= csrf_field() ?>`; AJAX needs no per-call change because both layouts put the token in `<meta name="csrf-token">` and install a small `fetch`/`XMLHttpRequest` shim that adds the `X-CSRF-TOKEN` header to same-origin non-GET requests. Excluded: `api/v1/*` (Bearer auth) and `news/view/*` (`sendBeacon` can't send headers). A new POST endpoint called from a `<form>` without the field, or from JS outside those layouts, will get a 403.
- Individual controller methods do their own finer-grained role checks inline (e.g. `session('user_role') !== 'admin'` for admin-only actions like Photo Card Generator, or reporter-role filtering in news creation) — there's no centralized permission matrix, so check the specific method rather than assuming `BaseAdminController`'s check is sufficient.

## Routing & controllers

All routes are declared explicitly in `app/Config/Routes.php` (no auto-routing) — grouped by feature area: core admin CRUD (`Admin.php`, ~1750 lines — the largest controller, covering news, categories, tags, roles, users, kickers, photo cards, contacts, reporter roles), sports events (`AdminSportsEvents.php` / `SportsEvent.php`), image handling (`ImageUpload.php`), prayer times (`PrayerTimes.php`), and the public site (`PublicSite.php`). When adding an admin feature, follow the existing pattern: add routes under `// Admin ...` in `Routes.php`, extend `BaseAdminController`, and add a sidebar link in `app/Views/admin/sidebar.php`.

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
- **Automation drafts are triaged in `/admin/incoming`** (`AdminIncoming`): every draft with a `source_url`. Discard = `status archived` (never delete: `source_url` must survive for dedup). `news.suggested_image_url` is a source-page image *suggestion* shown only there; "Use image" copies it into `public/uploads/news/` + `images` like a manual upload. The pipelines must never set `image_url` themselves.
- On `news`, `subtitle` is the one-sentence standfirst (also the card excerpt and SEO/social description) and `lead_text` holds the **key points, one per line**, rendered as an "এক নজরে" box on the article page; a single-line `lead_text` is treated as a legacy intro paragraph. Helpers: `key_points()`, `story_excerpt()`, `seo_description()` in `app/Helpers/text_helper.php`; migration notes in `DATABASE_UPDATES.md`.
