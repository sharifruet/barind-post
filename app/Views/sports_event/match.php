<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<?php
$sd = $match['sport_data'];
$isCricket = str_starts_with($event['sport_profile'], 'cricket');
$isLive = sports_is_live($match['status']);
?>
<div class="card shadow-sm mb-4">
    <div class="card-body text-center py-4">
        <small class="text-muted d-block mb-2">
            <?= esc(sports_stage_label($event['sport_profile'], $match['stage'])) ?>
            <?= $match['group_name'] ? ' · গ্রুপ ' . esc($match['group_name']) : '' ?>
            <?php if ($isLive): ?> · <span class="text-danger fw-bold live-badge">লাইভ</span><?php endif; ?>
        </small>

        <div class="row align-items-center justify-content-center mb-3">
            <div class="col-md-4 text-md-end">
                <?php if ($match['team_a_flag']): ?><img src="<?= esc($match['team_a_flag']) ?>" class="team-flag me-2" style="width:40px;height:30px" alt=""><?php endif; ?>
                <h3 class="d-inline bengali-text"><?= esc($match['team_a_name']) ?></h3>
            </div>
            <div class="col-md-4">
                <?php if ($isCricket): ?>
                    <div class="score-display text-danger">
                        <div><?= esc(sports_format_cricket_scoreline([$sd['innings'][0] ?? []])) ?></div>
                        <div class="my-1">vs</div>
                        <div><?= esc(sports_format_cricket_scoreline([$sd['innings'][1] ?? []])) ?></div>
                    </div>
                    <?php if (!empty($sd['result_text'])): ?>
                        <p class="bengali-text text-muted mt-2 mb-0"><?= esc($sd['result_text']) ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="score-display text-danger"><?= esc(sports_format_football_score($sd, $match['status'])) ?></div>
                    <?php if (isset($sd['a_ht'], $sd['b_ht']) && $sd['a_ht'] !== null): ?>
                        <small class="text-muted">(বিরতি: <?= (int)$sd['a_ht'] ?>-<?= (int)$sd['b_ht'] ?>)</small>
                    <?php endif; ?>
                <?php endif; ?>
                <div class="mt-2"><span class="badge bg-<?= $isLive ? 'danger' : 'secondary' ?>"><?= esc(sports_match_status_label($event['sport_profile'], $match['status'])) ?></span></div>
            </div>
            <div class="col-md-4 text-md-start">
                <?php if ($match['team_b_flag']): ?><img src="<?= esc($match['team_b_flag']) ?>" class="team-flag me-2" style="width:40px;height:30px" alt=""><?php endif; ?>
                <h3 class="d-inline bengali-text"><?= esc($match['team_b_name']) ?></h3>
            </div>
        </div>

        <div class="text-muted small">
            <?php if ($match['kickoff_at']): ?><i class="far fa-clock"></i> <?= date('M d, Y H:i', strtotime($match['kickoff_at'])) ?><?php endif; ?>
            <?php if ($match['venue_name']): ?> · <i class="fas fa-map-marker-alt"></i> <?= esc($match['venue_name']) ?><?= $match['venue_city'] ? ', ' . esc($match['venue_city']) : '' ?><?php endif; ?>
            <?php if ($match['referee']): ?> · রেফারি: <?= esc($match['referee']) ?><?php endif; ?>
            <?php if ($match['attendance']): ?> · দর্শক: <?= number_format($match['attendance']) ?><?php endif; ?>
        </div>
    </div>
</div>

<?php if (!empty($timeline)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">ম্যাচ টাইমলাইন</div>
        <ul class="list-group list-group-flush">
            <?php foreach ($timeline as $tl): ?>
                <?php
                $typeLabel = $profile['timeline_types'][$tl['event_type']] ?? $tl['event_type'];
                ?>
                <li class="list-group-item d-flex justify-content-between">
                    <span>
                        <strong><?= esc($tl['event_minute'] ?? '') ?>'</strong>
                        <?= esc($typeLabel) ?>
                        <?php if ($tl['player_name']): ?> — <?= esc($tl['player_name']) ?><?php endif; ?>
                        <?php if ($tl['team_name']): ?> <small class="text-muted">(<?= esc($tl['team_name']) ?>)</small><?php endif; ?>
                    </span>
                    <?php if ($tl['detail']): ?><small class="text-muted"><?= esc($tl['detail']) ?></small><?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<?php if (!empty($match['summary_bn'])): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">সারাংশ</div>
        <div class="card-body bengali-text lh-lg"><?= nl2br(esc($match['summary_bn'])) ?></div>
    </div>
<?php endif; ?>

<?php if (!empty($linkedNews)): ?>
    <div class="card shadow-sm mb-4 border-danger">
        <div class="card-header bg-danger text-white">ম্যাচ রিপোর্ট</div>
        <div class="card-body">
            <h5 class="bengali-text"><a href="/news/<?= esc($linkedNews['slug']) ?>" class="text-decoration-none text-dark"><?= esc($linkedNews['title']) ?></a></h5>
            <?php if ($linkedNews['lead_text']): ?><p class="text-muted bengali-text"><?= esc($linkedNews['lead_text']) ?></p><?php endif; ?>
            <a href="/news/<?= esc($linkedNews['slug']) ?>" class="btn btn-danger btn-sm">সম্পূর্ণ পড়ুন</a>
        </div>
    </div>
<?php endif; ?>

<?php if (!empty($relatedNews)): ?>
    <h4 class="mb-3">সম্পর্কিত সংবাদ</h4>
    <div class="row g-3">
        <?php foreach ($relatedNews as $n): ?>
            <?php if (($linkedNews['id'] ?? null) == $n['id']) continue; ?>
            <div class="col-md-6">
                <a href="/news/<?= esc($n['slug']) ?>" class="card shadow-sm text-decoration-none text-dark h-100">
                    <div class="card-body bengali-text"><?= esc($n['title']) ?></div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
