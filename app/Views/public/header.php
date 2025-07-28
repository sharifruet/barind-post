<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand d-flex flex-column align-items-start" href="/">
            <div class="d-flex align-items-center">
                <img src="<?= base_url('public/logo.png') ?>" alt="Barind Post Logo" style="height:40px;width:auto;margin-right:10px;">
                <span>বারিন্দ পোস্ট</span>
            </div>
            <span style="font-size:0.7rem; color:#dc3545; margin-left:50px; margin-top:-2px;">পরীক্ষামূলক সংস্করণ</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php $maxMenu = 6; $catCount = count($categories); ?>
                <?php foreach (array_slice($categories, 0, $maxMenu) as $cat): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
                    </li>
                <?php endforeach; ?>
                <?php if ($catCount > $maxMenu): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="moreMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            +
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="moreMenu">
                            <?php foreach (array_slice($categories, $maxMenu) as $cat): ?>
                                <li><a class="dropdown-item" href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
            <form class="d-flex" method="get" action="/search">
                <input class="form-control me-2" type="search" name="q" placeholder="সংবাদ খুঁজুন..." aria-label="Search" value="<?= esc($query ?? '', 'raw') ?>">
                <button class="btn btn-outline-primary" type="submit">খুঁজুন</button>
            </form>
        </div>
    </div>
</nav> 