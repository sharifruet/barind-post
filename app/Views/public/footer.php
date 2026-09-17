<?php
$footerCategories = array_slice($categories ?? [], 0, 8);
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
                <div class="site-footer__social">
                    <a href="https://facebook.com/barindpost" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://x.com/BarindPost" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://instagram.com/barindpost" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
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
            <span>সম্পাদক ও প্রকাশক — বারিন্দ পোস্ট</span>
        </div>
    </div>
</footer>
