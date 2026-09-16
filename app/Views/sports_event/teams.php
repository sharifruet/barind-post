<?= $this->extend('sports_event/layout') ?>
<?= $this->section('sports_content') ?>
<h3 class="mb-4">দলসমূহ</h3>
<div class="row g-4">
    <?php foreach ($teams as $t): ?>
        <div class="col-md-4 col-6">
            <a href="<?= esc(sports_event_url($event, 'team/' . ($t['short_code'] ?: generate_slug($t['name_en'] ?: $t['name_bn'])))) ?>" class="card shadow-sm text-decoration-none text-dark h-100">
                <div class="card-body text-center">
                    <?php if ($t['flag_url']): ?>
                        <img src="<?= esc($t['flag_url']) ?>" alt="" style="width:64px;height:48px;object-fit:cover" class="mb-2 rounded">
                    <?php endif; ?>
                    <h5 class="bengali-text mb-1"><?= esc($t['name_bn']) ?></h5>
                    <?php if ($t['short_code']): ?><small class="text-muted"><?= esc($t['short_code']) ?></small><?php endif; ?>
                    <?php if ($t['group_name']): ?><br><span class="badge bg-secondary mt-1">গ্রুপ <?= esc($t['group_name']) ?></span><?php endif; ?>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php if (empty($teams)): ?><p class="text-muted">কোনো দল যোগ করা হয়নি।</p><?php endif; ?>
<?= $this->endSection() ?>
