<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<h3 class="mb-4">ফিক্সচার</h3>
<?php
$byDate = [];
foreach ($matches as $m) {
    $date = $m['kickoff_at'] ? date('Y-m-d', strtotime($m['kickoff_at'])) : 'TBD';
    $byDate[$date][] = $m;
}
ksort($byDate);
?>
<?php foreach ($byDate as $date => $dayMatches): ?>
    <h5 class="text-muted mb-3"><?= $date === 'TBD' ? 'তারিখ নির্ধারিত নয়' : date('l, M d, Y', strtotime($date)) ?></h5>
    <?php foreach ($dayMatches as $m): ?>
        <?= view('sports_event/partials/match_card', ['match' => $m, 'event' => $event]) ?>
    <?php endforeach; ?>
<?php endforeach; ?>
<?php if (empty($matches)): ?><p class="text-muted">কোনো ফিক্সচার নেই।</p><?php endif; ?>
<?= $this->endSection() ?>
