<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<h3 class="mb-4">পরিসংখ্যান</h3>

<?php if (!empty($topScorers)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header">সর্বোচ্চ গোলদাতা</div>
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light"><tr><th>#</th><th>খেলোয়াড়</th><th>দল</th><th>গোল</th></tr></thead>
                <tbody>
                    <?php foreach ($topScorers as $i => $s): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= esc($s['player_name']) ?></td>
                            <td class="bengali-text"><?= esc($s['team_name'] ?? '') ?></td>
                            <td><strong><?= $s['goals'] ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php else: ?>
    <p class="text-muted">পরিসংখ্যান উপলব্ধ হলে এখানে দেখানো হবে।</p>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-header">অংশগ্রহণকারী দল (<?= count($teams) ?>)</div>
    <div class="card-body">
        <div class="row g-3">
            <?php foreach ($teams as $t): ?>
                <div class="col-md-4 col-6">
                    <a href="<?= esc(sports_event_url($event, 'team/' . ($t['short_code'] ?: generate_slug($t['name_en'] ?: $t['name_bn'])))) ?>" class="text-decoration-none text-dark">
                        <?php if ($t['flag_url']): ?><img src="<?= esc($t['flag_url']) ?>" class="team-flag me-1" alt=""><?php endif; ?>
                        <span class="bengali-text"><?= esc($t['name_bn']) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
