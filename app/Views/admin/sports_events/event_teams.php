<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h2 class="bengali-text mb-3"><?= esc($event['title_bn']) ?> — Teams</h2>
<?= view('admin/sports_events/_nav', ['event' => $event]) ?>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header">Add Team to Event</div>
    <div class="card-body">
        <form method="post" action="/admin/sports-events/<?= $event['id'] ?>/teams/add" class="row g-3">
            <div class="col-md-5">
                <select name="participant_id" class="form-select" required>
                    <option value="">Select team...</option>
                    <?php foreach ($available as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= esc($a['name_bn']) ?> (<?= esc($a['short_code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="group_name" class="form-control" placeholder="Group (e.g. A)" list="groupList">
                <datalist id="groupList">
                    <?php foreach ($groups as $g): ?><option value="<?= esc($g) ?>"><?php endforeach; ?>
                </datalist>
            </div>
            <div class="col-md-2"><input type="number" name="seed" class="form-control" placeholder="Seed"></div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Add</button></div>
        </form>
        <small class="text-muted">Need a new team? <a href="/admin/sports-events/participants">Add to library first</a></small>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Team</th><th>Code</th><th>Group</th><th>Seed</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($teams as $t): ?>
                    <tr>
                        <td class="bengali-text">
                            <?php if ($t['flag_url']): ?><img src="<?= esc($t['flag_url']) ?>" height="20" class="me-1"><?php endif; ?>
                            <?= esc($t['name_bn']) ?>
                        </td>
                        <td><?= esc($t['short_code']) ?></td>
                        <td>
                            <form method="post" action="/admin/sports-events/<?= $event['id'] ?>/teams/update/<?= $t['entry_id'] ?>" class="d-flex gap-1">
                                <input type="text" name="group_name" class="form-control form-control-sm" value="<?= esc($t['group_name'] ?? '') ?>" style="width:80px">
                                <input type="number" name="seed" class="form-control form-control-sm" value="<?= esc($t['seed'] ?? '') ?>" style="width:60px">
                                <button class="btn btn-sm btn-outline-primary">Save</button>
                            </form>
                        </td>
                        <td><?= esc($t['seed'] ?? '') ?></td>
                        <td>
                            <form method="post" action="/admin/sports-events/<?= $event['id'] ?>/teams/remove/<?= $t['entry_id'] ?>" class="d-inline" onsubmit="return confirm('Remove?')">
                                <button class="btn btn-sm btn-danger">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
