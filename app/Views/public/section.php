<?php
// Title is now set in the controller
$customStyles = '
        .category-pill {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .category-pill.active, .category-pill:focus, .category-pill:active {
            background: #0d6efd;
            color: #fff !important;
            border-color: #0d6efd;
        }
        
        /* Sidebar news styles */
        .latest-news-sidebar {
            background: #f8f9fa;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .latest-news-sidebar .list-group-item {
            border-left: 3px solid transparent;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .latest-news-sidebar .list-group-item:last-child {
            border-bottom: none;
        }
        
        .latest-news-sidebar .list-group-item:hover {
            border-left-color: #007bff;
            background-color: #fff;
            transform: translateX(5px);
        }
        
        .latest-news-sidebar .badge {
            font-size: 0.7rem;
            min-width: 20px;
        }
        
        /* Column borders for news items */
        .news-card {
            border-right: 1px solid #e9ecef !important;
            border-bottom: 1px solid #e9ecef !important;
        }
        
        /* Remove right border from last column in each row */
        .col-md-4:nth-child(3n) .news-card {
            border-right: none !important;
        }
        
        /* Remove bottom border from last row */
        .row:last-child .news-card {
            border-bottom: none !important;
        }
';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>

<?php
// Function to limit text to first 15 words
function limitTo15Words($text) {
    if (empty($text)) return '';
    
    $words = preg_split('/\s+/', trim($text));
    if (count($words) <= 15) {
        return $text;
    }
    
    $limitedWords = array_slice($words, 0, 15);
    return implode(' ', $limitedWords) . '...';
}
?>
<div class="container">
    <!-- Top Banner Ad -->
    <?php 
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    ?>
    <div class="mb-4">
        <a href="/section/<?= esc($category['slug']) ?>" class="btn btn-primary category-pill rounded-pill px-3 py-1 active">
            <?= esc($category['name']) ?>
        </a>
    </div>
    <h2 class="mb-4">Section: <?= esc($category['name']) ?></h2>
    <div class="row">
        <!-- Left Column - 8 columns for news with photos -->
        <div class="col-md-8">
            <div class="row g-4 mb-5">
                <?php 
                // Get first 6 news items for left column
                $leftColumnNews = array_slice($news, 0, 6);
                foreach ($leftColumnNews as $item): ?>
                    <div class="col-md-4">
                        <div class="card news-card h-100 border-0 shadow-sm">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= esc(get_image_url($item['image_url'])) ?>" class="card-img-top news-img" alt="<?= esc($item['image_alt_text'] ?? '') ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="/news/<?= esc($item['slug']) ?>" class="text-decoration-none text-dark fw-semibold"><?= esc($item['title']) ?></a>
                                </h5>
                                <p class="card-text small text-muted mb-1">
                                    <?= date('M d, Y', strtotime($item['published_at'])) ?>
                                </p>
                                <p class="card-text">
                                    <?= esc(limitTo15Words($item['lead_text'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Column - 4 columns for news titles only -->
        <div class="col-md-4">
            <div class="latest-news-sidebar">
                <h4 class="mb-3 text-primary">আরও <?= esc($category['name']) ?> সংবাদ</h4>
                <div class="list-group list-group-flush">
                    <?php 
                    // Get remaining news items for right sidebar
                    $rightColumnNews = array_slice($news, 6, 15);
                    if (!empty($rightColumnNews)): ?>
                        <?php foreach ($rightColumnNews as $index => $item): ?>
                            <a href="/news/<?= esc($item['slug']) ?>" class="list-group-item list-group-item-action border-0 px-0 py-2">
                                <div class="d-flex align-items-start">
                                    <span class="badge bg-secondary me-2 mt-1"><?= $index + 1 ?></span>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 fw-semibold text-dark"><?= esc($item['title']) ?></h6>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-newspaper"></i>
                            <p class="mb-0">আরও সংবাদ নেই</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bottom Banner Ad -->
    <?php 
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    ?>
</div>
<?= $this->endSection() ?> 