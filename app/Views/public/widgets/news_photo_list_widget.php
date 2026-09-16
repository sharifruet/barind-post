<?php
/**
 * News Photo List Widget
 * A reusable widget for displaying news items with small photos on the left and titles on the right
 * 
 * Usage: <?= view('public/widgets/news_photo_list_widget', ['newsItems' => $newsArray, 'title' => 'Latest News']) ?>
 * 
 * @param array $newsItems - Array of news items to display
 * @param string $title - Title for the widget (default: 'আরও সর্বশেষ সংবাদ')
 * @param int $maxItems - Maximum number of items to display (default: 15)
 * @param string $emptyMessage - Message to show when no news items (default: 'আরও সংবাদ নেই')
 * @param string $imageSize - Size class for images (default: 'small-photo')
 */

// Set default values
$title = $title ?? 'আরও সর্বশেষ সংবাদ';
$maxItems = $maxItems ?? 15;
$emptyMessage = $emptyMessage ?? 'আরও সংবাদ নেই';
$imageSize = $imageSize ?? 'small-photo';

// Limit news items if needed
$displayNews = array_slice($newsItems, 0, $maxItems);
?>

<div class="latest-news-sidebar">
    <h4 class="mb-3 text-primary"><?= esc($title, 'raw') ?></h4>
    <div class="list-group list-group-flush">
        <?php if (!empty($displayNews)): ?>
            <?php foreach ($displayNews as $index => $news): ?>
                <a href="/news/<?= esc($news['slug']) ?>" class="list-group-item list-group-item-action border-0 px-0 py-2">
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
                            <small class="text-muted">
                                <?= date('M d, Y', strtotime($news['published_at'])) ?>
                            </small>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center text-muted py-3">
                <i class="fas fa-newspaper"></i>
                <p class="mb-0"><?= esc($emptyMessage, 'raw') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>
