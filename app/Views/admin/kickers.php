<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Manage Kickers</h2>
    <a href="/admin/kickers/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New Kicker
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">All Kickers</h5>
    </div>
    <div class="card-body">
        <?php if (empty($kickers)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No kickers found</h5>
                <p class="text-muted">Create your first kicker to get started.</p>
                <a href="/admin/kickers/create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First Kicker
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Kicker Text</th>
                            <th>Color</th>
                            <th>Usage Count</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($kickers as $kicker): ?>
                            <tr>
                                <td>
                                    <span class="kicker-preview" style="color: <?= esc($kicker['color']) ?>; font-weight: bold;">
                                        <?= esc($kicker['text']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="color-preview me-2" style="width: 20px; height: 20px; background-color: <?= esc($kicker['color']) ?>; border-radius: 3px; border: 1px solid #ddd;"></div>
                                        <code><?= esc($kicker['color']) ?></code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $kicker['usage_count'] > 0 ? 'primary' : 'secondary' ?>">
                                        <?= $kicker['usage_count'] ?> use<?= $kicker['usage_count'] != 1 ? 's' : '' ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('M d, Y', strtotime($kicker['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="/admin/kickers/edit/<?= $kicker['id'] ?>" class="btn btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($kicker['usage_count'] == 0): ?>
                                            <button type="button" class="btn btn-outline-danger" 
                                                    onclick="deleteKicker(<?= $kicker['id'] ?>, '<?= esc($kicker['text'], 'attr') ?>')" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary" disabled title="Cannot delete - in use">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the kicker "<span id="deleteKickerText"></span>"?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script>
function deleteKicker(id, text) {
    document.getElementById('deleteKickerText').textContent = text;
    document.getElementById('confirmDelete').onclick = function() {
        window.location.href = '/admin/kickers/delete/' + id;
    };
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>

<style>
.kicker-preview {
    font-size: 0.9em;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.color-preview {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
</style>

<?= $this->endSection() ?>
