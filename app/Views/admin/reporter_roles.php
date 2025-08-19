<?php include 'header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <?php include 'sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Reporter Roles Management</h1>
            </div>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success">
                    <?= session()->getFlashdata('success') ?>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger">
                    <?= session()->getFlashdata('error') ?>
                </div>
            <?php endif; ?>

            <!-- Add New Reporter Role Form -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Add New Reporter Role</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="/admin/reporter-roles/add">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Role Name</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <input type="text" class="form-control" id="description" name="description">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="mb-3">
                                    <label class="form-label">&nbsp;</label>
                                    <button type="submit" class="btn btn-primary w-100">Add Role</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Reporter Roles List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Reporter Roles</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $role): ?>
                                <tr>
                                    <td><?= $role['id'] ?></td>
                                    <td><?= esc($role['name']) ?></td>
                                    <td><?= esc($role['description']) ?></td>
                                    <td>
                                        <?php if ($role['is_active']): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($role['created_at'])) ?></td>
                                    <td>
                                        <a href="/admin/reporter-roles/edit/<?= $role['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" action="/admin/reporter-roles/delete" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                            <input type="hidden" name="id" value="<?= $role['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'footer.php'; ?>
