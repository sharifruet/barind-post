<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-10 mx-auto">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Manage Users</h2>
            <a href="/admin" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        <form method="post" action="/admin/users/add" class="row g-3 mb-4">
            <div class="col-md-3">
                <input type="text" name="name" class="form-control" placeholder="Name" required>
            </div>
            <div class="col-md-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="col-md-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <div class="col-md-2">
                <?php
                // Fetch roles for dropdown
                $roleModel = new \App\Models\RoleModel();
                $roles = $roleModel->findAll();
                ?>
                <select name="role" class="form-select" required>
                    <option value="">Select Role</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= esc($role['name']) ?>"><?= esc($role['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">Add</button>
            </div>
        </form>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Existing Users</h5>
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= esc($user['id']) ?></td>
                                <td><?= esc($user['name']) ?></td>
                                <td><?= esc($user['email']) ?></td>
                                <td><?= esc($user['role']) ?></td>
                                <td><?= esc($user['created_at']) ?></td>
                                <td>
                                    <a href="/admin/reporter-roles/assign/<?= $user['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-user-tie"></i> Assign Roles
                                    </a>
                                    <form method="post" action="/admin/users/delete" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= esc($user['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')">Delete</button>
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