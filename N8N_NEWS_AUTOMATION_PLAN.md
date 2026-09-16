# n8n News Automation — Implementation Plan

## Status (2026-09-16) — Phase 1 fully implemented, running and verified in Docker

**API (CI4 side), all implemented and tested:**
- Migration + `source_url`/`content_hash` columns and indexes on `news`
  (`app/Database/Migrations/2026-09-16-000000_add_source_dedup_to_news.php`,
  mirrored in `dbscript.sql`).
- `App\Config\Automation`, `App\Filters\ApiKeyFilter` (API key + optional
  defense-in-depth IP allowlist via `automation.allowedIps`, empty = unrestricted),
  `App\Controllers\Api\NewsController`, `api/v1` route group.
- Endpoints: `GET exists`, `GET categories`, `GET tags`, `GET news/{id}`,
  `POST news`. `GET news/{id}` (previously skipped as optional) is now implemented.
- `docker/app/Dockerfile` had two pre-existing, unrelated build bugs, both fixed
  since they blocked running the app in Docker at all: missing `libicu-dev` (the
  `intl` PHP extension needs it) and a font-download step pointing at a dead Google
  Fonts CDN URL that also saved a `.woff2` as `.ttf` under both "Bold" and "Regular"
  filenames from the same source. Fixed by removing the manual download —
  `fonts-noto-extra`, already installed a few lines above, provides a real
  `NotoSansBengali-SemiBold.ttf`, which `Admin.php`'s existing font-fallback chain
  for the photo-card generator already tries as its third option.

**Running in Docker right now, verified together:** `docker-compose.yml` (app + db)
and `docker-compose.n8n.yml` (n8n) as two compose stacks sharing one Docker network
(`barind-post_barindpost`, joined via `external: true` in the n8n compose file), so
n8n calls the app as `http://app` by container/service name — no host networking
tricks needed. All three containers (`barindpost_app`, `barindpost_db`,
`barindpost_n8n`) confirmed up together and re-tested end-to-end in this configuration
(auth 401, validation 422, dedup 409, `news/{id}`, IP allowlist both blocking and
passing correctly).

**n8n workflow — redesigned to remove the earlier simplifications, and fully
verified including the AI-dependent nodes:** now 15 nodes, adding two things the
first version skipped:

`Schedule Trigger → [Get Categories / Get Tags (once, parallel)] + Source List (4
feeds) → RSS Feed Read → Tag With Source → Check Exists → Filter New → Build Facts
Prompt → Stage 1: Extract Facts (OpenAI) → Parse Facts → Build Article Prompt →
Stage 2: Generate Article (OpenAI) → Parse Draft + Map Category/Tags → Create Draft`

- **Two-stage AI** (fact extraction, then generation from only those facts) instead
  of one combined prompt, matching the original plan's safety design.
- **Real category/tag mapping**: `Get Categories`/`Get Tags` fetch the actual lists
  once per run; `Parse Draft + Map Category/Tags` fuzzy-matches the AI's guessed
  category/tag names against them (exact match first, substring fallback), falling
  back to the feed's default category if nothing matches, and silently omitting
  unmatched tags rather than guessing IDs.

Verified with a real run (all 4 feeds → 120 articles → dedup → prompts, failing only
at the real OpenAI call on the placeholder key, same well-formed-request proof as
before) **and then a second run using n8n's pinned-data feature** to simulate
realistic Stage 1/Stage 2 OpenAI responses on a disposable copy of the workflow —
this exercised every remaining node for real: `Parse Facts`, `Build Article Prompt`,
`Parse Draft + Map Category/Tags` (confirmed a fake category guess of "অর্থনীতি"
correctly resolved to the real category id 3, and a fake tag guess of "পরীক্ষা"
correctly resolved to the real tag id 52, while a non-matching guess was correctly
left unmatched rather than force-attached), and `Create Draft`, which produced a
real draft article (id 37) in the Dockerized database, confirmed by reading it back
via `GET /api/v1/news/37`. The disposable pinned-data copy was deleted afterward; the
real workflow (id `oToBoNqcfsll7e49`) has no pinning and calls the real OpenAI API.

**What's simplified vs. the full plan, and why (unchanged from before):**
- Workflows 1+2 (source fetch, AI draft) combined into one — fewer moving parts for
  a first working version; split later if useful.
- 4 of the 8 feeds you supplied aren't in the workflow (2 blocked by Cloudflare, 1
  looked like a dead path, 1 was a typo now fixed) — see §6 below.

**Genuinely still open — outside what this session can do, not a matter of more
implementation work:**
- A real OpenAI API key. The "OpenAI (Barind Post Automation)" n8n credential still
  holds a placeholder; I have no key to put there.
- Creating the `reporter@barindpost.com` production user and setting its id as
  `automation.authorId` in the **production** `.env` (§8) — needs your hosting
  account access.
- Actually rotating the live production MySQL password and updating the real
  production `.env` — same reason. Tracked-file secrets in this repo were already
  sanitized.
- Pointing the workflow at production instead of the local `http://app` target, and
  swapping the "Barind Post Automation API" credential to the real production
  `automation.apiKey` — deliberately not done until the above are resolved, so
  nothing here can accidentally write to the live site.

## 1. Purpose

Automate discovery, drafting, and publishing of news content using **n8n** as the
orchestration layer, running locally in Docker, pushing content to the **production**
Barind Post site over a new authenticated REST API. This document scopes what needs
to be built before any workflow or code is written. It is deliberately a cut-down,
realistic version of the broader vision in `n8n.html` — see §9 for what's explicitly
deferred and why.

This is a planning document, not an implementation. Nothing here is built yet.

## 2. Current state (what already exists / doesn't)

- **No API exists.** All content creation goes through session-authenticated admin
  controllers (`Admin::newsStore`, `Admin::newsUpdate` in `app/Controllers/Admin.php`)
  gated by `BaseAdminController`'s session check. There is no token/API-key auth
  anywhere in the codebase.
- **No source/dedup tables.** `news_sources`, `articles_raw`, `content_hash`, and
  `source_url` (as described in `n8n.html` §3–6) don't exist. The `news` table has a
  free-text `source` VARCHAR column only (used for attribution text, not dedup).
- **`author_id` is a required FK.** Every `news` row needs a real `users.id`. Per
  decision in §8, a new dedicated production user (`reporter@barindpost.com`) will be
  created for this (superseding an earlier plan to reuse an existing account).
- **Image handling is by value, not by reference.** `newsStore`/`newsUpdate` write
  `image_url`, `image_caption`, `image_alt_text` directly onto the `news` row as plain
  strings — there's no requirement to go through the `images` gallery table for a news
  article to have an image. This simplifies the API: accept an image URL/caption/alt
  text directly.
- **CSRF is effectively off.** `Config\Security::$csrfProtection = 'cookie'`, but the
  `csrf` filter is commented out of `Config\Filters::$globals` and not attached to any
  route. A new API route group won't hit CSRF at all right now — but if CSRF is ever
  turned on globally later, the API group must be explicitly excluded.
- **Schema has two sources of truth** (`dbscript.sql` vs `app/Database/Migrations/`,
  see `CLAUDE.md`). Any new columns/tables for this feature need a decision on which
  path to use — recommendation in §5.
- **Known pre-existing issue, unrelated to this feature but relevant to exposing a new
  public API:** the tracked `env` / `env.production` files contain real production DB
  credentials. Before opening any new authenticated endpoint on the production site,
  those credentials should be rotated and the files stopped being tracked. Flagging
  again here because a new API is a new attack surface on the same box.

## 3. Target architecture (Phase 1 scope)

```
Local machine                                   Production
┌─────────────────────────┐                     ┌──────────────────────────┐
│ Docker: n8n              │   HTTPS + API key   │ CI4 app (existing)        │
│  - RSS/HTTP source nodes │ ───────────────────▶│  /api/v1/news/* (new)      │
│  - AI nodes (OpenAI etc.)│                     │  -> NewsModel -> MySQL     │
│  - dedup check via API   │ ◀───────────────────│  existing /admin UI        │
│  - HTTP Request node     │   JSON responses    │  (human review/publish)   │
└─────────────────────────┘                     └──────────────────────────┘
```

n8n never touches the production database directly. It only talks to a new
authenticated REST API exposed by the existing CI4 app. Editorial review continues to
happen in the **existing admin panel** (news list already supports draft/published
status, per-role visibility, edit, and publish toggle) — no separate review dashboard
is built in Phase 1.

## 4. New API surface (CI4 side)

### 4.1 Auth

- Add a static API key (or a small `api_keys` table if more than one client is
  expected) checked via a new CI4 **filter**, e.g. `ApiKeyFilter`, applied only to an
  `/api/v1/*` route group — not via session/cookie, since n8n is a server-to-server
  client with no browser session.
- Key sent as `Authorization: Bearer <token>` header.
- Key lives in n8n's credential store (Header Auth credential type) and in the
  server's `.env` (`AUTOMATION_API_KEY`) — never committed, never logged.
- Rate limit / IP-allowlist the route group if the production host's firewall allows
  restricting to the developer's home/office IP (n8n runs locally, so this is
  feasible and meaningfully reduces exposure).

### 4.2 Endpoints (minimum for Phase 1)

| Method | Path | Purpose |
|---|---|---|
| `GET` | `/api/v1/news/exists?source_url=...` | Dedup check before creating a draft |
| `POST` | `/api/v1/news` | Create a draft (or published, if explicitly approved) article |
| `GET` | `/api/v1/news/{id}` | Fetch status (for n8n to poll/confirm after human review, optional) |
| `GET` | `/api/v1/categories` | List categories (id + name) so n8n can map AI-chosen category names to IDs |
| `GET` | `/api/v1/tags` | List existing tags, so n8n can reuse tag IDs instead of creating duplicates |

Explicitly **not** in Phase 1: update/delete via API, image upload via API (image URL
is passed as a string; if the source image needs to be mirrored/hosted, that's a
Phase 2 concern), publish-via-API (publishing stays a human action in `/admin`).

### 4.3 `POST /api/v1/news` request shape

Mirrors the fields `Admin::newsStore` already accepts, minus session-derived values:

```json
{
  "title": "নতুন শিক্ষা নীতি ঘোষণা",
  "subtitle": "...",
  "lead_text": "...",
  "content": "...",
  "category_id": 5,
  "tags": [12, 7],
  "language": "bn",
  "image_url": "https://...",
  "image_caption": "...",
  "image_alt_text": "...",
  "source": "Prothom Alo",
  "source_url": "https://prothomalo.com/...",
  "dateline": "ঢাকা",
  "status": "draft"
}
```

Server-side behavior:
- `author_id` is forced server-side to a fixed, pre-configured user ID (the dedicated
  `reporter@barindpost.com` account — see §8 — set via `.env` as
  `automation.authorId`) — never accepted from the request.
- `status` accepted values: `draft` only, unless a follow-up decision (§8) explicitly
  allows AI-approved auto-publish for specific categories later. Reject `published` in
  Phase 1 regardless of what's sent, matching the "no automatic publishing" principle
  from `n8n.html` §10.
- `slug` generated server-side via the existing `generate_unique_code()` helper, same
  as the admin form — not accepted from the request.
- `source_url` and `content_hash` (SHA-256 of normalized title+content) are stored for
  dedup (see §5) even though they're not shown in the admin UI.
- Validation reuses CI4's validation library; required: `title`, `content`,
  `category_id`. Reject with `422` and a field-level error body on failure.
- Response: `201` with `{ "id": ..., "slug": ..., "status": "draft" }`, or existing
  record info with `409` if `source_url`/`content_hash` already exists.

### 4.4 Where this lives in the codebase

- New `App\Controllers\Api\NewsApiController` (or `App\Controllers\Api\V1\News`),
  extending `BaseController` (not `BaseAdminController` — it has its own auth filter,
  not session auth).
- New routes grouped in `app/Config/Routes.php` under an `api/v1` prefix, with the
  `apikey` filter attached at the group level (`$routes->group('api/v1', ['filter' =>
  'apikey'], function ($routes) { ... })`).
- Extend `NewsModel::$allowedFields` with `source_url` and `content_hash` (see §5).

## 5. Database changes needed

Add two nullable columns to `news` for dedup, plus one indexed lookup:

```sql
ALTER TABLE news
  ADD COLUMN source_url VARCHAR(500) NULL AFTER source,
  ADD COLUMN content_hash CHAR(64) NULL AFTER source_url,
  ADD UNIQUE INDEX uq_news_source_url (source_url),
  ADD INDEX idx_news_content_hash (content_hash);
```

**Decision needed (see §8):** this repo maintains schema in both `dbscript.sql` and
`app/Database/Migrations/`. Given this is new functionality (not a fix to existing
data), recommend doing it as a proper CI4 migration
(`app/Database/Migrations/2026-xx-xx-000000_add_source_dedup_to_news.php`) **and**
mirroring the `ALTER TABLE` into `dbscript.sql` so fresh installs stay consistent —
same pattern already used for the `reporterRole` column
(`2024-01-15-000000_add_reporter_field_to_news.php`).

No `news_sources` / `articles_raw` / `pgvector` tables in Phase 1 — n8n's own
workflow state (or a simple lookup via `GET /api/v1/news/exists`) is enough to avoid
re-processing the same URL. Story-level clustering across multiple sources (`n8n.html`
§6, §19 "Story Intelligence") is a Phase 3 idea, not needed to prove the pipeline end
to end.

## 6. n8n side (local Docker)

- Add an `n8n` service to a **new** compose file (keep it separate from the app's
  `docker-compose.yml` — n8n is a personal automation tool, not part of this app's
  deployable stack), or a sibling `docker-compose.n8n.yml`:
  - Official `n8nio/n8n` image.
  - Persist workflows/credentials in a named volume.
  - `N8N_ENCRYPTION_KEY` set via `.env` (n8n's own, unrelated to the CI4 app's
    `encryption.key`) so credentials in n8n's SQLite store are encrypted at rest.
- Workflows for Phase 1 (scoped down from `n8n.html` §11's 7-workflow design — only
  what's needed to prove the pipeline):
  1. **Source Fetcher**: Cron → fixed list of RSS feeds (hardcoded in the "Source
     List" node for Phase 1; a `news_sources` table/UI is Phase 2 per §9) → for each
     item, `GET /api/v1/news/exists?source_url=...` → skip if it exists. Of the 8
     feeds supplied, 4 are wired in and confirmed reachable:
     amarbanglabd.com/feeds, thedailystar.net/frontpage/rss.xml,
     bd24live.com/feed, jagonews24.com/rss/rss.xml (note: the last one had a typo
     missing the leading `h` in `https` — corrected). The other 4 failed validation
     and were left out: jugantor.com/feed/rss.xml (200 but "Access denied" body —
     likely wrong path), kalerkantho.com/rss.xml (403), banglanews24.com/rss/rss.xml
     and bdnews24.com's widget feed (both 403, Cloudflare bot-challenge pages — not
     attempted to bypass, since that's the publisher's explicit anti-automation
     control). Send corrected URLs if you have them.
  2. **AI Draft**: for each new URL → fetch/extract article text → AI call(s) to
     produce Bangla headline, summary/lead, body, category guess, tags → `GET
     /api/v1/categories` and `/api/v1/tags` to map names to IDs → `POST
     /api/v1/news` with `status: draft`.
  3. Editorial review happens in the existing `/admin/news` UI — no separate n8n or
     dashboard workflow needed for this step in Phase 1.
- Credentials (API key, AI provider key) go in n8n's built-in credential store, never
  hardcoded into a workflow's JSON (which would leak them if the workflow is
  exported/shared).

## 7. Content-safety guardrails (carried over from `n8n.html`, non-negotiable regardless of phase)

- Never auto-publish; always land as `draft` for a human to approve (§10 of the source
  doc, enforced server-side per §4.3 above, not just as a workflow convention).
- Treat AI output as a rewrite/summary of the source, not a copy — check the source
  publisher's terms/robots.txt before scraping, and always store `source` +
  `source_url` for attribution.
- Don't let the AI state anything not present in the source text — this matters more
  once multi-source synthesis (Phase 3) is attempted, but even in Phase 1 the prompt
  should instruct the model to stick to given facts.

## 8. Open decisions (need your input before implementation starts)

1. ~~AI provider~~ — **Decided: OpenAI.**
2. ~~Automation user~~ — **Decided:** create a new dedicated production user,
   `reporter@barindpost.com`, role `reporter`, via the existing `/admin/users` "Add
   User" form (reuses the app's own password hashing/validation instead of raw SQL).
   After creating it, look up its `id` (visible on `/admin/users`, or
   `SELECT id FROM users WHERE email = 'reporter@barindpost.com';`) and set that as
   `automation.authorId` in the **production** server's `.env` (not the tracked repo
   file). This reverses the earlier "reuse an existing user" plan.
3. ~~Initial source list~~ — **Decided**, 8 feeds supplied by the user (see §6).
4. **API exposure** — is the production host's firewall able to restrict the new
   `/api/v1/*` group to the developer's home/office IP, or does it need to be reachable
   from anywhere (e.g. if n8n later moves off a home machine)?
5. ~~`env`/`env.production` credential exposure~~ — **Decided: rotate now.** Tracked
   files sanitized (this session); rotating the live MySQL password and updating the
   real production `.env` is still pending and requires hosting-account access this
   session doesn't have.

## 9. Explicitly deferred (Phase 2 / Phase 3, not built now)

Per the phased approach already recommended in `n8n.html` §21, this plan intentionally
excludes, until Phase 1 proves the pipeline works end-to-end:

- PostgreSQL + pgvector, story clustering / near-duplicate detection across sources.
- A separate Spring Boot "News AI API" service — the existing CI4 app's new
  `/api/v1` endpoints are enough for Phase 1.
- A separate React/MUI "AI News Desk" review dashboard — the existing `/admin/news`
  list already provides draft/review/publish.
- `news_sources` management table/UI (sources are hardcoded in the n8n workflow for
  now).
- Social distribution (Facebook/X/Telegram copy generation), AI image generation,
  breaking-news/trend detection, fact-provenance table (`ai_generations`,
  `story_facts`, etc.).

## 10. Suggested build order (once decisions in §8 are made)

1. ~~Migration: `source_url` + `content_hash` columns (§5).~~ **Done.**
2. Create the dedicated `reporter@barindpost.com` production user via `/admin/users`,
   then set its `id` as `automation.authorId` in the production `.env` (§8). *Local
   `.env` already uses the seeded sample id `3` for dev only — unaffected by this.*
3. ~~`ApiKeyFilter` + `/api/v1` route group + `NewsApiController` with
   `exists`/`categories`/`tags`/`POST news` (§4).~~ **Done.**
4. ~~Manual smoke test with `curl`/Postman against a local copy of the DB — not
   production — before pointing n8n at it.~~ **Done** — see §11 for the commands.
5. ~~n8n Docker Compose + credentials setup (§6).~~ **Done and running.**
6. ~~Workflow 1 (Source Fetcher) against one RSS feed, verify dedup works.~~ **Done**
   — verified against all 4 wired-in feeds (120 articles), running in Docker.
7. ~~Workflow 2 (AI Draft) end to end for one article, verify it lands correctly in
   `/admin/news` as a draft with correct category/tags/slug.~~ **Done** — verified via
   pinned-data simulation (see Status section above); only the real OpenAI key is
   missing to make this a genuinely live (non-simulated) run.
8. Point at production only after: the credential rotation in §2/§8 is resolved, the
   production `automation.authorId` (step 2 above) is set, and a real OpenAI key is
   in place — this local Docker run already stands in for "a full dry run against a
   local copy of the app and DB."

## 11. Running and testing everything in Docker (verified 2026-09-16)

Both stacks now run together, on a shared Docker network, with no host-networking
workarounds:

```bash
# 1. App + MySQL (docker/app/Dockerfile fixed this session — see Status above)
docker compose up -d

# 2. n8n, joined to the same network as a second compose file
cp n8n.env.example .env.n8n   # fill in a real N8N_ENCRYPTION_KEY (openssl rand -hex 32)
docker compose -f docker-compose.n8n.yml up -d

# App: http://localhost/            n8n editor: http://localhost:5678
# From inside the n8n container, the app is reachable as http://app (not localhost).
```

Exercising the API directly (same as before, just against port 80 instead of a
host-run `php spark serve`):

```bash
KEY="<your automation.apiKey from .env>"
curl -H "Authorization: Bearer $KEY" http://localhost/api/v1/categories
curl -H "Authorization: Bearer $KEY" http://localhost/api/v1/news/37
curl -X POST http://localhost/api/v1/news \
  -H "Authorization: Bearer $KEY" -H "Content-Type: application/json" \
  -d '{"title":"শিরোনাম","content":"যথেষ্ট দীর্ঘ একটি কন্টেন্ট অনুচ্ছেদ এখানে বসবে।","category_id":3,"source_url":"https://example.com/a"}'
```

A second POST with the same `source_url` (or identical `title`+`content`) correctly
returns `409` with the existing article's `id`/`slug`/`status` instead of creating a
duplicate; missing required fields return `422`; requests without a matching
`Authorization` header return `401`.

Optional IP allowlist, on top of the API key (empty/unset = unrestricted, the
default):
```
automation.allowedIps = 203.0.113.10, 203.0.113.11
```
Verified both directions: a non-matching caller gets `403` (confirmed this also
correctly blocks n8n's own container IP if it's not in the list — the allowlist
applies to every caller equally, so n8n needs to be included once this is turned on
for real).
