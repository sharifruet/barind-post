<?php
/**
 * Story card.
 *
 * @param array  $news       News row
 * @param string $size       lead | feature | medium | compact | row | text
 * @param bool   $showKicker Show the kicker/eyebrow label
 * @param bool   $showDate   Show the publication date
 * @param bool   $showLead   Show the summary line
 * @param bool   $showImage  Show the image (ignored when the row has none)
 */
if (empty($news)) {
    return;
}

$size       = $size       ?? 'medium';
$showKicker = $showKicker ?? true;
$showDate   = $showDate   ?? true;
$showLead   = $showLead   ?? true;
$showImage  = $showImage  ?? true;

// Legacy callers still pass the old size names.
$sizeMap = ['hero' => 'lead', 'large' => 'feature', 'small' => 'compact'];
$size    = $sizeMap[$size] ?? $size;

$url      = '/news/' . rawurlencode($news['slug'] ?? '');
$hasImage = $showImage && ! empty($news['image_url']) && $size !== 'text';
$excerpt  = $showLead ? story_excerpt($news, $size === 'lead' ? 32 : 20) : '';
$kicker   = $showKicker && ! empty($news['kicker']) ? $news['kicker'] : '';

// Text-first card: a story with no photo is not a photo card with a hole in it — it
// earns the space typographically and by showing more of the reporting. Only the sizes
// that have room for it; compact/row/text already hide their summary line.
$isTextFirst = ! $hasImage && in_array($size, ['lead', 'feature', 'medium'], true);
$points      = [];
if ($isTextFirst && $showLead) {
    $points = key_points($news['lead_text'] ?? null);
    if (empty($news['subtitle'])) {
        // No standfirst, so the points are the summary — story_excerpt() already joined
        // them into $excerpt, which would print them twice.
        $excerpt = $points === [] ? $excerpt : '';
        $points  = array_slice($points, 0, $size === 'lead' ? 4 : 3);
    } else {
        $points = array_slice($points, 0, $size === 'lead' ? 3 : 2);
    }
}
?>
<article class="story story--<?= esc($size, 'attr') ?><?= $isTextFirst ? ' story--text-first' : '' ?>">
    <?php if ($hasImage): ?>
        <a class="story__media" href="<?= esc($url, 'attr') ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= esc(get_image_url($news['image_url']), 'attr') ?>"
                 alt="<?= esc($news['image_alt_text'] ?? $news['title'] ?? '', 'attr') ?>"
                 loading="lazy">
        </a>
    <?php endif; ?>

    <div class="story__body">
        <?php if ($kicker !== ''): ?>
            <span class="kicker" style="color: <?= esc($news['kicker_color'] ?? '#c8102e', 'attr') ?>;"><?= esc($kicker, 'raw') ?></span>
        <?php endif; ?>

        <h3 class="story__title">
            <a href="<?= esc($url, 'attr') ?>"><?= esc($news['title'] ?? '', 'raw') ?></a>
        </h3>

        <?php if ($excerpt !== ''): ?>
            <p class="story__lead"><?= esc($excerpt, 'raw') ?></p>
        <?php endif; ?>

        <?php if ($points !== []): ?>
            <ul class="story__points">
                <?php foreach ($points as $point): ?>
                    <li><?= esc($point) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($showDate && ! empty($news['published_at'])): ?>
            <div class="story__meta">
                <time datetime="<?= esc($news['published_at'], 'attr') ?>"><?= esc(format_bangla_date($news['published_at']), 'raw') ?></time>
                <?php if (! empty($news['reporterRole'])): ?>
                    <span class="sep">|</span><span><?= esc($news['reporterRole'], 'raw') ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</article>
