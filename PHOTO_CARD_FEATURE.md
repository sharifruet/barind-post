# Photo Cards

Social cards for Facebook, Instagram and WhatsApp, made from an article in **admin → Photo
Cards** (`/admin/photo-card-generator`, admins only). The same design also provides the
share image (`og:image`) of articles that have no photo, or of any article whose card an editor
saved as its share image.

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
| `app/Controllers/AdminPhotoCards.php` | Lists articles; `save()` stores the PNG and optionally sets it as the article's share image; `postToFacebook()` posts it to the Facebook Page. |
| `app/Config/Facebook.php` | Page ID and Page access token for posting (`facebook.*` in `.env`). |
| `app/Libraries/PhotoCard.php` | GD fallback for `/og/{id}.png`: a 1200×630 brand card. Draws a Bangla headline only when `canShapeBengali()` says GD can. |
| `app/Helpers/site_helper.php` | `og_image_url($news)` (saved card → photo → generated card: a card the editor saved as the share image beats the article photo), `photo_card_share_path($id)`. |
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

1. Pick the article from the list: the 10 newest (drafts are marked), with page numbers below
   for older ones. Each page comes from `GET /admin/photo-card-generator/articles?page=N`
   (`AdminPhotoCards::articles`, archived articles excluded). Text fields fill from the
   article and can be edited; *Reset text* restores them.
2. Choose template, theme, format. For Photo/Quote cards pick the article photo, upload one, or
   none; adjust crop position and darkening.
3. **PNG/JPG** downloads the card. **Save to site** stores it and shows its URL. Tick *Use as
   this article's share image* before saving to make it the article's `og:image`; if the link
   was shared before, ask Facebook to re-scrape it (the URL gains `?v=<mtime>` so it is new to
   the scraper).

4. **Post card to the Facebook page** publishes the card as a photo post on the Barind Post
   Page. The caption is the *Message* box (it starts as the standfirst plus the key points from
   `lead_text`, as bullets, and can be edited), followed by `বিস্তারিত: <article link>`, which the
   server appends so it is always the real address. Only published articles can be posted (a
   draft's link would not open), and the editor confirms first because the post is public at
   once. `AdminPhotoCards::postToFacebook` makes the Graph API call (`/{page-id}/photos`, the
   same one the n8n pipeline uses) server-side, so the Page token never reaches the browser.
   It needs `facebook.pageId` and `facebook.pageToken` (a long-lived Page token with
   `pages_manage_posts`) in `.env`; without them the button is disabled. Each post is logged
   with the article id, the Facebook post id and the user.

   Facebook's own share dialog (`sharer.php`) was not used: it cannot attach an image or
   pre-fill the message (Meta's platform policy forbids pre-filled text), so it could only
   share the link with its `og:image`.
5. **Share to your timeline, a Page or a group** is for everything the Page post cannot
   reach: the editor's own profile, groups, and other Pages they manage. The Graph API can
   no longer post to profiles (2018) or groups (2024), so this opens Facebook's Share dialog,
   where the editor picks the destination. The dialog shares a link, so the button first saves
   the card as the article's share image (the same `save` call with `share_image=1`), and the
   server asks Facebook to re-scrape the article (`POST graph.facebook.com/?id=…&scrape=true`
   with the Page token) so the preview shows the new card instead of Facebook's cached one.
   Without `facebook.*` configured that re-scrape is skipped and the page suggests the Sharing
   Debugger. The message box (plus `বিস্তারিত: <link>`) is copied to the clipboard to paste,
   because the dialog cannot be pre-filled. Link previews are 1200×630, so *Link share* is
   the format that fits. Published articles only.

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
