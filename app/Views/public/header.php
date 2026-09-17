<?php
/**
 * Masthead + primary navigation.
 * $categories is supplied by every public controller action.
 */
$sportsNavEvents = [];
try {
    $db = \Config\Database::connect();
    if ($db->tableExists('sports_events')) {
        helper('sports_event');
        $sportsNavEvents = model('App\Models\SportsEventModel')->getActiveNavEvents();
    }
} catch (\Throwable $e) {
    $sportsNavEvents = [];
}

$navCategories     = [];
$specialCategories = [];
foreach (($categories ?? []) as $cat) {
    if (! empty($cat['isSpecial'])) {
        $specialCategories[] = $cat;
    } else {
        $navCategories[] = $cat;
    }
}

// Highlight the section currently being viewed.
$activeSlug = $category['slug'] ?? null;
?>
<div class="topbar">
    <div class="container">
        <div class="topbar__inner">
            <span class="topbar__date"><?= esc(format_bangla_date(date('Y-m-d'), true), 'raw') ?></span>
            <div class="topbar__social">
                <a href="https://facebook.com/barindpost" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                <a href="https://x.com/BarindPost" target="_blank" rel="noopener" aria-label="X"><i class="fa-brands fa-x-twitter"></i></a>
                <a href="https://instagram.com/barindpost" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="/rss" aria-label="RSS"><i class="fas fa-rss"></i></a>
            </div>
        </div>
    </div>
</div>

<header class="masthead">
    <div class="container">
        <div class="masthead__inner">
            <a class="masthead__brand" href="/">
                <img class="masthead__logo" src="<?= base_url('public/logo.png') ?>" alt="বারিন্দ পোস্ট">
                <span>
                    <span class="masthead__name">বারিন্দ পোস্ট<span class="masthead__flag">পরীক্ষামূলক</span></span>
                    <span class="masthead__tagline">গোদাগাড়ী · রাজশাহী</span>
                </span>
            </a>

            <div class="masthead__search">
                <form class="searchbox" method="get" action="/search" role="search">
                    <input type="search" name="q" placeholder="সংবাদ খুঁজুন…" aria-label="সংবাদ খুঁজুন" value="<?= esc($query ?? '', 'attr') ?>">
                    <button type="submit" aria-label="খুঁজুন"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </div>
</header>

<nav class="mainnav" id="mainnav">
    <div class="container">
        <div class="mainnav__inner" id="mainnavInner">
            <a class="mainnav__link mainnav__home" href="/" aria-label="প্রচ্ছদ"><i class="fas fa-home"></i></a>

            <?php foreach ($navCategories as $cat): ?>
                <a class="mainnav__link js-nav-item <?= $activeSlug === $cat['slug'] ? 'is-active' : '' ?>"
                   href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
            <?php endforeach; ?>

            <?php foreach ($sportsNavEvents as $se): ?>
                <a class="mainnav__link mainnav__special js-nav-item" href="<?= esc(sports_event_url($se)) ?>">
                    <i class="fas fa-trophy me-1"></i><?= esc($se['title_bn'], 'raw') ?>
                </a>
            <?php endforeach; ?>

            <?php foreach ($specialCategories as $cat): ?>
                <a class="mainnav__link mainnav__special js-nav-item <?= $activeSlug === $cat['slug'] ? 'is-active' : '' ?>"
                   href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
            <?php endforeach; ?>

            <div class="dropdown mainnav__more" id="navMore" hidden>
                <button class="mainnav__link dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    আরও
                </button>
                <ul class="dropdown-menu dropdown-menu-end" id="navMoreMenu"></ul>
            </div>
        </div>
    </div>
</nav>

<script>
/**
 * Collapse nav items that don't fit into an "আরও" dropdown.
 * Recomputed on resize; items are measured while visible so widths are real.
 */
(function () {
    var inner = document.getElementById('mainnavInner');
    var more = document.getElementById('navMore');
    var menu = document.getElementById('navMoreMenu');
    if (!inner || !more || !menu) return;

    var items = Array.prototype.slice.call(inner.querySelectorAll('.js-nav-item'));
    if (!items.length) return;

    function layout() {
        // Reveal everything, then hide what overflows.
        items.forEach(function (el) { el.hidden = false; });
        more.hidden = true;
        menu.innerHTML = '';

        // Small screens scroll horizontally instead of collapsing.
        if (window.matchMedia('(max-width: 767.98px)').matches) return;

        var available = inner.clientWidth - 90; // leave room for home + "আরও"
        var used = 0;
        var overflow = [];

        var home = inner.querySelector('.mainnav__home');
        if (home) used += home.offsetWidth;

        items.forEach(function (el) {
            used += el.offsetWidth;
            if (used > available) overflow.push(el);
        });

        if (!overflow.length) return;

        overflow.forEach(function (el) {
            el.hidden = true;
            var li = document.createElement('li');
            var a = document.createElement('a');
            a.className = 'dropdown-item';
            a.href = el.getAttribute('href');
            a.textContent = el.textContent.trim();
            li.appendChild(a);
            menu.appendChild(li);
        });

        more.hidden = false;
    }

    layout();
    window.addEventListener('resize', layout);
    window.addEventListener('load', layout);
})();
</script>
