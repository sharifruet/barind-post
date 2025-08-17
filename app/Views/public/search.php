<?php
$title = 'Search Results - বারিন্দ পোস্ট';
$customStyles = '
        .search-highlight {
            background: #fff3cd;
            padding: 0.1rem 0.2rem;
            border-radius: 0.2rem;
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
        <h2>Search Results</h2>
        <?php if ($query): ?>
            <p class="text-muted">Searching for: "<strong><?= esc($query) ?></strong>"</p>
            <p class="text-muted">Found <?= count($news) ?> result<?= count($news) != 1 ? 's' : '' ?></p>
        <?php else: ?>
            <p class="text-muted">Enter a search term to find news articles</p>
        <?php endif; ?>
    </div>

    <?php if ($query && !empty($news)): ?>
        <div class="row g-4">
            <?php foreach ($news as $item): ?>
                <div class="col-md-4">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= esc(get_image_url($item['image_url'])) ?>" class="card-img-top news-img" alt="<?= esc($item['image_alt_text'] ?? '') ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="/news/<?= esc($item['slug']) ?>" class="text-decoration-none text-dark fw-semibold">
                                    <?= esc($item['title']) ?>
                                </a>
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
    <?php elseif ($query && empty($news)): ?>
        <div class="text-center py-5">
            <h4 class="text-muted">No results found</h4>
            <p class="text-muted">Try different keywords or browse our categories</p>
            <div class="mt-3">
                <?php foreach ($categories as $cat): ?>
                                    <a href="/section/<?= esc($cat['slug']) ?>" class="btn btn-outline-danger me-2 mb-2">
                    <?= esc($cat['name']) ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Bottom Banner Ad -->
    <?php 
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    ?>
</div>
<?= $this->endSection() ?> 