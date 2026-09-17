<?php
$title = ($query ? $query . ' — ' : '') . 'অনুসন্ধান - বারিন্দ পোস্ট';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="page-head">
        <div class="page-head__eyebrow">অনুসন্ধান</div>
        <?php if ($query): ?>
            <h1 class="page-head__title">“<?= esc($query, 'raw') ?>”</h1>
            <p class="page-head__note"><?= esc(bn_number(count($news))) ?>টি ফলাফল পাওয়া গেছে</p>
        <?php else: ?>
            <h1 class="page-head__title">সংবাদ খুঁজুন</h1>
            <p class="page-head__note">অনুসন্ধান করতে উপরের বক্সে শব্দ লিখুন</p>
        <?php endif; ?>
    </div>

    <?php if ($query && ! empty($news)): ?>
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
    <?php elseif ($query): ?>
        <div class="empty-state">
            <i class="fas fa-magnifying-glass d-block"></i>
            <p class="mb-3">কোনো ফলাফল পাওয়া যায়নি। অন্য শব্দ দিয়ে চেষ্টা করুন অথবা বিভাগ থেকে পড়ুন।</p>
            <div class="chips justify-content-center">
                <?php foreach (($categories ?? []) as $cat): ?>
                    <a class="chip" href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="chips">
            <?php foreach (($categories ?? []) as $cat): ?>
                <a class="chip" href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
