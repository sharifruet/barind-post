<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h2 class="mb-4">Edit Participant</h2>
<form method="post" action="/admin/sports-events/participants/edit/<?= $participant['id'] ?>">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <label class="form-label">Name (Bengali)</label>
                <input type="text" name="name_bn" class="form-control bengali-text" required value="<?= esc($participant['name_bn']) ?>">
            </div>
            <div class="mb-3">
                <label class="form-label">Name (English)</label>
                <input type="text" name="name_en" class="form-control" value="<?= esc($participant['name_en'] ?? '') ?>">
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Short Code</label>
                    <input type="text" name="short_code" class="form-control" value="<?= esc($participant['short_code'] ?? '') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="team" <?= $participant['type'] === 'team' ? 'selected' : '' ?>>Team</option>
                        <option value="individual" <?= $participant['type'] === 'individual' ? 'selected' : '' ?>>Individual</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Flag URL</label>
                    <input type="text" name="flag_url" class="form-control" value="<?= esc($participant['flag_url'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="/admin/sports-events/participants" class="btn btn-secondary">Cancel</a>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
