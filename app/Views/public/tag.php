<?php
$title = 'Tag: ' . esc($tag['name']) . ' - বারিন্দ পোস্ট';
$customStyles = '
        .tag-pill {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
            background: #0d6efd;
            color: #fff;
            border-color: #0d6efd;
        }
';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <!-- Top Banner Ad -->
    <?php 
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    ?>
    <div class="mb-4">
        <span class="btn tag-pill rounded-pill px-3 py-1 disabled">
            #<?= esc($tag['name']) ?>
        </span>
    </div>
    <h2 class="mb-4">Tag: <?= esc($tag['name']) ?></h2>
    <div class="row g-4">
        <?php foreach ($news as $item): ?>
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
                            <?= esc($item['lead_text']) ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
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