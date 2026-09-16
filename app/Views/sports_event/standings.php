<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<h3 class="mb-4">পয়েন্ট তালিকা</h3>
<?php foreach ($standings as $groupName => $rows): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header fw-semibold"><?= $groupName === 'all' ? 'সামগ্রিক' : 'গ্রুপ ' . esc($groupName) ?></div>
        <div class="card-body p-0">
            <?= view('sports_event/partials/standings_table', ['rows' => $rows, 'event' => $event]) ?>
        </div>
    </div>
<?php endforeach; ?>

<?php if (!empty($topScorers) && !str_starts_with($event['sport_profile'], 'cricket')): ?>
    <div class="card shadow-sm">
        <div class="card-header">সর্বোচ্চ গোলদাতা</div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light"><tr><th>খেলোয়াড়</th><th>দল</th><th>গোল</th></tr></thead>
                <tbody>
                    <?php foreach ($topScorers as $s): ?>
                        <tr>
                            <td><?= esc($s['player_name']) ?></td>
                            <td class="bengali-text"><?= esc($s['team_name'] ?? '') ?></td>
                            <td><strong><?= $s['goals'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
<?= $this->endSection() ?>
