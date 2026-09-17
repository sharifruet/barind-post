<?php
/**
 * Sports event rail module.
 * @var array $event
 * @var array $matches
 */
helper('sports_event');
$eventUrl  = sports_event_url($event);
$isCricket = str_starts_with($event['sport_profile'] ?? '', 'cricket');
?>
<div class="sports-module">
    <div class="sports-module__head">
        <span class="sports-module__title"><i class="fas fa-trophy me-2"></i><?= esc($event['title_bn'], 'raw') ?></span>
        <a href="<?= esc($eventUrl, 'attr') ?>">সব দেখুন →</a>
    </div>

    <?php if (empty($matches)): ?>
        <p class="sports-module__empty">আজ কোনো খেলা নেই।</p>
    <?php else: ?>
        <ul class="sports-module__list">
            <?php foreach ($matches as $m): ?>
                <?php
                $sd   = sports_decode_json_field($m['sport_data'] ?? null);
                $live = sports_is_live($m['status']);
                ?>
                <li>
                    <a href="<?= esc(sports_event_url($event, 'match/' . $m['match_slug']), 'attr') ?>">
                        <span class="sports-module__teams"><?= esc($m['team_a_name'], 'raw') ?> <span class="vs">vs</span> <?= esc($m['team_b_name'], 'raw') ?></span>
                        <span class="sports-module__score">
                            <?php if ($live): ?>
                                <span class="sports-module__live">লাইভ</span>
                            <?php elseif ($isCricket): ?>
                                <?= esc(sports_format_cricket_scoreline($sd['innings'] ?? []), 'raw') ?>
                            <?php else: ?>
                                <?= esc(sports_format_football_score($sd, $m['status']), 'raw') ?>
                            <?php endif; ?>
                        </span>
                    </a>
                    <?php if (! empty($m['kickoff_at'])): ?>
                        <span class="sports-module__time"><?= esc(bn_number(date('H:i', strtotime($m['kickoff_at'])))) ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<style>
.sports-module { border: 1px solid var(--rule); background: var(--paper); }

.sports-module__head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--rule);
    background: var(--paper-2);
}

.sports-module__title {
    font-family: var(--sans);
    font-size: 0.74rem;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--ink);
}

.sports-module__head a { font-family: var(--sans); font-size: 0.76rem; color: var(--ink-3); }
.sports-module__head a:hover { color: var(--accent); }

.sports-module__list { list-style: none; margin: 0; padding: 0.25rem 1rem; }

.sports-module__list li { padding: 0.55rem 0; border-bottom: 1px solid var(--rule); }
.sports-module__list li:last-child { border-bottom: 0; }

.sports-module__list a { display: flex; align-items: center; justify-content: space-between; gap: 0.6rem; }

.sports-module__teams { font-family: var(--sans); font-size: 0.86rem; color: var(--ink-2); }
.sports-module__teams .vs { color: var(--ink-4); font-size: 0.78rem; }

.sports-module__score {
    font-family: var(--sans);
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--ink);
    white-space: nowrap;
}

.sports-module__live {
    background: var(--accent);
    color: #fff;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    padding: 0.1rem 0.4rem;
    border-radius: 2px;
}

.sports-module__time { font-family: var(--sans); font-size: 0.74rem; color: var(--ink-4); }
.sports-module__empty { font-family: var(--sans); font-size: 0.86rem; color: var(--ink-3); padding: 0.9rem 1rem; margin: 0; }
</style>
