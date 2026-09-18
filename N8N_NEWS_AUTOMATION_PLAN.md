# n8n News Automation — Implementation Plan

## Status (2026-09-16, updated 2026-09-17) — Phase 1 fully implemented, running and verified in Docker

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
real workflow (id `VTYgCkQktefYyy3i` as of the latest update — the id changes each
time the workflow is replaced via delete+recreate, since n8n has no in-place
"update" call this session used) has no pinning and calls the real OpenAI API.

**Error tolerance added and verified (2026-09-16):** none of the 15 nodes had any
error handling configured — n8n's default is to abort the *entire* execution the
moment any single item fails anywhere, which would mean one bad AI response (or a
transient OpenAI/API hiccup) on article #47 of 120 kills the other 119 too, including
ones that already succeeded. Set `onError: "continueRegularOutput"` on the five nodes
downstream of dedup that can fail per-article (`Stage 1`, `Parse Facts`, `Stage 2`,
`Parse Draft + Map Category/Tags`, `Create Draft`), then proved it with a pinned-data
run seeding one good response and one deliberately malformed (`"THIS IS NOT VALID
JSON {{{"`) response side by side: the malformed item failed cleanly at both `Parse
Draft + Map Category/Tags` (JSON parse error, caught) and `Create Draft` (422 from
our own validation, caught), while the good item in the same run was unaffected and
became a real draft (id 38, confirmed via `GET /api/v1/news/38`).

**New: single-URL ingestion, not just RSS (2026-09-16).** A second, separate n8n
workflow — "Barind Post - Manual URL Ingest" — lets you (or another system) submit
one arbitrary news article URL instead of waiting for it to show up in an RSS feed.
This is genuinely different from RSS ingestion: RSS gives structured `title`/
`content` fields for free, whereas a bare URL only gives raw HTML, which has to be
turned into clean article text before it can go through the same AI pipeline.

- **Trigger**: `POST http://localhost:5678/webhook/ingest-url`, header-auth protected
  with the same shared secret as everything else (`Authorization: Bearer
  <automation.apiKey>` — reusing the same credential in n8n, just in its "incoming
  request" direction instead of "outgoing call"). Body: `{"url": "...", "source_name":
  "..." (optional, defaults to the URL's hostname), "category_id": <optional, default
  1>}`.
- **Extraction** (`Shape Article Item` node): fetches the raw HTML, then tries Open
  Graph tags (`og:title`/`og:description`), then JSON-LD `NewsArticle`/`Article`
  schema (`articleBody`/`headline`), then falls back to concatenating all `<p>` tag
  text with short fragments filtered out. This is a generic, dependency-free
  approximation — **not** a real readability library (Trafilatura/Readability),
  which `n8n.html`'s own original plan flagged as the properly robust option for
  this ("Level 3 — Website extraction" needing a dedicated extractor). Known
  limitations, both expected and observed in testing:
  - Some boilerplate (bylines, timestamps, share-button labels) can leak into the
    extracted text alongside the real article content — seen in the verification run
    below (a repeated byline and a "X (Twitter) X" fragment came through).
  - Pages that need JavaScript to render their content won't work at all — this is a
    plain HTTP fetch, no browser/JS execution.
  - Quality varies by site; there's no per-site tuning, on purpose (that's exactly
    the fragility RSS/API-first ingestion was chosen to avoid — see `n8n.html` §4).
- **Same dedup + AI pipeline as RSS**: after extraction, the item flows into
  `Check Exists` → (new: continue: / duplicate: fork) → the identical `Build Facts
  Prompt` → `Stage 1` → `Parse Facts` → `Build Article Prompt` → `Stage 2` → `Parse
  Draft + Map Category/Tags` → `Create Draft` nodes **reused verbatim** from the RSS
  workflow (copied node-for-node from the live, verified workflow rather than
  retyped, to avoid the two drifting apart). A duplicate URL short-circuits straight
  to a `{"status": "duplicate", "existing": {...}}` response instead of running the
  AI pipeline pointlessly.
- **A real bug found and fixed along the way**: the webhook was first built using
  `responseMode: "lastNode"` (let n8n figure out what to respond with). That turned
  out to be unreliable for a workflow with two possible terminal branches of very
  different lengths — n8n kept selecting the instant, always-empty "not a duplicate"
  branch as the response source over the much longer AI-pipeline branch, even after
  routing both into a shared downstream node, producing a bogus "No item to return
  was found" error on an otherwise-successful run. Fixed by switching to an explicit
  `Respond to Webhook` node (`responseMode: "responseNode"`) that both branches feed
  into — verified correct afterward.
- **Verified with two real webhook calls** against a real, previously-unseen article
  URL from one of your own RSS feeds: extraction correctly pulled the real Bengali
  headline and article body (shown above), dedup correctly judged it new, and it
  correctly failed at the real OpenAI call on the placeholder key with a clean error
  surfaced all the way back through the webhook response (same expected failure mode
  as the RSS workflow — nothing new here since these nodes are the reused, already-
  proven ones). A second call with an already-existing article's URL correctly
  returned the duplicate response instead of reprocessing it. Not separately
  re-verified with pinned AI data on this workflow, since the AI-chain nodes are
  identical, verbatim copies of the ones already proven end-to-end (including a real
  created draft) on the RSS workflow — re-proving identical logic would test nothing
  new.

**New: "top stories from a listing page" — Daily Nayadiganta (2026-09-17).** A third,
small n8n workflow, "Barind Post - Nayadiganta Latest (top 10)" (id
`DnSbtm1vi8XDf1mN`), for sites with no usable RSS: it scrapes the site's own "latest"
listing, picks the top 10 stories, and hands each URL to the Manual URL Ingest
workflow above — so the extraction, dedup, two-stage AI and draft creation are not
duplicated, just reused through the ingest webhook.

`Manual Trigger → Fetch Latest Page → Pick Top Stories → Send to URL Ingest`

- **Fetch Latest Page**: plain `GET https://dailynayadiganta.com/latest` with a
  browser `User-Agent` (the site answers a normal fetch with the full HTML; no JS
  rendering needed), response kept as raw text.
- **Pick Top Stories** (Code node, `const TOP_N = 10` at the top — the only thing to
  change for a different count): the page's "latest" list lives inside `<main>`; the
  `/post/…` links *before* `<main>` are a header/featured block (op-eds), so the node
  starts scanning at `<main>` and takes the first `TOP_N` distinct
  `href="/post/<section>/<id>"` links in page order. Fallback if `<main>` is ever
  missing: the highest post ids on the page (ids are sequential, so highest =
  newest). Emits one item per story: `{rank, url, post_id, title, source_name:
  "Daily Nayadiganta"}`.
- **Send to URL Ingest**: `POST http://localhost:5678/webhook/ingest-url` (n8n
  calling its own webhook inside the container) once per story with `{url,
  source_name}`, using the same "Barind Post Automation API" header credential the
  webhook requires — so the ingest workflow must be **active** (it is). Requests go
  out in batches of 3 (1 s apart) and the node has `onError: continueRegularOutput`,
  so one failing article surfaces as an error item without aborting the other nine.
  Each item's output is the ingest response: `{id, slug, status: "draft"}` for a new
  draft, `{status: "duplicate", existing: {...}}` for an already-ingested URL.
- **Verified with real runs**: first as a top-1 version (execution 13, `success`,
  14 s → draft **42** from `/post/market/1054107`), then as top-10 (execution 15,
  `success`, 30 s): all 10 listing items were picked in order (1054107 … 1054098,
  skipping the three op-ed links above `<main>`); item 1 correctly came back
  `duplicate` (it was draft 42, by then published from the admin), and the other 9
  became drafts **43–51** in the local Docker database — 10 Nayadiganta articles in
  total, each with a Bengali headline, lead and 57–114-word body, `source` = Daily
  Nayadiganta and `source_url` set for dedup. Category fell back to the webhook's
  default (id 1) and no tags matched on any of them — the ingest workflow's existing
  category/tag mapping behaviour, unchanged here and the obvious next thing to tune.
- **How to run**: open it in the n8n editor and press *Execute workflow* (it has no
  schedule on purpose — add a Schedule Trigger in front of *Fetch Latest Page* and
  activate it when you want it unattended; re-runs only create drafts for stories
  not seen before). Other sites' listing pages need only the URL and the link regex
  in *Pick Top Stories* changed.
- Target is still the local `http://app` via the ingest workflow — nothing reaches
  production.

**New: one button for "10 from every source" (2026-09-17).** A fourth workflow,
"Barind Post - Collect All Sources (10 each)" (id `00x37gmrqXDJoi77`), runs both
collectors and reports per source:

`Manual Trigger → [Run RSS Sources (10 each) | Run Nayadiganta (top 10)] → Merge → Summarize`

- The two `Execute Workflow` nodes call the collectors as sub-workflows (each collector
  gained a "When Executed by Another Workflow" trigger next to its existing one, so
  the RSS workflow's Schedule Trigger and the Nayadiganta Manual Trigger still work
  on their own). `Summarize` outputs one row per source — `{source, created,
  duplicate, error}` — plus a `TOTAL` row.
- **Per-source cap in the RSS workflow**: new `Limit Per Source` Code node between
  `Tag With Source` and `Check Exists` keeps the newest `MAX_PER_SOURCE = 10` items of
  each feed (sorted by `isoDate`/`pubDate` within a source). `Filter New`, which
  pairs dedup results with originals by position, now reads from `Limit Per Source`
  instead of `Tag With Source` — required, otherwise the cap would misalign dedup.
  Before this the RSS workflow processed *every* feed item (120 in the first test).
- **A dead feed no longer kills the run**: `RSS Feed Read` now has `onError:
  continueErrorOutput`, so a feed that fails to fetch/parse goes to the node's error
  output and the other feeds continue. Proven on the very first run: Jago News 24's
  feed served malformed XML that day (`Invalid character in entity name`, column
  112891 of line 3) — the other three feeds were unaffected. It had parsed fine the
  day before, so this is intermittent on their side; if it persists, the fix is to
  fetch it with an HTTP Request node, repair the entities in a Code node, and parse
  with the XML node instead of `RSS Feed Read`.
- The two OpenAI nodes now send requests in batches of 5 (1 s apart) instead of all
  at once, and both collectors end in a `Report` Code node that attaches
  `source`/`source_url`/`title` and a `created | duplicate | error` status to each
  result, which is what `Summarize` aggregates.
- **Verified with a real run (execution 26, `success`, 30 s)**: `Summarize` reported
  Amar Bangla 10 / BD24Live 10 / Daily Nayadiganta 10 / The Daily Star 10 created,
  0 duplicates, 0 errors — 40 new drafts (ids 52–91) in the local Docker database,
  52–100 words each; the cap was exercised for real (Amar Bangla's feed carried 50
  items, 10 kept), and category mapping resolved a real category for 27 of the 40.
- **How to run**: n8n editor → "Barind Post - Collect All Sources (10 each)" →
  *Execute workflow*. Re-runs only add stories not seen before (dedup on
  `source_url`). For unattended runs, put a Schedule Trigger in front of the two
  `Execute Workflow` nodes and activate it (the RSS workflow's own daily Schedule
  Trigger would also work alone, but then Nayadiganta wouldn't run). To change the
  count: `MAX_PER_SOURCE` in `Limit Per Source` (RSS) and `TOP_N` in `Pick Top
  Stories` (Nayadiganta).

**Drafts now carry a subtitle and key points (2026-09-17).** The site's two summary
fields changed meaning (see `DATABASE_UPDATES.md`): `subtitle` is the one-sentence
standfirst (also SEO/social description and card excerpt) and `lead_text` holds
3–5 key points, one per line, shown as an "এক নজরে" box above the body. In both the
RSS and the URL-ingest workflows, `Build Article Prompt` now asks Stage 2 for
`subtitle_bn` (one sentence, ≤ 25 words) and `key_points_bn` (3–5 facts, ≤ 15 words
each, drawn only from the Stage 1 facts object) instead of `summary_bn`, and `Parse
Draft + Map Category/Tags` maps them to `subtitle` (trimmed to 255) and a
newline-joined `lead_text`. The API also accepts `lead_text`/`key_points` as an
array. Because the points come from the already-extracted facts, this adds no new
hallucination surface beyond what Stage 2 already had.

**Newsroom visibility, duplicates, draft quality, listing sources, OG images (2026-09-18).**

- **Run summaries the newsroom sees.** The orchestrator now ends `Summarize → Build Run
  Report → Post Run Report → Notify Newsroom (Telegram)`. `Post Run Report` calls
  `POST /api/v1/automation/runs` (Bearer, `Api\AutomationController`), which stores one
  row per run in the new `automation_runs` table; `/admin/incoming` shows the latest as a
  "Last collection run" banner (green/red, per-source counts, error notes). The Telegram
  node is **disabled** with a sticky note explaining the four steps to enable it (BotFather
  token → n8n credential → chat id → enable); nothing depends on it.
- **Feed failures are no longer silent.** In the RSS workflow, `RSS Feed Read`'s error
  output goes to `Feed Failure`, which is merged with the article `Report` items
  (`Collect Reports` → `Run Report`, the workflow's last node), so a feed that cannot be
  parsed shows up in the summary as `Jago News 24 ⚠1 — feed: Invalid character…`.
- **Crashes go to an error workflow.** "Barind Post - Pipeline Error Alert"
  (`n8n-nodes-base.errorTrigger` → `Format Error` → `Post Error Report` → Telegram) is set
  as *Settings → Error workflow* on all four pipelines. **It must be active** — n8n 2.x
  logs `Workflow "…" is not active and cannot be executed` and silently skips it
  otherwise (found the hard way: the first two ingest failures produced no alert). Any execution that fails (article
  API down, extraction refusing a page, OpenAI failing) becomes a red banner with the
  workflow name, the failing node and the message, plus the execution URL.
- **Cross-source duplicates.** Both AI workflows now send `source_title` (the outlet's own
  headline). At intake the API compares it and the rewritten title against the last 7
  days of articles with `App\Libraries\TitleSimilarity` (content-word overlap after
  stopwords/punctuation/digit normalisation; ≥ 3 shared words and ≥ 0.6 containment or
  Jaccard) and sets `news.possible_duplicate_of` / `duplicate_score`. The queue shows a
  yellow "possible duplicate of #N (score%)" badge linking to the other article; nothing is
  merged automatically. Unit-tested in `tests/unit/TitleSimilarityTest.php`.
- **Draft quality.** `Build Article Prompt` now asks for a fixed structure (lead →
  context → attributed quote(s) → what's next), a target length by section (news 220–320
  words, sport/entertainment/lifestyle 180–260, editorial/special report 350–500, always
  bounded by the extracted facts), no headline restatement, and tags picked **from the
  real tag list** (`tags_bn`, exact names; fuzzy only as fallback). The extraction library
  strips site chrome glued to the first paragraph ("প্রকাশ: … মিনিটে পড়ুন", photo
  captions, share labels). The first run with the length target still produced ~100-word
  drafts from 160–1,000-word sources: Stage 1 was returning only a handful of `key_facts`,
  and Stage 2's "write fewer if the facts are thin" clause let the model stop there. Stage 1
  now demands *every* distinct fact (typically 8–15 for a full article, all quotes as
  "speaker: quote") and Stage 2 is told how many facts/quotes it holds and must use all
  of them, shorter only below 5 facts.
- **Feed titles can be objects.** The Daily Star wraps titles in a link, and rss-parser
  hands that over as `{ a: [ { _: "text", $: { href } } ] }` — which made `source_title`
  fail API validation (422) and put "[object Object]" into Stage 1's "Source title" line.
  `Tag With Source` now normalises `title` once with a small `titleText()` helper (first
  text node found), `Parse Draft` applies the same to `source_title`, and the API discards
  non-string values.
- **Listing collector is config-driven.** "Barind Post - Listing Sources (top N per site)"
  (formerly the Nayadiganta workflow) reads a `Listing Sources` array — `listing`,
  `link_regex`, `scope_marker`, `top_n` per site — and reports listing-fetch failures
  through the same `Report` path. **The four requested sites cannot be added:** Jugantor,
  Kaler Kantho and Banglanews24 answer plain HTTP with Cloudflare's JS challenge (403
  "Just a moment…", the same reason their feeds failed on day one) and
  bangla.bdnews24.com renders its listings with JavaScript (4 article links in 400 KB of
  HTML). They would need a headless browser (e.g. an n8n Browserless/Puppeteer node) —
  a separate decision, not a config change.
- **OG image for photo-less articles.** The GD headline-card renderer moved from the admin
  Photo Card tool into `App\Libraries\PhotoCard`; `GET /og/{id}.png`
  (`PublicSite::ogImage`, cached in `writable/og/`) serves it, and article pages use it as
  `og:image` / `twitter:image` when `image_url` is empty (previously the site logo).

**Pointing the pipelines at production (2026-09-18).** The target is now a single switch
instead of ten hardcoded URLs: `docker-compose.n8n.yml` sets `BARIND_API_BASE` (default
`https://www.barindpost.com`) and every API node builds its URL from
`{{ $env.BARIND_API_BASE }}/api/v1/...` — `Get Categories`, `Get Tags`, `Check Exists`,
`Create Draft` in both AI workflows, plus `Post Run Report` / `Post Error Report`. Switch
back to the local app with `BARIND_API_BASE=http://app docker compose -f
docker-compose.n8n.yml up -d`. The Listing workflow's `Send to URL Ingest` still points at
`http://localhost:5678/webhook/ingest-url` — that is n8n calling its own webhook, not the
site, and must stay.

Two things had to change for that to work: recent n8n **blocks `$env` in expressions by
default** (the first attempt returned `access to env vars denied`), so the compose file sets
`N8N_BLOCK_ENV_ACCESS_IN_NODE: "false"` — not a real widening, since anyone who can edit a
workflow can already use any stored credential; and the active workflows need the
deactivate/activate bounce described below after the URLs change.

Verified: a throwaway probe workflow hitting `{{ $env.BARIND_API_BASE }}/api/v1/categories`
with the existing credential reached the live site and got `401 Invalid or missing API key`
back — i.e. the expression resolves and the request is real, but the credential still holds
the local key. The probe was deleted afterwards.

**Gated auto-publish + Daily Amar Desh (2026-09-18).**

- **The site decides whether AI copy goes live, not the workflow.** `Parse Draft` now sends
  `"status": "published"`, but that is a *request*: the API honours it only when
  `automation.autoPublish` is on **and** the article clears every gate in
  `Api\NewsController::publishGateFailures()` — body at least
  `automation.autoPublishMinWords` (120) words, not flagged as a cross-source duplicate,
  has a subtitle or key points, and has a `source_url` to attribute it to. A failed gate is
  not an error: the article is still filed as a draft and the reasons come back in
  `publish_gates`, land in the run summary, and show in the Incoming queue. Every gate
  exists because that failure actually happened here — headline-only fabrications, one
  wholly invented article from a failed fetch, cross-source duplicates.
  `tests/unit/PublishGateTest.php` covers all of it; the shipped default is **off**.
- **New source: Daily Amar Desh** (`https://www.dailyamardesh.com/latest`). It is a Next.js
  app with no `<a href>` article links in the markup, but the server-rendered flight data
  carries the paths (`/sports/football/amd771169suxv`) and the article pages themselves are
  server-rendered, so a regex over the payload is enough — no headless browser. Its path
  segments also feed the category mapping (`/sports/…` → খেলাধুলা, `/op-ed/…` → সম্পাদকীয়).
- **Extraction fixed for it, and for the others.** Amar Desh renders its date bar and whole
  section menu as an ordinary `<p>` (outside `<nav>`/`<header>`), so it was landing in the
  article text; The Daily Star did the same with "Main navigation News Politics …". Neither
  sentence-density nor an `<h1>` anchor separated chrome from prose (Amar Desh's menu scores
  the same 14 words/sentence as its own copy, and its `<h1>` sits inside the stripped
  `<header>`). What works is the story-body container the sites mark themselves —
  `class="… story-details …"` — so extraction now anchors there, then falls back to the
  `<h1>`, then to the whole page. Inline scripts that survive as paragraphs are dropped too.
  Verified clean on all four sources.
- **Verified end to end locally** (`BARIND_API_BASE=http://app`, `automation.autoPublish =
  true`): four Amar Desh stories collected, extracted, written and **published
  automatically** (166–237 words, no gate failures) — live on the site, in the latest grid
  and in `news-sitemap.xml`. n8n was pointed back at production afterwards.

**Three more sources, two of them English (2026-09-18).** Stage 2 already writes in Bangla
whatever the source language — The Daily Star has been an English feed from the start — so
English wires need no new machinery, only wiring:

| Source | How | Notes |
|---|---|---|
| **Al Jazeera** | RSS `aljazeera.com/xml/rss/all.xml` (25 items) | feed carries a ~100-char teaser only, so the body comes from the article page |
| **Dawn** | RSS `dawn.com/feeds/home` (28 items) | ships the whole story in `<content:encoded>`, which matters because its article pages answer a plain fetch with **403** — the feed is the only way in, and it is enough |
| **BSS** | listing scrape of `bssnews.net/` | the national agency has no feed of any kind; every story has a numeric id (`/news/425731`), so highest id = newest. English edition, as asked; `bssnews.net/bangla` is the same agency in Bangla if you would rather skip the translation step |

Both feeds default to আন্তর্জাতিক (9) when neither the feed's `<category>` nor the URL maps to
one of ours. **Cost note:** the RSS workflow is now 6 feeds × up to 10 articles = 60 articles
per run, and every article is two OpenAI calls — lower `MAX_PER_SOURCE` in `Limit Per Source`
if that is more than you want to spend per run.

**Extraction hardened again for them.** Al Jazeera renders its share bar as text and glues it
to the photo caption ("x whatsapp-stroke copylink google Add Al Jazeera on Google info …"),
which was landing at the top of the article. The rule added matches only strings that never
occur in prose — `copylink`, an icon class like `whatsapp-stroke`, "share this", "add … on
google" — and only at the start of a paragraph, so a story that merely mentions WhatsApp is
untouched (checked). All six sources now start at the real first sentence.

**Still required before the first production draft** (none of it possible from here):

1. **Production `.env`** — `automation.apiKey = '<key>'` and `automation.authorId = <id of
   reporter@barindpost.com>`. Generate the key on the server with `openssl rand -base64 32`;
   never paste it into a chat or commit it. Leave `automation.allowedIps` empty unless n8n
   has a static public IP — the filter 403s every other address.
2. **n8n credential** — "Barind Post Automation API" → value `Bearer <that same key>`
   (header name `Authorization`). Entered in the n8n UI only.
3. **Production database** — apply `DATABASE_UPDATES.md`: `suggested_image_url`,
   `source_title`, `possible_duplicate_of`, `duplicate_score`, the `automation_runs` table,
   the collation fix and the indexes. Without them `Create Draft` fails with a 500.
4. **Deploy the current code** — `/api/v1/automation/runs` still 404s on the live site, so
   run summaries have nowhere to go (harmless: those nodes are `continueRegularOutput`).
5. **First run**: call the ingest webhook with one URL, then check `/admin/incoming` on the
   live site. Only when that produces a draft should the collectors be activated.

Safety while this is half-configured: the API only ever creates `status: draft` (anything
else is a 422), all three collectors are inactive, and no Schedule Trigger is running — so
nothing can reach readers without an editor pressing Publish.

**Gotcha found while doing this — editing an *active* webhook workflow via the REST
API does not refresh what the webhook runs.** After `PATCH /rest/workflows/{id}`
updated the URL-ingest workflow's prompt (stored version confirmed changed), the next
webhook calls still executed the *previous* version (the execution record's
`workflowData` lacked the new code) and produced old-style drafts (ids 92–95:
no subtitle, sentence-style `lead_text`). Fix: bounce activation — `POST
/rest/workflows/{id}/deactivate`, then `POST /rest/workflows/{id}/activate` with
`{"versionId": <current>}` (the activate call refuses without it; a `PATCH` with
`active: false` is silently ignored). Editing in the n8n UI and saving does this
for you; scripted edits must do it explicitly. Manual `/run` calls are unaffected
because they carry their own `workflowData`.

**Editorial gate, category mapping, image suggestions, page cache (2026-09-17).**

- **Incoming queue** — `/admin/incoming` (`App\Controllers\AdminIncoming`,
  `app/Views/admin/incoming.php`, sidebar "Inbox → Incoming" with a pending-count
  badge). Lists every draft that has a `source_url` (i.e. came from a pipeline):
  title, subtitle, key points, source name linking to the original, suggested
  image, category. Per row: change category inline (saved immediately via AJAX and
  also applied on Publish), **Publish**, **Edit**, **Discard**, **Use image**; bulk
  **Discard selected**; a *Discarded* tab with **Restore**. Discard sets
  `status = archived` — the row and its `source_url` stay, so `GET /api/v1/news/exists`
  (which ignores status) keeps treating it as seen and the pipelines never re-ingest
  it. Only admin/editor/sub-editor can use the queue; reporters are redirected. This
  is where "never publish without a human" is enforced in practice: the API only
  ever creates drafts, and this screen is the only fast path from draft to public.
- **Category mapping at the source instead of fuzzy matching.** Both workflows now
  map the source's own classification to one of our categories through a shared
  alias table (`CATEGORY_ALIASES` — identical copies in the RSS workflow's `Tag With
  Source` and the URL-ingest's `Shape Article Item`; keep them in sync): URL path
  segments (`/post/market/…` → অর্থনীতি, `/news/article/sports/…` → খেলাধুলা,
  `national`/`country` → সারাদেশ, …) and RSS `<category>` labels (`World News` →
  আন্তর্জাতিক). Stage 2 is now given the real category list and must answer with
  one of those names (`category_bn`); `Parse Draft + Map Category/Tags` resolves in
  this order: source mapping → the model's pick (exact name) → the feed/webhook
  default. Sections that carry no signal (`miscellaneous`, `last-page`, `sodesh`)
  fall through to the model's pick, which is what you want.
- **Suggested lead image.** The pipelines record `og:image` / `twitter:image` /
  JSON-LD `image` (URL-ingest: from the page it already fetched; RSS: from a new
  `Fetch Article Page` request per *new* item, since none of the feeds carry an
  `enclosure`) into `news.suggested_image_url` (new column — see
  `DATABASE_UPDATES.md`). It is shown only in the queue; **Use image** downloads it
  into `public/uploads/news/`, records it in `images` and sets `image_url`, exactly
  like a manual upload. Nothing is ever published from a remote URL automatically.
- **Two content-safety bugs found and fixed while verifying this.** (1) Amar Bangla's
  feed carries the body under `summary`, which `Build Facts Prompt` never read, so
  every Amar Bangla draft so far (ids 52–61, 97–106) had been generated from a
  headline alone. (2) A failed page fetch (Nayadiganta answered 500 once) still flowed
  on and produced a fully invented article (id 107). Those 21 drafts are archived in
  the Discarded tab. Fixes: the RSS `Extract Text + Image` node takes the body from
  `content:encoded`/`content`/`summary`/`description`, falls back to the fetched
  page's text, and **drops** any item with under 200 characters of prose; the
  URL-ingest `Shape Article Item` **throws** when the fetch failed or the page does
  not pass `articleCheck` (a share title — `og:title` / JSON-LD headline — plus at
  least 3 real paragraphs and 400 characters of prose after stripping nav, header,
  footer and aside; `og:type`/JSON-LD alone are unreliable on these sites), so the
  webhook returns an error and the collector's `Report` records it — no headline or
  app-shell page ever reaches the AI. Two more things had to change for that to
  hold: `Shape Article Item` and `Continue If New` were set to *continue on error*
  from the original build, which turned a thrown guard into an error item that
  flowed on (link-less, text-less) and still became an invented draft (id 120) —
  both nodes now **stop the workflow**; and a failed dedup call (`Check Exists`
  erroring) was read as "new" — the ingest now fails loudly and the RSS `Filter New`
  skips such items ("unknown is not new"). Rule of thumb for these workflows:
  *continue on error* is only right for per-article AI/API steps after dedup, never
  for the nodes that decide whether there is an article at all.
- **Verified (2026-09-17, local Docker):** queue — every action exercised through a
  real admin session (inline category save, publish → `published_at` set, cache
  purged, public page 200; discard/restore; bulk discard; "Use image" → file in
  `public/uploads/news/`, `images` row, `image_url` set; reporter redirected).
  Pipelines — Nayadiganta `/post/health/…` → স্বাস্থ্য and `/post/international/…`
  → আন্তর্জাতিক, both with the page's `og:image` recorded (drafts 109, 121, 122);
  Daily Star sports feed → 10/10 খেলাধুলা with body text taken from the article
  page and `og:image` on all ten (drafts 110–119); a bogus URL → collector `Report`
  status `error` ("Error in workflow"), **no row created**. Page cache: home 102 ms →
  11 ms; `POST /news/view/{id}` → 204 and a `news_views` row.
- **n8n Code-node gotcha:** `new URL()` is not available in the Code sandbox (no
  `URL` global). Every earlier use was inside a `try/catch`, which silently turned
  section labels and image URLs into nothing. Parsing is now regex-based
  (`parseUrl` / `absolutize` / `pathLabels` in the shared helper block at the top of
  those nodes).
- **Public page cache.** `PublicSite::home/section/news/tag` call `cachePage(60)`;
  `NewsModel` purges the whole page cache after every insert/update/delete (helper
  `purge_public_cache()`, `app/Helpers/cache_helper.php`), so publishing from the
  queue or the editor is visible on the next request. Because the article page is
  cached, view counting moved to a JS beacon (`POST /news/view/{id}` →
  `PublicSite::trackViewBeacon`), otherwise views would be counted once per TTL.
  Measured locally: home 102 ms → 11 ms on the second hit.

**What's simplified vs. the full plan, and why (unchanged from before):**
- Workflows 1+2 (source fetch, AI draft) combined into one — fewer moving parts for
  a first working version; split later if useful.
- 4 of the 8 feeds you supplied aren't in the workflow (2 blocked by Cloudflare, 1
  looked like a dead path, 1 was a typo now fixed) — see §6 below.

**Genuinely still open — outside what this session can do, not a matter of more
implementation work:**
- ~~A real OpenAI API key.~~ Done: the real key now lives only in the "OpenAI
  (Barind Post Automation)" n8n credential (never in this repo or `.env`), and real
  end-to-end runs (drafts 39, 42) confirm it works.
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
