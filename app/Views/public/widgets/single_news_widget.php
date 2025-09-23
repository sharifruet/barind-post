<?php
/**
 * Single News Widget
 * A reusable widget for displaying a single news item with photo and title
 * 
 * Usage: <?= view('public/widgets/single_news_widget', ['news' => $newsItem, 'showDate' => true]) ?>
 * 
 * @param array $news - Single news item array
 * @param string $imageSize - Size class for images (default: 'small-photo')
 * @param bool $showDate - Whether to show publication date (default: true)
 * @param bool $showLead - Whether to show lead text (default: false)
 * @param string $linkClass - CSS class for the link (default: 'list-group-item list-group-item-action border-0 px-0 py-2')
 */

// Set default values
$imageSize = $imageSize ?? 'small-photo';
$showDate = $showDate ?? true;
$showLead = $showLead ?? false;
$linkClass = $linkClass ?? 'list-group-item list-group-item-action border-0 px-0 py-2';

// Ensure we have a news item
if (empty($news)) {
    return;
}
?>

<a href="/news/<?= esc($news['slug']) ?>" class="<?= esc($linkClass) ?>">
    <div class="d-flex align-items-start">
        <div class="flex-shrink-0 me-3">
            <?php if (!empty($news['image_url'])): ?>
                <img src="<?= esc(get_image_url($news['image_url'])) ?>" 
                     class="<?= esc($imageSize) ?>" 
                     alt="<?= esc($news['image_alt_text'] ?? '') ?>"
                     style="width: 60px; height: 45px; object-fit: cover; border-radius: 4px;">
            <?php else: ?>
                <div class="<?= esc($imageSize) ?>" 
                     style="width: 60px; height: 45px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-image text-muted"></i>
                </div>
            <?php endif; ?>
        </div>
        <div class="flex-grow-1">
            <h6 class="mb-1 fw-semibold text-dark"><?= esc($news['title'], 'raw') ?></h6>
            <?php if ($showDate): ?>
                <small class="text-muted">
                    <?= date('M d, Y', strtotime($news['published_at'])) ?>
                </small>
            <?php endif; ?>
            <?php if ($showLead && !empty($news['lead_text'])): ?>
                <p class="mb-0 mt-1 small text-muted">
                    <?= esc(limitTo15Words($news['lead_text']), 'raw') ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</a>
