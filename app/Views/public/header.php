<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand d-flex flex-column align-items-start" href="/">
            <div class="d-flex align-items-center position-relative">
                <img src="<?= base_url('public/logo.png') ?>" alt="Barind Post Logo" style="height:55px;width:auto;margin-right:12px;">
                <span>বারিন্দ পোস্ট</span>
                <span style="font-size:0.7rem; color:#fff; background-color:#dc3545; padding:2px 8px; border-radius:12px; font-weight:500; position:absolute; bottom:-8px; right:-5px;">পরীক্ষামূলক সংস্করণ</span>
            </div>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <!-- Home menu item -->
                <li class="nav-item">
                    <a class="nav-link fw-semibold" href="/">
                        <i class="fas fa-home"></i>
                    </a>
                </li>
                
                <?php 
                // Define the main menu categories
                $mainMenuCategories = ['জাতীয়', 'রাজনীতি', 'অর্থনীতি', 'আন্তর্জাতিক', 'খেলাধুলা', 'বিনোদন'];
                
                // Separate categories into main menu and dropdown
                $mainMenuItems = [];
                $dropdownItems = [];
                
                foreach ($categories as $cat) {
                    if (in_array($cat['name'], $mainMenuCategories)) {
                        $mainMenuItems[] = $cat;
                    } else {
                        $dropdownItems[] = $cat;
                    }
                }
                
                // Show main menu categories
                foreach ($mainMenuItems as $cat): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-semibold" href="/section/<?= esc($cat['slug']) ?>"><?= esc($cat['name'], 'raw') ?></a>
                    </li>
                <?php endforeach; ?>
                
                <?php if (!empty($dropdownItems)): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="moreMenu" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            +
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="moreMenu">
                            <?php foreach ($dropdownItems as $cat): ?>
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