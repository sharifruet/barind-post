<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Stadiums / Venues</h2>
    <a href="/admin/sports-events" class="btn btn-secondary">Back to Events</a>
</div>
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card shadow-sm mb-4">
    <div class="card-header">Add Stadium</div>
    <div class="card-body">
        <form method="post" action="/admin/sports-events/venues/add" class="row g-3"><?= csrf_field() ?>
            <div class="col-md-3"><input type="text" name="name_bn" class="form-control bengali-text" placeholder="Name (Bengali) *" required></div>
            <div class="col-md-2"><input type="text" name="name_en" class="form-control" placeholder="Name (English)"></div>
            <div class="col-md-2"><input type="text" name="city" class="form-control" placeholder="City"></div>
            <div class="col-md-2"><input type="text" name="country" class="form-control" placeholder="Country"></div>
            <div class="col-md-1"><input type="number" name="capacity" class="form-control" placeholder="Capacity"></div>
            <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Add</button></div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Name</th><th>City</th><th>Country</th><th>Capacity</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($venues as $v): ?>
                    <tr>
                        <td class="bengali-text"><?= esc($v['name_bn']) ?><?php if ($v['name_en']): ?> <small class="text-muted">(<?= esc($v['name_en']) ?>)</small><?php endif; ?></td>
                        <td><?= esc($v['city'] ?? '') ?></td>
                        <td><?= esc($v['country'] ?? '') ?></td>
                        <td><?= $v['capacity'] ? number_format($v['capacity']) : '—' ?></td>
                        <td>
                            <a href="/admin/sports-events/venues/edit/<?= $v['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form method="post" action="/admin/sports-events/venues/delete/<?= $v['id'] ?>" class="d-inline" onsubmit="return confirm('Delete?')"><?= csrf_field() ?>
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
