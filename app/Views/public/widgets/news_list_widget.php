<?php
/**
 * News List Widget
 * A reusable widget for displaying news items in a list format
 * 
 * Usage: <?= view('public/widgets/news_list_widget', ['newsItems' => $newsArray, 'showNumbers' => false]) ?>
 * 
 * @param array $newsItems - Array of news items to display
 * @param string $title - Title for the widget (default: 'আরও সর্বশেষ সংবাদ')
 * @param int $maxItems - Maximum number of items to display (default: 15)
 * @param bool $showNumbers - Whether to show item numbers (default: true)
 * @param string $emptyMessage - Message to show when no news items (default: 'আরও সংবাদ নেই')
 */

// Set default values
$title = $title ?? 'আরও সর্বশেষ সংবাদ';
$maxItems = $maxItems ?? 15;
$showNumbers = $showNumbers ?? true;
$emptyMessage = $emptyMessage ?? 'আরও সংবাদ নেই';

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
                        <?php if ($showNumbers): ?>
                            <span class="badge bg-secondary me-2 mt-1"><?= $index + 1 ?></span>
                        <?php endif; ?>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fw-semibold text-dark"><?= esc($news['title'], 'raw') ?></h6>
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
