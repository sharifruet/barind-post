<?php
$title = $news['title'] . ' - বারিন্দ পোস্ট';
$customStyles = '
        .news-img {
            border-radius: 1rem;
            object-fit: cover;
            max-height: 400px;
            width: 100%;
        }
        .back-btn {
            border-radius: 2rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
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
    <div class="row justify-content-center">
        <div class="col-md-8">
            <a href="javascript:history.back()" class="btn btn-outline-primary back-btn mb-3">&larr; Back</a>
            <h1 class="mb-2 mt-3 fw-bold display-5"><?= $news['title'] ?></h1>
            <?php if (!empty($news['subtitle'])): ?>
                <h4 class="text-muted mb-3 fw-normal"><?= $news['subtitle'] ?></h4>
            <?php endif; ?>
            <p class="text-muted small mb-2">
                <?= date('M d, Y', strtotime($news['published_at'])) ?>
            </p>
            <?php if (!empty($news['image_url'])): ?>
                <img src="<?= esc($news['image_url']) ?>" class="news-img mb-3 shadow-sm" alt="">
            <?php endif; ?>
            <div class="mb-3 fs-5">
                <strong><?= $news['lead_text'] ?></strong>
            </div>
            <div class="mb-4 fs-5 lh-lg">
                <?= $news['content'] ?>
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