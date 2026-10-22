# Photo Cards

Social cards for Facebook, Instagram and WhatsApp, made from an article in **admin → Photo
Cards** (`/admin/photo-card-generator`, admins only). The same design also provides the
share image (`og:image`) of articles that have no photo.

## Why the card is drawn in the browser

PHP's GD cannot shape Bangla. Unless libgd was built with libraqm (it is not in PHP's bundled
GD, so neither Docker nor cPanel), pre-base vowel signs land after their consonant
(গোদাগাড়ী → গ োদাগাড়ী) and conjuncts fall apart. Browsers shape Bangla correctly, so the
card is rendered on a `<canvas>` in the admin page and the finished PNG is uploaded. The
editor sees exactly what is exported.

```
admin page ──canvas (photo-card.js)──▶ PNG ──POST save──▶ public/uploads/photo_cards/
                                                            └─ og-{id}.png  ⇐ "use as share image"
/og/{id}.png  ──▶ og-{id}.png if the editor saved one, else a GD brand card (PhotoCard.php)
```

## Files

| File | Role |
|---|---|
| `public/assets/js/photo-card.js` | The renderer: `renderCard(ctx, spec)`, `FORMATS`, `TEMPLATES`, text fitting, cover-crop. Pure canvas 2D — runs unchanged in the browser and in Node (`@napi-rs/canvas`). |
| `public/assets/js/photo-card-admin.js` | The admin page: article picker, controls, live preview, download, save. |
| `app/Views/admin/photo_card_generator.php` | Markup, `@font-face` for the shipped fonts, JSON config for the script. |
| `app/Controllers/AdminPhotoCards.php` | Lists articles; `save()` stores the PNG and optionally sets it as the article's share image. |
| `app/Libraries/PhotoCard.php` | GD fallback for `/og/{id}.png`: a 1200×630 brand card. Draws a Bangla headline only when `canShapeBengali()` says GD can. |
| `app/Helpers/site_helper.php` | `og_image_url($news)` (photo → saved card → generated card), `photo_card_share_path($id)`. |
| `public/assets/fonts/` | Noto Serif Bengali 700/800, Noto Sans Bengali 400/600/700 (SIL OFL, `OFL.txt`). Used by the browser (`@font-face`) and by GD. |
| `public/uploads/photo_cards/` | Saved cards (git-ignored). `og-{id}.png` is article `{id}`'s share image. |

## Templates and formats

Templates: **Photo** (full-bleed photo under a dark gradient, section tag, serif headline,
optional standfirst), **Headline** (paper or ink background, headline, standfirst, up to four
key points — points that do not fit are dropped from the end, then the standfirst), **Quote**
(large quotation mark, quote, attribution; optional photo backdrop). Any template can carry a
red *ব্রেকিং নিউজ* band.

Formats: link share 1200×630, square 1080×1080, portrait 1080×1350, story 1080×1920. The
layout uses one design unit (`u = min(W, 1.25·H) / 1080`), and headlines auto-fit: the font
shrinks until the text fits the allowed lines and height.

Brand: colours and type follow `theme.css` (ink `#14140f`, accent `#c8102e`, Noto Serif
Bengali headlines, Noto Sans Bengali text). Site name, URL and social handle come from
`Config\SiteInfo` via the controller.

## Using it

1. Pick the article (search box filters the list; drafts are marked). Text fields fill from the
   article and can be edited; *Reset text* restores them.
2. Choose template, theme, format. For Photo/Quote cards pick the article photo, upload one, or
   none; adjust crop position and darkening.
3. **PNG/JPG** downloads the card. **Save to site** stores it and shows its URL. Tick *Use as
   this article's share image* before saving to make it the article's `og:image`; if the link
   was shared before, ask Facebook to re-scrape it (the URL gains `?v=<mtime>` so it is new to
   the scraper).

The article photo is loaded from the page's own origin, so the canvas stays exportable. A
photo hosted on another site cannot be used (the browser would block the export); upload it
instead.

## Changing the design

Edit `public/assets/js/photo-card.js` and check the result without a browser:

```bash
cd card-renderer && npm ci            # @napi-rs/canvas, already used by the Facebook pipeline
node -e "…"                           # register public/assets/fonts as CardSerif/CardSans, call renderCard, write PNG
```

Keep `TEMPLATES`/`FORMATS` keys in step with `AdminPhotoCards::save()`'s file naming and with
this document. The GD fallback in `PhotoCard.php` mirrors the look (ink, red band, logo chip,
footer rule) but must never print Bangla when `canShapeBengali()` is false.

## Docker note

The Docker site serves `/public/uploads/...` from `public/public/uploads/`, a separate directory,
so a saved card's public URL is only correct on the live (cPanel) layout. The share-image path
is unaffected: `/og/{id}.png` reads `og-{id}.png` from disk.
