<?php
$title = 'ট্যাগ: ' . $tag['name'] . ' - বারিন্দ পোস্ট';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="page-head">
        <div class="page-head__eyebrow">ট্যাগ</div>
        <h1 class="page-head__title">#<?= esc($tag['name'], 'raw') ?></h1>
        <p class="page-head__note"><?= esc(bn_number(count($news))) ?>টি সংবাদ</p>
    </div>

    <?php if (empty($news)): ?>
        <div class="empty-state">
            <i class="fas fa-tag d-block"></i>
            <p class="mb-0">এই ট্যাগে কোনো সংবাদ নেই।</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($news as $item): ?>
                <div class="col-lg-4 col-md-6">
                    <?= view('public/widgets/news_card_widget', [
                        'news'     => $item,
                        'size'     => 'medium',
                        'showDate' => true,
                        'showLead' => true,
                    ]) ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
