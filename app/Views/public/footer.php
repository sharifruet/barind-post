<?php
$footerCategories = array_slice(menu_categories($categories ?? []), 0, 8);
$publisher        = config('SiteInfo');
?>
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-5 col-md-12">
                <div class="site-footer__brand">বারিন্দ পোস্ট</div>
                <p class="site-footer__blurb">
                    মহিশালবাড়ী, গোদাগাড়ী, রাজশাহী থেকে প্রকাশিত একটি অনলাইন সংবাদমাধ্যম।
                    বরেন্দ্র অঞ্চলের খবর, রাজনীতি, অর্থনীতি, খেলাধুলা ও জীবনযাত্রার সর্বশেষ সংবাদ।
                </p>
                <address class="site-footer__blurb">
                    <?php if ($publisher->editorName !== ''): ?>
                        <?= $publisher->publisherName === '' ? 'সম্পাদক ও প্রকাশক' : 'সম্পাদক' ?>: <?= esc($publisher->editorName) ?><br>
                    <?php endif; ?>
                    <?php if ($publisher->publisherName !== ''): ?>
                        প্রকাশক: <?= esc($publisher->publisherName) ?><br>
                    <?php endif; ?>
                    কার্যালয়: <?= esc($publisher->address) ?><br>
                    <?php if ($publisher->phone !== ''): ?>
                        ফোন: <a href="tel:<?= esc(preg_replace('/[^0-9+]/', '', $publisher->phone), 'attr') ?>"><?= esc($publisher->phone) ?></a><br>
                    <?php endif; ?>
                    ইমেইল: <a href="mailto:<?= esc($publisher->email, 'attr') ?>"><?= esc($publisher->email) ?></a>
                    <?php if ($publisher->registration !== ''): ?>
                        <br><?= esc($publisher->registration) ?>
                    <?php endif; ?>
                </address>
                <div class="site-footer__social">
                    <?php foreach ($publisher->socialLinks() as $social): ?>
                        <a href="<?= esc($social['url'], 'attr') ?>" target="_blank" rel="noopener" aria-label="<?= esc($social['label'], 'attr') ?>"><i class="<?= esc($social['icon'], 'attr') ?>"></i></a>
                    <?php endforeach; ?>
                    <a href="/rss" aria-label="RSS"><i class="fas fa-rss"></i></a>
                </div>
            </div>

            <div class="col-lg-4 col-md-7">
                <div class="site-footer__head">বিভাগসমূহ</div>
                <div class="row">
                    <div class="col-6">
                        <ul class="site-footer__links">
                            <?php foreach (array_slice($footerCategories, 0, 4) as $cat): ?>
                                <li><a href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <div class="col-6">
                        <ul class="site-footer__links">
                            <?php foreach (array_slice($footerCategories, 4, 4) as $cat): ?>
                                <li><a href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-5">
                <div class="site-footer__head">প্রতিষ্ঠান</div>
                <ul class="site-footer__links">
                    <li><a href="/barind-post">আমাদের সম্পর্কে</a></li>
                    <li><a href="/contact">যোগাযোগ</a></li>
                    <li><a href="/ads">বিজ্ঞাপন</a></li>
                    <li><a href="/privacy">গোপনীয়তা নীতি</a></li>
                    <li><a href="/terms">ব্যবহারের শর্তাবলী</a></li>
                    <li><a href="/rss-info"><i class="fas fa-rss me-1"></i>আরএসএস ফিড</a></li>
                </ul>
            </div>
        </div>

        <div class="site-footer__bottom">
            <span>&copy; <?= esc(bn_number(date('Y'))) ?> বারিন্দ পোস্ট। সর্বস্বত্ব সংরক্ষিত।</span>
            <?php if ($publisher->editorName !== ''): ?>
                <span><?= $publisher->publisherName === '' ? 'সম্পাদক ও প্রকাশক' : 'সম্পাদক' ?> — <?= esc($publisher->editorName) ?></span>
            <?php endif; ?>
        </div>
    </div>
</footer>
