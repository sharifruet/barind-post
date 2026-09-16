<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Manage Roles</h2>
            <a href="/admin" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        <form method="post" action="/admin/roles/add" class="row g-3 mb-4">
            <div class="col-auto">
                <input type="text" name="name" class="form-control" placeholder="New Role Name" required>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Add Role</button>
            </div>
        </form>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Existing Roles</h5>
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 10%">ID</th>
                            <th>Name</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($roles as $role): ?>
                            <tr>
                                <td><?= esc($role['id']) ?></td>
                                <td><?= esc($role['name']) ?></td>
                                <td>
                                    <form method="post" action="/admin/roles/delete" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= esc($role['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this role?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?> 