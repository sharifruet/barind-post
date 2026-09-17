<?php
// Meta tags come from the controller.
$stories   = $news ?? [];
$lead      = array_shift($stories);
$gridItems = array_slice($stories, 0, 8);
$railItems = array_slice($stories, 8, 10);
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="page-head">
        <div class="page-head__eyebrow">বিভাগ</div>
        <h1 class="page-head__title"><?= esc($category['name'], 'raw') ?></h1>
    </div>

    <?php if (! $lead): ?>
        <div class="empty-state">
            <i class="fas fa-newspaper d-block"></i>
            <p class="mb-0">এই বিভাগে এখনো কোনো সংবাদ প্রকাশিত হয়নি।</p>
        </div>
    <?php else: ?>
        <div class="row g-4 g-lg-5">
            <div class="col-lg-8">
                <div class="lead-block">
                    <?= view('public/widgets/news_card_widget', [
                        'news'     => $lead,
                        'size'     => 'lead',
                        'showDate' => true,
                        'showLead' => true,
                    ]) ?>
                </div>

                <?php if (! empty($gridItems)): ?>
                    <div class="row g-4">
                        <?php foreach ($gridItems as $item): ?>
                            <div class="col-md-6">
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

            <aside class="col-lg-4 rail">
                <?php if (! empty($railItems)): ?>
                    <div class="rail-module">
                        <div class="rail-head">আরও <?= esc($category['name'], 'raw') ?></div>
                        <?php foreach ($railItems as $item): ?>
                            <?= view('public/widgets/single_news_widget', ['news' => $item]) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="rail-module">
                    <?= view('public/widgets/prayer_times_widget') ?>
                </div>

                <?php if (! empty($categories)): ?>
                    <div class="rail-module">
                        <div class="rail-head">অন্যান্য বিভাগ</div>
                        <div class="chips">
                            <?php foreach ($categories as $cat): ?>
                                <a class="chip <?= $cat['slug'] === $category['slug'] ? 'chip--active' : '' ?>"
                                   href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </aside>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
