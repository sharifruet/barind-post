<?php
$sd = $match['sport_data'] ?? sports_decode_json_field($match['sport_data'] ?? null);
$isCricket = str_starts_with($event['sport_profile'], 'cricket');
$isLive = sports_is_live($match['status']);
$isFinished = sports_is_finished($match['status']);
$cardClass = $isLive ? 'live' : '';
$matchUrl = sports_event_url($event, 'match/' . $match['match_slug']);
?>
<div class="card match-card mb-3 shadow-sm <?= $cardClass ?>">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <small class="text-muted">
                <?= sports_stage_label($event['sport_profile'], $match['stage']) ?>
                <?= $match['group_name'] ? ' · গ্রুপ ' . esc($match['group_name']) : '' ?>
            </small>
            <?php if ($isLive): ?>
                <span class="badge bg-danger live-badge"><?= esc(sports_match_status_label($event['sport_profile'], $match['status'])) ?></span>
            <?php elseif ($isFinished): ?>
                <span class="badge bg-secondary"><?= esc(sports_match_status_label($event['sport_profile'], $match['status'])) ?></span>
            <?php elseif ($match['kickoff_at']): ?>
                <small class="text-muted"><?= date('M d, H:i', strtotime($match['kickoff_at'])) ?></small>
            <?php endif; ?>
        </div>
        <a href="<?= esc($matchUrl) ?>" class="text-decoration-none text-dark">
            <div class="row align-items-center">
                <div class="col-5 text-end">
                    <?php if ($match['team_a_flag']): ?><img src="<?= esc($match['team_a_flag']) ?>" class="team-flag me-1" alt=""><?php endif; ?>
                    <span class="bengali-text fw-semibold"><?= esc($match['team_a_name']) ?></span>
                </div>
                <div class="col-2 text-center score-display text-danger">
                    <?php if ($isCricket): ?>
                        <small><?= esc(sports_format_cricket_scoreline($sd['innings'] ?? [])) ?></small>
                    <?php else: ?>
                        <?= esc(sports_format_football_score($sd, $match['status'])) ?>
                    <?php endif; ?>
                </div>
                <div class="col-5">
                    <?php if ($match['team_b_flag']): ?><img src="<?= esc($match['team_b_flag']) ?>" class="team-flag me-1" alt=""><?php endif; ?>
                    <span class="bengali-text fw-semibold"><?= esc($match['team_b_name']) ?></span>
                </div>
            </div>
        </a>
        <?php if (!empty($match['venue_name'])): ?>
            <small class="text-muted d-block mt-2"><i class="fas fa-map-marker-alt"></i> <?= esc($match['venue_name']) ?><?= $match['venue_city'] ? ', ' . esc($match['venue_city']) : '' ?></small>
        <?php endif; ?>
    </div>
</div>
