<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h2 class="mb-4">Welcome, <?php echo session('user_name'); ?>!</h2>
<div class="row dashboard-cards g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="fas fa-newspaper display-4 text-primary mb-3"></i>
                <h5 class="card-title">Manage News</h5>
                <p class="card-text">Create, edit, and publish news articles for Barind Post.</p>
                <a href="/admin/news" class="btn btn-primary btn-sm">Go to News</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="fas fa-users display-4 text-success mb-3"></i>
                <h5 class="card-title">Manage Users</h5>
                <p class="card-text">View, add, or update user accounts and permissions.</p>
                <a href="/admin/users" class="btn btn-success btn-sm">Go to Users</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <i class="fas fa-shield-alt display-4 text-warning mb-3"></i>
                <h5 class="card-title">Manage Roles</h5>
                <p class="card-text">Assign and manage user roles and access levels.</p>
                <a href="/admin/roles" class="btn btn-warning btn-sm text-white">Go to Roles</a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?> 