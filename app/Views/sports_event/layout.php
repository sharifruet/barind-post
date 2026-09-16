<?= $this->extend('public/layout') ?>
<?= $this->section('content') ?>
<?php
$bannerStyle = !empty($event['banner_image'])
    ? "background: linear-gradient(rgba(0,0,0,.55), rgba(0,0,0,.55)), url('" . esc($event['banner_image']) . "') center/cover;"
    : "background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);";
?>
<style>
.sports-event-banner { <?= $bannerStyle ?> color: #fff; padding: 2.5rem 0; margin-bottom: 0; }
.sports-sub-nav { background: #dc3545; }
.sports-sub-nav .nav-link { color: rgba(255,255,255,.85); padding: .75rem 1.25rem; border-radius: 0; }
.sports-sub-nav .nav-link:hover, .sports-sub-nav .nav-link.active { color: #fff; background: rgba(0,0,0,.15); }
.match-card { border-left: 4px solid #dc3545; transition: box-shadow .2s; }
.match-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }
.match-card.live { border-left-color: #28a745; }
.team-flag { width: 28px; height: 20px; object-fit: cover; border-radius: 2px; }
.score-display { font-size: 1.5rem; font-weight: 700; }
.live-badge { animation: pulse 1.5s infinite; }
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.6} }
</style>

<div class="sports-event-banner">
    <div class="container">
        <div class="d-flex align-items-center gap-3">
            <?php if (!empty($event['logo_image'])): ?>
                <img src="<?= esc($event['logo_image']) ?>" alt="" height="60">
            <?php endif; ?>
            <div>
                <h1 class="h2 mb-1 bengali-text"><?= esc($event['title_bn']) ?></h1>
                <?php if ($event['start_date']): ?>
                    <small><?= esc($event['start_date']) ?><?php if ($event['end_date']): ?> — <?= esc($event['end_date']) ?><?php endif; ?></small>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<nav class="sports-sub-nav mb-4">
    <div class="container">
        <ul class="nav">
            <?php
            $tabs = [
                'hub'       => ['', 'সারাংশ'],
                'fixtures'  => ['fixtures', 'ফিক্সচার'],
                'results'   => ['results', 'ফলাফল'],
                'standings' => ['standings', 'পয়েন্ট তালিকা'],
                'stats'     => ['stats', 'পরিসংখ্যান'],
                'teams'     => ['teams', 'দলসমূহ'],
                'news'      => ['news', 'সংবাদ'],
            ];
            foreach ($tabs as $key => [$path, $label]):
                if ($key === 'standings' && empty($profile['has_standings'])) continue;
            ?>
                <li class="nav-item">
                    <a class="nav-link<?= ($activeTab ?? '') === $key ? ' active' : '' ?>" href="<?= esc(sports_event_url($event, $path)) ?>"><?= $label ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<div class="container mb-5">
    <?= $this->renderSection('sports_content') ?>
</div>
<?= $this->endSection() ?>
