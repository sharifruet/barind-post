<?php
// Meta tags come from the controller.
$structured_data = [
    '@context'  => 'https://schema.org',
    '@type'     => 'NewsArticle',
    'headline'  => $news['title'],
    'description' => $meta_description,
    'image'     => $og_image,
    'author'    => [
        '@type' => 'Organization',
        'name'  => 'বারিন্দ পোস্ট',
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name'  => 'বারিন্দ পোস্ট',
        'logo'  => ['@type' => 'ImageObject', 'url' => base_url('public/logo.png')],
    ],
    'datePublished'    => $news['published_at'],
    'dateModified'     => $news['updated_at'] ?? $news['published_at'],
    'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => current_url()],
];

$customScripts = '<script type="application/ld+json">'
    . json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
    . '</script>';

$shareUrl   = urlencode(current_url());
$shareTitle = urlencode($news['title']);
$readTime   = reading_time($news);

// Sidebar stories, excluding the current article.
$railStories = [];
foreach (($latestNews ?? []) as $item) {
    if ((int) $item['id'] !== (int) $news['id']) {
        $railStories[] = $item;
    }
}
$readNext = array_slice($railStories, 0, 4);
$railList = array_slice($railStories, 4, 8);
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-4">
    <div class="row g-4 g-lg-5">
        <div class="col-lg-8">
            <article class="article" itemscope itemtype="https://schema.org/NewsArticle">
                <?php if (! empty($news['kicker'])): ?>
                    <span class="kicker kicker--bar article__kicker" style="color: <?= esc($news['kicker_color'] ?? '#c8102e', 'attr') ?>;">
                        <?= esc($news['kicker'], 'raw') ?>
                    </span>
                <?php endif; ?>

                <h1 class="article__title" itemprop="headline"><?= esc($news['title'], 'raw') ?></h1>

                <?php if (! empty($news['subtitle'])): ?>
                    <p class="article__standfirst" itemprop="alternativeHeadline description"><?= esc($news['subtitle'], 'raw') ?></p>
                <?php endif; ?>

                <div class="article__byline">
                    <?php if (! empty($news['reporterRole'])): ?>
                        <strong><?= esc($news['reporterRole'], 'raw') ?></strong>
                        <span class="sep">|</span>
                    <?php endif; ?>

                    <?php if (! empty($news['dateline'])): ?>
                        <span><?= esc($news['dateline'], 'raw') ?></span>
                        <span class="sep">|</span>
                    <?php endif; ?>

                    <time datetime="<?= esc($news['published_at'], 'attr') ?>" itemprop="datePublished">
                        <?= esc(format_bangla_date($news['published_at'], false, true), 'raw') ?>
                    </time>

                    <?php if ($readTime !== ''): ?>
                        <span class="sep">|</span>
                        <span><?= esc($readTime, 'raw') ?></span>
                    <?php endif; ?>

                    <div class="share ms-auto">
                        <a class="share__btn" target="_blank" rel="noopener" title="ফেসবুকে শেয়ার"
                           href="https://www.facebook.com/sharer/sharer.php?u=<?= $shareUrl ?>"><i class="fab fa-facebook-f"></i></a>
                        <a class="share__btn" target="_blank" rel="noopener" title="এক্স-এ শেয়ার"
                           href="https://twitter.com/intent/tweet?url=<?= $shareUrl ?>&text=<?= $shareTitle ?>"><i class="fa-brands fa-x-twitter"></i></a>
                        <a class="share__btn" target="_blank" rel="noopener" title="হোয়াটসঅ্যাপে শেয়ার"
                           href="https://wa.me/?text=<?= $shareTitle ?>%20<?= $shareUrl ?>"><i class="fab fa-whatsapp"></i></a>
                        <button class="share__btn" type="button" id="copyLinkBtn" title="লিংক কপি করুন"><i class="fas fa-link"></i></button>
                        <button class="share__btn" type="button" onclick="window.print()" title="প্রিন্ট করুন"><i class="fas fa-print"></i></button>
                    </div>
                </div>

                <?php if (! empty($news['image_url'])): ?>
                    <figure class="article__figure">
                        <img src="<?= esc(get_image_url($news['image_url']), 'attr') ?>"
                             alt="<?= esc($news['image_alt_text'] ?? $news['title'], 'attr') ?>"
                             itemprop="image">
                        <?php if (! empty($news['image_caption'])): ?>
                            <figcaption>ছবি: <?= esc($news['image_caption'], 'raw') ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php endif; ?>

                <?php $points = key_points($news['lead_text'] ?? null); ?>
                <?php if (count($points) >= 2): ?>
                    <aside class="key-points" aria-labelledby="key-points-title">
                        <h2 class="key-points__title" id="key-points-title">এক নজরে</h2>
                        <ul class="key-points__list">
                            <?php foreach ($points as $point): ?>
                                <li><?= esc($point) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </aside>
                <?php elseif (count($points) === 1): ?>
                    <p class="article__standfirst"><?= esc($points[0], 'raw') ?></p>
                <?php endif; ?>

                <div class="prose" itemprop="articleBody">
                    <?= render_article_body($news['content']) ?>
                </div>

                <script>
                    // View-count beacon: the page itself is cached, so counting happens here.
                    (function () {
                        var u = '/news/view/<?= (int) $news['id'] ?>';
                        if (navigator.sendBeacon) { navigator.sendBeacon(u); }
                        else { fetch(u, { method: 'POST', keepalive: true, credentials: 'same-origin' }).catch(function () {}); }
                    })();
                </script>

                <?php if (! empty($news['source'])): ?>
                    <p class="story__meta mt-4 pt-3" style="border-top:1px solid var(--rule);">
                        সূত্র:
                        <?php if (! empty($news['source_url'])): ?>
                            <a href="<?= esc($news['source_url'], 'attr') ?>" target="_blank" rel="noopener nofollow"><?= esc($news['source'], 'raw') ?></a>
                        <?php else: ?>
                            <?= esc($news['source'], 'raw') ?>
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </article>

            <?php if (! empty($readNext)): ?>
                <section class="read-next mt-5">
                    <div class="section-head">
                        <h2 class="section-head__title">আরও পড়ুন<span class="dot"></span></h2>
                    </div>
                    <div class="row g-4 grid-ruled">
                        <?php foreach ($readNext as $item): ?>
                            <div class="col-md-3 col-sm-6">
                                <?= view('public/widgets/news_card_widget', [
                                    'news'     => $item,
                                    'size'     => 'compact',
                                    'showDate' => true,
                                    'showLead' => false,
                                ]) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <aside class="col-lg-4 rail">
            <div class="rail-module">
                <?= view('public/widgets/prayer_times_widget') ?>
            </div>

            <?php if (! empty($railList)): ?>
                <div class="rail-module">
                    <div class="rail-head">সর্বশেষ সংবাদ</div>
                    <?php foreach ($railList as $item): ?>
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

<script>
document.getElementById('copyLinkBtn')?.addEventListener('click', function () {
    var btn = this;
    navigator.clipboard.writeText(window.location.href).then(function () {
        btn.innerHTML = '<i class="fas fa-check"></i>';
        setTimeout(function () { btn.innerHTML = '<i class="fas fa-link"></i>'; }, 1600);
    });
});
</script>
<?= $this->endSection() ?>
