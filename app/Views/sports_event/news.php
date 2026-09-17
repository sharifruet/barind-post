<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<h3 class="mb-4">সংবাদ</h3>
<div class="row g-4">
    <?php foreach ($news as $n): ?>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <?php if ($n['image_url']): ?>
                    <img src="<?= esc(get_image_url($n['image_url'])) ?>" class="card-img-top" style="aspect-ratio:16/9;object-fit:cover" alt="">
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title bengali-text">
                        <a href="/news/<?= esc($n['slug']) ?>" class="text-decoration-none text-dark"><?= esc($n['title']) ?></a>
                    </h5>
                    <?php $summary = story_excerpt($n, 18); ?>
                    <?php if ($summary): ?><p class="card-text text-muted small bengali-text"><?= esc($summary) ?></p><?php endif; ?>
                    <small class="text-muted"><?= $n['published_at'] ? date('M d, Y', strtotime($n['published_at'])) : '' ?></small>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php if (empty($news)): ?><p class="text-muted">এখনো কোনো সংবাদ নেই।</p><?php endif; ?>
<?= $this->endSection() ?>
