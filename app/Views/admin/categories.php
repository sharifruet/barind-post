<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-8 mx-auto">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Manage Categories</h2>
            <a href="/admin" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        <form method="post" action="/admin/categories/add" class="row g-3 mb-4">
            <div class="col-md-4">
                <input type="text" name="name" class="form-control" placeholder="Category Name" required>
            </div>
            <div class="col-md-4">
                <input type="text" name="slug" class="form-control" placeholder="Slug (e.g. politics)" required>
            </div>
            <div class="col-md-2">
                <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="isSpecial" value="1" id="isSpecial">
                    <label class="form-check-label" for="isSpecial">
                        Special
                    </label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Add Category</button>
            </div>
        </form>
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Existing Categories</h5>
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 8%">ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th style="width: 10%">Special</th>
                            <th style="width: 15%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): ?>
                            <tr>
                                <td><?= esc($cat['id']) ?></td>
                                <td><?= esc($cat['name']) ?></td>
                                <td><?= esc($cat['slug']) ?></td>
                                <td>
                                    <?php if (isset($cat['isSpecial']) && $cat['isSpecial']): ?>
                                        <span class="badge bg-warning">Special</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Regular</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="/admin/categories/edit/<?= esc($cat['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                                    <form method="post" action="/admin/categories/delete" style="display:inline;">
                                        <input type="hidden" name="id" value="<?= esc($cat['id']) ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this category?')">Delete</button>
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