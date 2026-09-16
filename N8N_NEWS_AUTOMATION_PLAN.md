# n8n News Automation — Implementation Plan

## Status (2026-09-16)

**Phase 1 API is implemented and smoke-tested locally** (build order items 1, 3, 4 in
§10). What exists now:

- Migration `app/Database/Migrations/2026-09-16-000000_add_source_dedup_to_news.php`
  and matching `source_url`/`content_hash` columns + indexes added directly to the
  `news` table in `dbscript.sql`.
- `App\Config\Automation` (reads `automation.apiKey` / `automation.authorId` from
  `.env`), `App\Filters\ApiKeyFilter`, `App\Controllers\Api\NewsController`, and the
  `api/v1` route group in `app/Config/Routes.php`.
- Verified end-to-end against a local Dockerized MySQL + `php spark serve`: auth
  rejection (no/wrong key → 401), `GET /api/v1/categories`, `GET /api/v1/tags`,
  `GET /api/v1/news/exists`, `POST /api/v1/news` (create → 201, correct `author_id`/
  `slug`/`content_hash`/`word_count`, invalid tag IDs silently skipped and reported),
  duplicate `source_url` → 409, missing required fields → 422, `status: published` →
  422 (Phase 1 never auto-publishes).
- `docker-compose.n8n.yml` + `n8n.env.example` scaffolded for running n8n locally
  (not started — see open decisions below).

**Not implemented / still open:**
- `GET /api/v1/news/{id}` — listed as optional in §4.2, skipped for now.
- The actual n8n workflows (Source Fetcher, AI Draft) — blocked on the open decisions
  below, particularly AI provider and the RSS source list (the latter can't be guessed
  on your behalf).
- Which **production** `users.id` to set as `automation.authorId` on the real server —
  local testing used the seeded sample user id `3` ("Eve Editor"), which has no
  bearing on production.
- Rotating the exposed `env`/`env.production` credentials (§2) before this API is
  reachable in production.

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
  decision in §8, automated posts will use an existing production user's ID rather
  than a new dedicated bot account — no new `users` row needed.
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
- `author_id` is forced server-side to a fixed, pre-configured existing user ID (an
  existing production account — see §8 — set via `.env` as `AUTOMATION_AUTHOR_ID`) —
  never accepted from the request, and never a newly created bot account.
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
  1. **Source Fetcher**: Cron → fixed list of RSS feeds (hardcoded in the workflow for
     Phase 1; a `news_sources` table/UI is Phase 2 per §9) → for each item, `GET
     /api/v1/news/exists?source_url=...` → skip if it exists.
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

1. **AI provider** — OpenAI, or something else (cost/latency/Bangla quality
   trade-offs differ)?
2. ~~Automation user~~ — **Decided:** reuse an existing production user's `id` as
   `author_id` for API-created articles, rather than creating a dedicated bot account.
   Still need: *which* existing user (their role determines nothing server-side here,
   since the API always forces `status: draft` regardless of role — but pick one whose
   name/byline makes sense to appear on automated drafts, e.g. an editor account
   rather than an individual reporter's).
3. **Initial source list** — which 3–5 RSS feeds to start with (needed to build/test
   workflow 1 concretely)?
4. **API exposure** — is the production host's firewall able to restrict the new
   `/api/v1/*` group to the developer's home/office IP, or does it need to be reachable
   from anywhere (e.g. if n8n later moves off a home machine)?
5. **`env`/`env.production` credential exposure** (§2) — rotate now, before adding a
   new authenticated surface to the same server, or accept the existing risk and
   proceed?

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
2. Confirm the existing **production** `users.id` to use as `automation.authorId` and
   set it in the production `.env` (no schema change needed since no new user row is
   created). *Local `.env` already uses the seeded sample id `3` for dev only.*
3. ~~`ApiKeyFilter` + `/api/v1` route group + `NewsApiController` with
   `exists`/`categories`/`tags`/`POST news` (§4).~~ **Done.**
4. ~~Manual smoke test with `curl`/Postman against a local copy of the DB — not
   production — before pointing n8n at it.~~ **Done** — see §11 for the commands.
5. ~~n8n Docker Compose + credentials setup (§6).~~ **Scaffolded** (`docker-compose.n8n.yml`,
   `n8n.env.example`) — not yet started; needs `N8N_ENCRYPTION_KEY` generated first.
6. Workflow 1 (Source Fetcher) against one RSS feed, verify dedup works. *Blocked on
   AI provider + source list decisions in §8.*
7. Workflow 2 (AI Draft) end to end for one article, verify it lands correctly in
   `/admin/news` as a draft with correct category/tags/slug. *Same blocker.*
8. Point at production only after: the credential rotation in §2/§8 is resolved, the
   production `automation.authorId` (step 2 above) is set, and a full dry run against
   a local/staging copy of the app and DB has passed.

## 11. Testing the API locally (verified 2026-09-16)

```bash
# 1. Bring up just the DB (the app's docker/app/Dockerfile currently fails to build —
#    pre-existing issue, unrelated to this feature: it installs the `intl` PHP
#    extension without the required libicu-dev system package. Not fixed as part of
#    this plan; flagging in case it blocks `docker compose up -d` for the app service).
docker compose up -d db

# 2. Run the app with host PHP against the container's published DB port, since
#    the `db` hostname in .env only resolves inside the compose network.
#    (Temporarily override .env's database.default.hostname/port back to
#    127.0.0.1/3307 for this, then revert to db/3306 afterward — or keep a separate
#    .env.local for this workflow if you do it often.)
php spark serve --port 8123

# 3. Exercise the API (key must match automation.apiKey in .env)
KEY="<your automation.apiKey>"

curl -H "Authorization: Bearer $KEY" http://localhost:8123/api/v1/categories
curl -H "Authorization: Bearer $KEY" "http://localhost:8123/api/v1/news/exists?source_url=https://example.com/a"

curl -X POST http://localhost:8123/api/v1/news \
  -H "Authorization: Bearer $KEY" -H "Content-Type: application/json" \
  -d '{
        "title": "শিরোনাম",
        "content": "যথেষ্ট দীর্ঘ একটি কন্টেন্ট অনুচ্ছেদ এখানে বসবে।",
        "category_id": 3,
        "tags": [1],
        "source": "Wire",
        "source_url": "https://example.com/a"
      }'
```

A second POST with the same `source_url` (or identical `title`+`content`) correctly
returns `409` with the existing article's `id`/`slug`/`status` instead of creating a
duplicate.
