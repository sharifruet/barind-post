<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php
$isEdit = !empty($event);
$action = $isEdit ? '/admin/sports-events/edit/' . $event['id'] : '/admin/sports-events/create';
$config = $isEdit ? sports_decode_json_field($event['config'] ?? []) : [];
$profilesConfig = config('SportProfiles');
$defaultProfile = $isEdit ? ($event['sport_profile'] ?? 'football') : 'football';
$defaults = $profilesConfig->get($defaultProfile)['default_config'] ?? [];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= $isEdit ? 'Edit Event' : 'Create Sports Event' ?></h2>
    <?php if ($isEdit): ?>
        <a href="/admin/sports-events/manage/<?= $event['id'] ?>" class="btn btn-secondary">Back to Manage</a>
    <?php else: ?>
        <a href="/admin/sports-events" class="btn btn-secondary">Back</a>
    <?php endif; ?>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<form method="post" action="<?= $action ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Basic Information</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Title (Bengali) *</label>
                        <input type="text" name="title_bn" class="form-control bengali-text" required value="<?= esc(old('title_bn', $event['title_bn'] ?? '')) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title (English)</label>
                        <input type="text" name="title_en" class="form-control" value="<?= esc(old('title_en', $event['title_en'] ?? '')) ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Slug *</label>
                            <input type="text" name="slug" class="form-control" required value="<?= esc(old('slug', $event['slug'] ?? '')) ?>" placeholder="fifa-world-cup-2026">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Custom URL (optional)</label>
                            <input type="text" name="custom_url" class="form-control" value="<?= esc(old('custom_url', $event['custom_url'] ?? '')) ?>" placeholder="world-cup">
                            <small class="text-muted">e.g. world-cup → /sports/world-cup</small>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Bengali)</label>
                        <textarea name="description_bn" class="form-control bengali-text" rows="3"><?= esc(old('description_bn', $event['description_bn'] ?? '')) ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Banner Image URL</label>
                            <input type="text" name="banner_image" class="form-control" value="<?= esc(old('banner_image', $event['banner_image'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Logo Image URL</label>
                            <input type="text" name="logo_image" class="form-control" value="<?= esc(old('logo_image', $event['logo_image'] ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Configuration</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Sport Profile *</label>
                        <select name="sport_profile" class="form-select" required>
                            <?php foreach ($profiles as $key => $label): ?>
                                <option value="<?= esc($key) ?>" <?= old('sport_profile', $event['sport_profile'] ?? 'football') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['draft', 'active', 'archived'] as $st): ?>
                                <option value="<?= $st ?>" <?= old('status', $event['status'] ?? 'draft') === $st ? 'selected' : '' ?>><?= ucfirst($st) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?= esc(old('start_date', $event['start_date'] ?? '')) ?>">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?= esc(old('end_date', $event['end_date'] ?? '')) ?>">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">News Tag Slug</label>
                        <input type="text" name="news_tag_slug" class="form-control" value="<?= esc(old('news_tag_slug', $event['news_tag_slug'] ?? '')) ?>">
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="show_in_nav" value="1" id="show_in_nav" <?= old('show_in_nav', $event['show_in_nav'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_in_nav">Show in navigation</label>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="show_homepage_widget" value="1" id="show_homepage_widget" <?= old('show_homepage_widget', $event['show_homepage_widget'] ?? 0) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="show_homepage_widget">Show homepage widget</label>
                    </div>
                    <hr>
                    <p class="small text-muted mb-2">Standing points</p>
                    <div class="row">
                        <div class="col-4 mb-2">
                            <label class="form-label small">Win</label>
                            <input type="number" name="points_win" class="form-control form-control-sm" value="<?= esc(old('points_win', $config['points_win'] ?? $defaults['points_win'] ?? 3)) ?>">
                        </div>
                        <div class="col-4 mb-2">
                            <label class="form-label small">Draw</label>
                            <input type="number" name="points_draw" class="form-control form-control-sm" value="<?= esc(old('points_draw', $config['points_draw'] ?? $defaults['points_draw'] ?? 1)) ?>">
                        </div>
                        <div class="col-4 mb-2">
                            <label class="form-label small">Loss</label>
                            <input type="number" name="points_loss" class="form-control form-control-sm" value="<?= esc(old('points_loss', $config['points_loss'] ?? $defaults['points_loss'] ?? 0)) ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label small">NR points (cricket)</label>
                            <input type="number" name="points_nr" class="form-control form-control-sm" value="<?= esc(old('points_nr', $config['points_nr'] ?? $defaults['points_nr'] ?? 1)) ?>">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label small">Overs (cricket)</label>
                            <input type="number" name="overs" class="form-control form-control-sm" value="<?= esc(old('overs', $config['overs'] ?? $defaults['overs'] ?? 20)) ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 mt-3"><?= $isEdit ? 'Update Event' : 'Create Event' ?></button>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $this->endSection() ?>
