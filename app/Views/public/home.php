<?php
$title           = 'বারিন্দ পোস্ট - গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল';
$meta_description = 'বারিন্দ পোস্ট গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল। সর্বশেষ সংবাদ, রাজনীতি, আন্তর্জাতিক, খেলাধুলা, শিক্ষা, স্বাস্থ্য ও বিজ্ঞান-প্রযুক্তি সংবাদ জানুন।';
$meta_keywords   = 'বারিন্দ পোস্ট, রাজশাহী সংবাদ, বাংলাদেশ সংবাদ, অনলাইন নিউজ, বাংলা সংবাদ, সর্বশেষ খবর';

$og_title           = $title;
$og_description     = $meta_description;
$og_type            = 'website';
$og_image           = base_url('public/logo.png');
$twitter_card       = 'summary_large_image';
$twitter_title      = $og_title;
$twitter_description = $og_description;
$twitter_image      = $og_image;

$structured_data = [
    '@context'   => 'https://schema.org',
    '@type'      => 'WebPage',
    'name'       => 'বারিন্দ পোস্ট',
    'description'=> $meta_description,
    'url'        => base_url(),
    'mainEntity' => [
        '@type'      => 'NewsMediaOrganization',
        'name'       => 'বারিন্দ পোস্ট',
        'url'        => base_url(),
        'logo'       => base_url('public/logo.png'),
        'description'=> 'রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল',
    ],
];

$customScripts = '<script type="application/ld+json">'
    . json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    . '</script>';

// Split the featured set into the front-page slots. The side rail takes compact
// rows so its height tracks the lead story instead of towering over it, and the
// strip only appears when there are enough stories left to fill it properly.
$featured     = array_slice($featuredNews ?? [], 0, 8);

// The lead slot is the one place a photo really carries the page, and automation
// drafts arrive without one. Promote the newest story that has an image into it;
// if none of the featured set has a photo, the text-first lead takes over instead.
$leadIndex = null;
foreach ($featured as $i => $candidate) {
    if (! empty($candidate['image_url'])) {
        $leadIndex = $i;
        break;
    }
}
$leadStory = $leadIndex === null ? array_shift($featured) : $featured[$leadIndex];
if ($leadIndex !== null) {
    array_splice($featured, $leadIndex, 1);
}

$sideStories  = array_slice($featured, 0, 3);
$stripStories = array_slice($featured, 3, 4);

// Keep the strip visually balanced whatever the count.
$stripCols = count($stripStories) >= 3 ? 'col-lg-3 col-sm-6' : 'col-md-6';

// Latest feed: first block is a grid, the remainder fills the rail.
$latest     = $latestNews ?? [];
$latestGrid = array_slice($latest, 0, 6);
$latestRail = array_slice($latest, 6, 8);
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4">

    <?php if ($leadStory): ?>
        <section class="lead-block">
            <div class="row g-4">
                <div class="col-lg-8">
                    <?= view('public/widgets/news_card_widget', [
                        'news'     => $leadStory,
                        'size'     => 'lead',
                        'showDate' => true,
                        'showLead' => true,
                    ]) ?>
                </div>

                <?php if (! empty($sideStories)): ?>
                    <div class="col-lg-4 lead-block__side">
                        <div class="rail-head">আরও শীর্ষ সংবাদ</div>
                        <?php foreach ($sideStories as $item): ?>
                            <?= view('public/widgets/single_news_widget', ['news' => $item]) ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (! empty($stripStories)): ?>
        <section class="mb-5">
            <div class="row g-4 grid-ruled">
                <?php foreach ($stripStories as $item): ?>
                    <div class="<?= esc($stripCols, 'attr') ?>">
                        <?= view('public/widgets/news_card_widget', [
                            'news'     => $item,
                            'size'     => 'medium',
                            'showDate' => true,
                            'showLead' => true,
                        ]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <div class="row g-4 g-lg-5">
        <!-- Main column -->
        <div class="col-lg-8">
            <?php if (! empty($latestGrid)): ?>
                <section class="mb-5">
                    <div class="section-head">
                        <h2 class="section-head__title">সর্বশেষ সংবাদ<span class="dot"></span></h2>
                    </div>
                    <div class="row g-4">
                        <?php foreach ($latestGrid as $item): ?>
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
                </section>
            <?php endif; ?>

            <?php foreach (($categoryNews ?? []) as $block): ?>
                <?php if (empty($block['news'])) { continue; } ?>
                <?php
                $blockStories = $block['news'];
                $blockLead    = array_shift($blockStories);
                ?>
                <section class="mb-5">
                    <div class="section-head">
                        <h2 class="section-head__title"><?= esc($block['category']['name'], 'raw') ?><span class="dot"></span></h2>
                        <a class="section-head__more" href="/section/<?= esc($block['category']['slug']) ?>">
                            সব দেখুন <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <?= view('public/widgets/news_card_widget', [
                                'news'     => $blockLead,
                                'size'     => 'feature',
                                'showDate' => true,
                                'showLead' => true,
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <?php foreach ($blockStories as $item): ?>
                                <?= view('public/widgets/single_news_widget', ['news' => $item]) ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </section>
            <?php endforeach; ?>
        </div>

        <!-- Rail -->
        <aside class="col-lg-4 rail">
            <?php if (! empty($mostReadNews)): ?>
                <div class="rail-module">
                    <div class="rail-head">সর্বাধিক পঠিত</div>
                    <ol class="rank-list">
                        <?php foreach (array_slice($mostReadNews, 0, 7) as $item): ?>
                            <li><a href="/news/<?= esc(rawurlencode($item['slug']), 'attr') ?>"><?= esc($item['title'], 'raw') ?></a></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>

            <div class="rail-module">
                <?= view('public/widgets/prayer_times_widget') ?>
            </div>

            <?php foreach (($sportsWidgets ?? []) as $sw): ?>
                <div class="rail-module">
                    <?= view('public/widgets/sports_event_widget', ['event' => $sw['event'], 'matches' => $sw['matches']]) ?>
                </div>
            <?php endforeach; ?>

            <?php if (! empty($latestRail)): ?>
                <div class="rail-module">
                    <div class="rail-head">আরও পড়ুন</div>
                    <?php foreach ($latestRail as $item): ?>
                        <?= view('public/widgets/single_news_widget', ['news' => $item]) ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (! empty($categories)): ?>
                <div class="rail-module">
                    <div class="rail-head">বিভাগ</div>
                    <div class="chips">
                        <?php foreach ($categories as $cat): ?>
                            <a class="chip" href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </aside>
    </div>
</div>
<?= $this->endSection() ?>
