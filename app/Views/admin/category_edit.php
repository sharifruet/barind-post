<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-6 mx-auto">
        <h2 class="mb-4">Edit Category</h2>
        <form method="post" action="/admin/categories/edit/<?= esc($category['id']) ?>">
            <div class="mb-3">
                <label class="form-label">Category Name</label>
                <input type="text" name="name" class="form-control" value="<?= esc($category['name']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="<?= esc($category['slug']) ?>" required>
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="isSpecial" value="1" id="isSpecial" <?= isset($category['isSpecial']) && $category['isSpecial'] ? 'checked' : '' ?>>
                    <label class="form-check-label" for="isSpecial">
                        Special Category (shows on second line of header)
                    </label>
                </div>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="/admin/categories" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?> 