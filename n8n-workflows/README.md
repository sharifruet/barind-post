# n8n workflows

Importable workflow definitions. Most Barind Post workflows live only in the n8n
data volume; the ones checked in here are the ones worth version-controlling.

## facebook-pipeline.json — "Barind Post - Publish to Facebook"

Reads recently published articles from the news site and posts each as a photo
card (title, subtitle, key points, logo, QR to the article, date) to a Facebook
page. It is separate from the news-creation pipelines: the card is a Facebook
artifact and is never shown on the site.

Flow: `Fetch Recent` (GET /api/v1/news/recent) → `Select To Post` (skip already
posted, oldest-first, max 3/run) → `Render Card` (card-renderer service, PNG) →
`Post to Facebook` (Graph API photo post) → `Mark Posted` (advance the marker in
workflow static data).

No AI and no OpenAI cost: the card only lays out text the article already has.

### One-time setup

1. **Facebook**: create a Facebook Page, a Meta app, and a long-lived Page access
   token with `pages_manage_posts`. Note the Page ID.
2. Put both in `.env.n8n` (never commit them):
   ```
   BARIND_FB_PAGE_ID=<page id>
   BARIND_FB_PAGE_TOKEN=<long-lived page token>
   ```
3. Start/refresh the stack so n8n picks up the vars and the card renderer runs:
   ```
   docker compose -f docker-compose.n8n.yml up -d
   ```
4. Import the workflow (in the container, so it lands in the n8n data volume):
   ```
   docker cp n8n-workflows/facebook-pipeline.json barindpost_n8n:/tmp/fb.json
   docker exec barindpost_n8n n8n import:workflow --input=/tmp/fb.json
   ```
5. In the n8n editor, open the workflow and press **Execute workflow** once. The
   first run only seeds the "last posted" marker and posts nothing, so old
   articles are never back-posted. From then on each run posts new articles.
6. To run it automatically, activate the workflow (the `Every 30 min` schedule
   trigger fires it).

### Requires

- The `/api/v1/news/recent` endpoint deployed on the site (app/Controllers/Api/NewsController.php).
- The `card-renderer` service running (docker-compose.n8n.yml).
- `BARIND_FB_PAGE_ID` and `BARIND_FB_PAGE_TOKEN` set in `.env.n8n`.
