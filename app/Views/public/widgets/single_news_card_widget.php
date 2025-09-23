<?php
/**
 * Single News Card Widget
 * A reusable widget for displaying a single news item as a card
 * 
 * Usage: <?= view('public/widgets/single_news_card_widget', ['news' => $newsItem, 'imageHeight' => 150]) ?>
 * 
 * @param array $news - Single news item array
 * @param string $cardClass - CSS class for the card (default: 'card news-card h-100 border-0 shadow-sm')
 * @param string $imageClass - CSS class for the image (default: 'card-img-top news-img')
 * @param bool $showDate - Whether to show publication date (default: true)
 * @param bool $showLead - Whether to show lead text (default: true)
 * @param string $titleClass - CSS class for the title (default: 'card-title')
 * @param int $imageHeight - Height for the image in pixels (default: 200)
 */

// Set default values
$cardClass = $cardClass ?? 'card news-card h-100 border-0 shadow-sm';
$imageClass = $imageClass ?? 'card-img-top news-img';
$showDate = $showDate ?? true;
$showLead = $showLead ?? true;
$titleClass = $titleClass ?? 'card-title';
$imageHeight = $imageHeight ?? 200;

// Ensure we have a news item
if (empty($news)) {
    return;
}
?>

<div class="<?= esc($cardClass) ?>">
    <?php if (!empty($news['image_url'])): ?>
        <img src="<?= esc(get_image_url($news['image_url'])) ?>" 
             class="<?= esc($imageClass) ?>" 
             alt="<?= esc($news['image_alt_text'] ?? '') ?>"
             style="height: <?= $imageHeight ?>px; object-fit: cover;">
    <?php endif; ?>
    <div class="card-body">
        <h5 class="<?= esc($titleClass) ?>">
            <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none text-dark fw-semibold">
                <?= esc($news['title'], 'raw') ?>
            </a>
        </h5>
        <?php if ($showDate): ?>
            <p class="card-text small text-muted mb-1">
                <?= date('M d, Y', strtotime($news['published_at'])) ?>
            </p>
        <?php endif; ?>
        <?php if ($showLead && !empty($news['lead_text'])): ?>
            <p class="card-text">
                <?= esc(limitTo15Words($news['lead_text']), 'raw') ?>
            </p>
        <?php endif; ?>
    </div>
</div>
