<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<h3 class="mb-4">ফলাফল</h3>
<?php foreach ($matches as $m): ?>
    <?= view('sports_event/partials/match_card', ['match' => $m, 'event' => $event]) ?>
<?php endforeach; ?>
<?php if (empty($matches)): ?><p class="text-muted">এখনো কোনো ফলাফল নেই।</p><?php endif; ?>
<?= $this->endSection() ?>
