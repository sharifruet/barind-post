<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h2 class="bengali-text mb-3">Standings — <?= esc($event['title_bn']) ?></h2>
<?= view('admin/sports_events/_nav', ['event' => $event]) ?>

<?php $isCricket = str_starts_with($event['sport_profile'], 'cricket'); ?>

<?php foreach ($standings as $groupName => $rows): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header"><?= $groupName === 'all' ? 'Overall Standings' : 'Group ' . esc($groupName) ?></div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th><th>Team</th><th>P</th><th>W</th>
                        <?php if (!$isCricket): ?><th>D</th><?php endif; ?>
                        <th>L</th>
                        <?php if ($isCricket): ?><th>NR</th><th>NRR</th><?php else: ?><th>GF</th><th>GA</th><th>GD</th><?php endif; ?>
                        <th>Pts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $row): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="bengali-text"><?= esc($row['name_bn']) ?></td>
                            <td><?= $row['played'] ?></td>
                            <td><?= $row['won'] ?></td>
                            <?php if (!$isCricket): ?><td><?= $row['drawn'] ?></td><?php endif; ?>
                            <td><?= $row['lost'] ?></td>
                            <?php if ($isCricket): ?>
                                <td><?= $row['nr'] ?? 0 ?></td>
                                <td><?= $row['nrr'] ?? 0 ?></td>
                            <?php else: ?>
                                <td><?= $row['gf'] ?></td>
                                <td><?= $row['ga'] ?></td>
                                <td><?= $row['gd'] ?></td>
                            <?php endif; ?>
                            <td><strong><?= $row['points'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!empty($topScorers) && !$isCricket): ?>
    <div class="card shadow-sm">
        <div class="card-header">Top Scorers</div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>Player</th><th>Team</th><th>Goals</th></tr></thead>
                <tbody>
                    <?php foreach ($topScorers as $s): ?>
                        <tr>
                            <td><?= esc($s['player_name']) ?></td>
                            <td class="bengali-text"><?= esc($s['team_name'] ?? '') ?></td>
                            <td><?= $s['goals'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
