<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Assign Reporter Roles</h1>
    <a href="/admin/users" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
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

<!-- Assign Reporter Roles Form -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Assign Reporter Roles to: <?= esc($user['name']) ?> (<?= esc($user['email']) ?>)</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/reporter-roles/assign/<?= $user['id'] ?>">
            <div class="row">
                <div class="col-12">
                    <p class="text-muted mb-3">Select the reporter roles you want to assign to this user:</p>
                    
                    <div class="row">
                        <?php foreach ($allRoles as $role): ?>
                        <div class="col-md-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       id="role_<?= $role['id'] ?>" 
                                       name="reporter_roles[]" 
                                       value="<?= $role['id'] ?>"
                                       <?= in_array($role['id'], $userRoleIds) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                    <strong><?= esc($role['name']) ?></strong>
                                    <?php if ($role['description']): ?>
                                        <br><small class="text-muted"><?= esc($role['description']) ?></small>
                                    <?php endif; ?>
                                </label>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (empty($allRoles)): ?>
                    <div class="alert alert-info">
                        No reporter roles available. Please create some reporter roles first.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="row mt-3">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary" <?= empty($allRoles) ? 'disabled' : '' ?>>
                        <i class="fas fa-save"></i> Save Assignments
                    </button>
                    <a href="/admin/users" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
