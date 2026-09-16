<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h2 class="bengali-text mb-0"><?= esc($event['title_bn']) ?></h2>
        <small class="text-muted"><?= esc($event['title_en'] ?? '') ?> · <?= esc($profile['label']) ?></small>
    </div>
    <a href="<?= esc(sports_event_url($event)) ?>" target="_blank" class="btn btn-outline-primary btn-sm">View Public Page</a>
</div>

<?= view('admin/sports_events/_nav', ['event' => $event]) ?>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center shadow-sm"><div class="card-body"><h3><?= $teamCount ?></h3><small>Teams</small></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm"><div class="card-body"><h3><?= $matchCount ?></h3><small>Matches</small></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm"><div class="card-body"><h3><?= count($liveMatches) ?></h3><small class="text-danger">Live Now</small></div></div>
    </div>
    <div class="col-md-3">
        <div class="card text-center shadow-sm"><div class="card-body"><h3><?= count($todayMatches) ?></h3><small>Today</small></div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between">
                <span>Live Matches</span>
                <a href="/admin/sports-events/<?= $event['id'] ?>/matches?status=live" class="small">View all</a>
            </div>
            <div class="card-body">
                <?php if (empty($liveMatches)): ?>
                    <p class="text-muted mb-0">No live matches.</p>
                <?php else: ?>
                    <?php foreach ($liveMatches as $m): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <span class="bengali-text"><?= esc($m['team_a_name']) ?> vs <?= esc($m['team_b_name']) ?></span>
                            <a href="/admin/sports-events/<?= $event['id'] ?>/matches/edit/<?= $m['id'] ?>" class="btn btn-sm btn-danger">Update</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-6 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between">
                <span>Today's Fixtures</span>
                <a href="/admin/sports-events/<?= $event['id'] ?>/matches/create" class="btn btn-sm btn-primary">Add Match</a>
            </div>
            <div class="card-body">
                <?php if (empty($todayMatches)): ?>
                    <p class="text-muted mb-0">No matches today.</p>
                <?php else: ?>
                    <?php foreach ($todayMatches as $m): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <div>
                                <span class="bengali-text"><?= esc($m['team_a_name']) ?> vs <?= esc($m['team_b_name']) ?></span>
                                <br><small class="text-muted"><?= $m['kickoff_at'] ? date('H:i', strtotime($m['kickoff_at'])) : '' ?></small>
                            </div>
                            <a href="/admin/sports-events/<?= $event['id'] ?>/matches/edit/<?= $m['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header">Quick Actions</div>
    <div class="card-body">
        <a href="/admin/sports-events/<?= $event['id'] ?>/teams" class="btn btn-outline-secondary me-2 mb-2">Manage Teams</a>
        <a href="/admin/sports-events/venues" class="btn btn-outline-secondary me-2 mb-2">Manage Stadiums</a>
        <a href="/admin/sports-events/<?= $event['id'] ?>/matches/create" class="btn btn-outline-primary me-2 mb-2">Add Fixture</a>
        <a href="/admin/sports-events/<?= $event['id'] ?>/standings" class="btn btn-outline-success me-2 mb-2">View Standings</a>
        <a href="/admin/sports-events/<?= $event['id'] ?>/news" class="btn btn-outline-info mb-2">Related News</a>
    </div>
</div>
<?= $this->endSection() ?>
