<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h2 class="mb-4">Edit Stadium</h2>
<form method="post" action="/admin/sports-events/venues/edit/<?= $venue['id'] ?>"><?= csrf_field() ?>
    <div class="card shadow-sm"><div class="card-body">
        <div class="mb-3"><label class="form-label">Name (Bengali)</label><input type="text" name="name_bn" class="form-control bengali-text" required value="<?= esc($venue['name_bn']) ?>"></div>
        <div class="mb-3"><label class="form-label">Name (English)</label><input type="text" name="name_en" class="form-control" value="<?= esc($venue['name_en'] ?? '') ?>"></div>
        <div class="row">
            <div class="col-md-4 mb-3"><label class="form-label">City</label><input type="text" name="city" class="form-control" value="<?= esc($venue['city'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label">Country</label><input type="text" name="country" class="form-control" value="<?= esc($venue['country'] ?? '') ?>"></div>
            <div class="col-md-4 mb-3"><label class="form-label">Capacity</label><input type="number" name="capacity" class="form-control" value="<?= esc($venue['capacity'] ?? '') ?>"></div>
        </div>
        <div class="mb-3"><label class="form-label">Image URL</label><input type="text" name="image_url" class="form-control" value="<?= esc($venue['image_url'] ?? '') ?>"></div>
        <button type="submit" class="btn btn-primary">Update</button>
        <a href="/admin/sports-events/venues" class="btn btn-secondary">Cancel</a>
    </div></div>
</form>
<?= $this->endSection() ?>
