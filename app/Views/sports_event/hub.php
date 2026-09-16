<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>

<?php if (!empty($event['description_bn'])): ?>
    <p class="lead bengali-text text-muted mb-4"><?= esc($event['description_bn']) ?></p>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <?php if (!empty($liveMatches)): ?>
            <h4 class="mb-3 text-danger"><i class="fas fa-circle live-badge" style="font-size:.5rem"></i> লাইভ</h4>
            <?php foreach ($liveMatches as $m): ?>
                <?= view('sports_event/partials/match_card', ['match' => $m, 'event' => $event]) ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($todayMatches)): ?>
            <h4 class="mb-3 mt-4">আজকের খেলা</h4>
            <?php foreach ($todayMatches as $m): ?>
                <?= view('sports_event/partials/match_card', ['match' => $m, 'event' => $event]) ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($recentResults)): ?>
            <h4 class="mb-3 mt-4">সাম্প্রতিক ফলাফল</h4>
            <?php foreach ($recentResults as $m): ?>
                <?= view('sports_event/partials/match_card', ['match' => $m, 'event' => $event]) ?>
            <?php endforeach; ?>
            <a href="<?= esc(sports_event_url($event, 'results')) ?>" class="btn btn-outline-danger btn-sm">সব ফলাফল</a>
        <?php endif; ?>

        <?php if (!empty($upcoming)): ?>
            <h4 class="mb-3 mt-4">আসন্ন খেলা</h4>
            <?php foreach ($upcoming as $m): ?>
                <?= view('sports_event/partials/match_card', ['match' => $m, 'event' => $event]) ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="col-lg-4">
        <?php if (!empty($standingsPreview)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>পয়েন্ট তালিকা<?= isset($standingsGroup) ? ' — গ্রুপ ' . esc($standingsGroup) : '' ?></span>
                    <a href="<?= esc(sports_event_url($event, 'standings')) ?>" class="small">সব</a>
                </div>
                <div class="card-body p-2">
                    <?= view('sports_event/partials/standings_table', ['rows' => $standingsPreview, 'event' => $event]) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($news)): ?>
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>সর্বশেষ সংবাদ</span>
                    <a href="<?= esc(sports_event_url($event, 'news')) ?>" class="small">সব</a>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($news as $n): ?>
                        <li class="list-group-item">
                            <a href="/news/<?= esc($n['slug']) ?>" class="text-decoration-none text-dark bengali-text"><?= esc($n['title']) ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
