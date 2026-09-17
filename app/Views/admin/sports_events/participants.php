<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Teams / Participants Library</h2>
    <a href="/admin/sports-events" class="btn btn-secondary">Back to Events</a>
</div>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header">Add Participant</div>
    <div class="card-body">
        <form method="post" action="/admin/sports-events/participants/add" class="row g-3"><?= csrf_field() ?>
            <div class="col-md-3">
                <input type="text" name="name_bn" class="form-control bengali-text" placeholder="Name (Bengali) *" required>
            </div>
            <div class="col-md-2">
                <input type="text" name="name_en" class="form-control" placeholder="Name (English)">
            </div>
            <div class="col-md-1">
                <input type="text" name="short_code" class="form-control" placeholder="Code" maxlength="10">
            </div>
            <div class="col-md-3">
                <input type="text" name="flag_url" class="form-control" placeholder="Flag image URL">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="team">Team</option>
                    <option value="individual">Individual</option>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Add</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Flag</th><th>Name</th><th>Code</th><th>Type</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($participants as $p): ?>
                    <tr>
                        <td><?php if ($p['flag_url']): ?><img src="<?= esc($p['flag_url']) ?>" height="24" alt=""><?php else: ?>—<?php endif; ?></td>
                        <td class="bengali-text"><?= esc($p['name_bn']) ?><?php if ($p['name_en']): ?> <small class="text-muted">(<?= esc($p['name_en']) ?>)</small><?php endif; ?></td>
                        <td><?= esc($p['short_code']) ?></td>
                        <td><?= esc($p['type']) ?></td>
                        <td>
                            <a href="/admin/sports-events/participants/edit/<?= $p['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form method="post" action="/admin/sports-events/participants/delete/<?= $p['id'] ?>" class="d-inline" onsubmit="return confirm('Delete?')"><?= csrf_field() ?>
                                <button class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
