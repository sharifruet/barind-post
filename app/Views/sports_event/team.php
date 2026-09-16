<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<div class="d-flex align-items-center gap-3 mb-4">
    <?php if ($team['flag_url']): ?><img src="<?= esc($team['flag_url']) ?>" style="width:80px;height:60px;object-fit:cover" class="rounded" alt=""><?php endif; ?>
    <div>
        <h2 class="bengali-text mb-0"><?= esc($team['name_bn']) ?></h2>
        <?php if ($team['group_name']): ?><span class="badge bg-secondary">গ্রুপ <?= esc($team['group_name']) ?></span><?php endif; ?>
    </div>
</div>
<h4 class="mb-3">খেলার তালিকা</h4>
<?php foreach ($matches as $m): ?>
    <?= view('sports_event/partials/match_card', ['match' => $m, 'event' => $event]) ?>
<?php endforeach; ?>
<?php if (empty($matches)): ?><p class="text-muted">এই দলের কোনো খেলা নেই।</p><?php endif; ?>
<?= $this->endSection() ?>
