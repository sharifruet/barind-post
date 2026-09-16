<?php
/**
 * Sports event homepage widget
 * @var array $event
 * @var array $matches
 */
helper('sports_event');
$eventUrl = sports_event_url($event);
$isCricket = str_starts_with($event['sport_profile'] ?? '', 'cricket');
?>
<div class="card shadow-sm mb-4 border-danger">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <span class="bengali-text"><i class="fas fa-trophy me-2"></i><?= esc($event['title_bn']) ?></span>
        <a href="<?= esc($eventUrl) ?>" class="text-white small">সব দেখুন →</a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($matches)): ?>
            <p class="text-muted p-3 mb-0 small">আজ কোনো খেলা নেই।</p>
        <?php else: ?>
            <ul class="list-group list-group-flush">
                <?php foreach ($matches as $m): ?>
                    <?php
                    $sd = sports_decode_json_field($m['sport_data'] ?? null);
                    $live = sports_is_live($m['status']);
                    ?>
                    <li class="list-group-item">
                        <a href="<?= esc(sports_event_url($event, 'match/' . $m['match_slug'])) ?>" class="text-decoration-none text-dark">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="bengali-text small"><?= esc($m['team_a_name']) ?> vs <?= esc($m['team_b_name']) ?></span>
                                <span class="fw-bold text-danger small">
                                    <?php if ($live): ?>
                                        <span class="badge bg-danger">লাইভ</span>
                                    <?php elseif ($isCricket): ?>
                                        <?= esc(sports_format_cricket_scoreline($sd['innings'] ?? [])) ?>
                                    <?php else: ?>
                                        <?= esc(sports_format_football_score($sd, $m['status'])) ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                            <small class="text-muted"><?= $m['kickoff_at'] ? date('H:i', strtotime($m['kickoff_at'])) : '' ?></small>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
