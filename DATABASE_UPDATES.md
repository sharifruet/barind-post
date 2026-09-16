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