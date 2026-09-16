# Language Settings - Barind Post

## Overview
Barind Post has been configured to use **Bangla** as the default language throughout the application.

## Changes Made

### 1. Application Configuration (`app/Config/App.php`)
- **Default Locale**: Set to `'bn'` (Bangla)
- **Supported Locales**: `['bn', 'en']` (Bangla first, then English)
- **Timezone**: Set to `'Asia/Dhaka'` (Bangladesh timezone)

### 2. Database Migration (`app/Database/Migrations/2024-06-01-000002_create_news.php`)
- **Language Field Default**: Changed from `'en'` to `'bn'`
- All new news articles will default to Bangla

### 3. News Model (`app/Models/NewsModel.php`)
- Added default values including `'language' => 'bn'`

### 4. Admin Controller (`app/Controllers/Admin.php`)
- Added fallback to set language to `'bn'` (Bangla) if not specified during news creation

### 5. News Form (`app/Views/admin/news_form.php`)
- Reordered language options to show Bangla first
- Bangla is now the default selected option

### 6. Public Layout (`app/Views/public/layout.php`)
- HTML lang attribute: `lang="bn"`
- Meta language tag: `content="bn"`

## Language Options

### News Articles
- **Bangla (bn)**: Default language for all new articles
- **English (en)**: Available as alternative option

### User Interface
- **Admin Panel**: English (for consistency with technical terms)
- **Public Site**: Bangla (primary) with English support

## Running the Update Script

To update existing news articles to Bengali:

```bash
php update_language_defaults.php
```

This script will:
1. Update all news articles without a language set to Bangla
2. Update all news articles currently set to English to Bangla
3. Show the current language distribution

## Language Detection

The application uses the following priority for language detection:
1. **User Selection**: If user explicitly chooses a language
2. **Article Language**: The language set for the specific news article
3. **Default**: Bangla (`bn`)

## Future Considerations

- Consider adding a language switcher for the public site
- Implement multi-language content support for the same article
- Add Bengali language files for system messages and validation

## Files Modified

1. `app/Config/App.php` - Application locale settings
2. `app/Database/Migrations/2024-06-01-000002_create_news.php` - Database default
3. `app/Models/NewsModel.php` - Model defaults
4. `app/Controllers/Admin.php` - Controller defaults
5. `app/Views/admin/news_form.php` - Form defaults
6. `app/Views/public/layout.php` - Public layout language
7. `test_tweet_embed.php` - Test page language
8. `update_language_defaults.php` - Update script (new)
9. `LANGUAGE_SETTINGS.md` - Documentation (new)

## Verification

To verify the changes:
1. Create a new news article - Bangla should be pre-selected
2. Check existing articles - they should default to Bangla
3. View public site - should display in Bangla by default 