<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-trophy me-2"></i>Sports Events</h2>
    <div>
        <a href="/admin/sports-events/participants" class="btn btn-outline-secondary me-2">Teams Library</a>
        <a href="/admin/sports-events/venues" class="btn btn-outline-secondary me-2">Stadiums</a>
        <a href="/admin/sports-events/create" class="btn btn-primary">Create Event</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Event</th>
                    <th>Sport</th>
                    <th>Dates</th>
                    <th>Status</th>
                    <th>Public URL</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($events)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No events yet. Create your first tournament.</td></tr>
                <?php else: ?>
                    <?php foreach ($events as $ev): ?>
                        <tr>
                            <td>
                                <strong class="bengali-text"><?= esc($ev['title_bn']) ?></strong>
                                <?php if ($ev['title_en']): ?>
                                    <br><small class="text-muted"><?= esc($ev['title_en']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?= esc($ev['sport_profile']) ?></td>
                            <td>
                                <?php if ($ev['start_date']): ?>
                                    <?= esc($ev['start_date']) ?>
                                    <?php if ($ev['end_date']): ?> → <?= esc($ev['end_date']) ?><?php endif; ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $badge = match($ev['status']) {
                                    'active' => 'success',
                                    'archived' => 'secondary',
                                    default => 'warning',
                                };
                                ?>
                                <span class="badge bg-<?= $badge ?>"><?= esc($ev['status']) ?></span>
                            </td>
                            <td>
                                <?php $url = sports_event_url($ev); ?>
                                <a href="<?= esc($url) ?>" target="_blank"><?= esc($url) ?></a>
                            </td>
                            <td>
                                <a href="/admin/sports-events/manage/<?= $ev['id'] ?>" class="btn btn-sm btn-primary">Manage</a>
                                <a href="/admin/sports-events/edit/<?= $ev['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form method="post" action="/admin/sports-events/delete/<?= $ev['id'] ?>" class="d-inline" onsubmit="return confirm('Delete this event and all matches?')">
                                    <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
