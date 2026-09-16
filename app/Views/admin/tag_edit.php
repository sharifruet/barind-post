<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="row mb-4">
    <div class="col-md-6 mx-auto">
        <h2 class="mb-4">Edit Tag</h2>
        <form method="post" action="/admin/tags/edit/<?= esc($tag['id']) ?>">
            <div class="mb-3">
                <label class="form-label">Tag Name</label>
                <input type="text" name="name" class="form-control" value="<?= esc($tag['name']) ?>" required>
            </div>
            <button type="submit" class="btn btn-success">Update</button>
            <a href="/admin/tags" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
<?= $this->endSection() ?> 