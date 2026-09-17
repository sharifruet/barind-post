# Database Updates for Featured News Functionality

## Overview
This document outlines the database updates needed to support the featured/unfeatured news functionality.

## Current Database Structure
The `dbscript.sql` file already includes the correct structure for the news table with the `featured` field:

```sql
CREATE TABLE IF NOT EXISTS news (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    subtitle VARCHAR(255),
    lead_text TEXT,
    content TEXT NOT NULL,
    author_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED,
    status ENUM('draft', 'published', 'archived') NOT NULL DEFAULT 'draft',
    featured BOOLEAN NOT NULL DEFAULT FALSE,  -- This field is already included
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    published_at DATETIME NULL,
    image_url VARCHAR(255),
    slug VARCHAR(255),
    source VARCHAR(255),
    dateline VARCHAR(255),
    word_count INT UNSIGNED,
    language VARCHAR(5) NOT NULL DEFAULT 'bn',
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## For Existing Databases
If you have an existing database that doesn't have the `featured` column, run the `update_featured_column.sql` script:

```bash
mysql -u your_username -p your_database_name < update_featured_column.sql
```

## Sample Data
The `dbscript.sql` already includes sample news articles with featured status:

- Some articles have `featured = 1` (featured)
- Some articles have `featured = 0` (not featured)

## Verification
To verify the database structure is correct, run:

```sql
DESCRIBE news;
```

You should see the `featured` column with type `BOOLEAN` and default value `FALSE`.

## Testing the Featured Functionality
1. The news list page (`/admin/news`) will show featured status
2. You can toggle featured status using the toggle button
3. The news form includes a featured checkbox
4. Featured news can be displayed differently on the public site

## No Migration Required
Since you're using database scripts rather than CodeIgniter migrations, simply:
1. Use the existing `dbscript.sql` for new installations
2. Run `update_featured_column.sql` for existing databases that need the featured column 
---

# Lead text → key points (2026-09-17)

## What changed
No schema change. The two summary columns on `news` changed meaning:

| Column | Before | Now |
|---|---|---|
| `subtitle` (VARCHAR 255) | rarely used | **the standfirst** — one sentence under the headline; also the card excerpt and the meta / Open Graph / Twitter description |
| `lead_text` (TEXT) | the standfirst | **key points** — one per line, rendered as an “এক নজরে” bullet box above the body (`key_points()` in `app/Helpers/text_helper.php`). A single line is rendered as a plain intro paragraph, so legacy rows still look right. |

Fallback order everywhere (`story_excerpt()`, `seo_description()`): subtitle → key points → content/title.
The automation API accepts `lead_text` as a string or an array of points (also under the alias `key_points`).

## Data migration (run once on production)
Existing articles have a one-sentence `lead_text` and, mostly, no `subtitle`. Move that sentence
into `subtitle` so it keeps its standfirst/SEO role; rows that already have a subtitle are left alone
(their single-line `lead_text` still renders as a paragraph):

```sql
UPDATE news
SET    subtitle = lead_text, lead_text = NULL
WHERE  (subtitle IS NULL OR subtitle = '')
  AND  lead_text IS NOT NULL AND lead_text <> ''
  AND  lead_text NOT LIKE '%\n%'
  AND  CHAR_LENGTH(lead_text) <= 255;
```

`dbscript.sql` needs no change: its sample rows already carry both a subtitle and a lead sentence.

---

# Suggested lead image for automation drafts (2026-09-17)

## What changed
New nullable column on `news`: `suggested_image_url VARCHAR(500)`. The n8n pipelines record the
source page's `og:image` / `twitter:image` / first article image there as a **suggestion** for the
editor, shown in the admin **Incoming** queue (`/admin/incoming`). It is never rendered on the
public site: the editor adopts it with one click ("Use image"), which downloads the file into
`public/uploads/news/`, records it in `images`, and sets `image_url` — the same as a manual upload.

Also in `dbscript.sql` and as migration `2026-09-17-000000_add_suggested_image_to_news.php`.

## Run once on production
```sql
ALTER TABLE news ADD COLUMN suggested_image_url VARCHAR(500) NULL AFTER content_hash;
```
