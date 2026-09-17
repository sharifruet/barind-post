<?php
/**
 * Compact list row — thumbnail plus headline. Used in rails and "read more" lists.
 *
 * @param array $news
 * @param bool  $showDate
 * @param bool  $showImage
 */
if (empty($news)) {
    return;
}

$showDate  = $showDate  ?? true;
$showImage = $showImage ?? true;
$hasImage  = $showImage && ! empty($news['image_url']);
$url       = '/news/' . rawurlencode($news['slug'] ?? '');
?>
<article class="story <?= $hasImage ? 'story--row' : 'story--text' ?>">
    <?php if ($hasImage): ?>
        <a class="story__media" href="<?= esc($url, 'attr') ?>" tabindex="-1" aria-hidden="true">
            <img src="<?= esc(get_image_url($news['image_url']), 'attr') ?>"
                 alt="<?= esc($news['image_alt_text'] ?? $news['title'] ?? '', 'attr') ?>"
                 loading="lazy">
        </a>
    <?php endif; ?>

    <div class="story__body">
        <h3 class="story__title">
            <a href="<?= esc($url, 'attr') ?>"><?= esc($news['title'] ?? '', 'raw') ?></a>
        </h3>
        <?php if ($showDate && ! empty($news['published_at'])): ?>
            <div class="story__meta">
                <time datetime="<?= esc($news['published_at'], 'attr') ?>"><?= esc(format_bangla_date($news['published_at']), 'raw') ?></time>
            </div>
        <?php endif; ?>
    </div>
</article>
