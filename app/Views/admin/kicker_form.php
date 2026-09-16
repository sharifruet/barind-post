<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><?= isset($kicker) ? 'Edit Kicker' : 'Create New Kicker' ?></h2>
    <a href="/admin/kickers" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Kickers
    </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('errors')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Kicker Details</h5>
            </div>
            <div class="card-body">
                <form method="post" action="<?= isset($kicker) ? '/admin/kickers/edit/' . $kicker['id'] : '/admin/kickers/create' ?>">
                    <div class="mb-3">
                        <label for="text" class="form-label">Kicker Text <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control <?= session()->getFlashdata('error') ? 'is-invalid' : '' ?>" 
                               id="text" 
                               name="text" 
                               value="<?= old('text', isset($kicker) ? $kicker['text'] : '') ?>" 
                               required
                               maxlength="255">
                        <div class="form-text">Enter the kicker text that will appear above headlines</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="color" class="form-label">Kicker Color <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <input type="color" 
                                       class="form-control form-control-color <?= session()->getFlashdata('error') ? 'is-invalid' : '' ?>" 
                                       id="color" 
                                       name="color" 
                                       value="<?= old('color', isset($kicker) ? $kicker['color'] : '#dc3545') ?>" 
                                       required>
                            </div>
                            <div class="col-md-6">
                                <input type="text" 
                                       class="form-control" 
                                       id="color-hex" 
                                       value="<?= old('color', isset($kicker) ? $kicker['color'] : '#dc3545') ?>" 
                                       placeholder="#dc3545"
                                       readonly>
                            </div>
                        </div>
                        <div class="form-text">Choose a color for the kicker text</div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= isset($kicker) ? 'Update' : 'Create' ?> Kicker
                        </button>
                        <a href="/admin/kickers" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Preview</h5>
            </div>
            <div class="card-body">
                <div class="preview-container">
                    <div id="kicker-preview" class="kicker-preview" style="color: <?= old('color', isset($kicker) ? $kicker['color'] : '#dc3545') ?>;">
                        <?= old('text', isset($kicker) ? $kicker['text'] : 'বিশেষ খবর') ?>
                    </div>
                    <h4 class="mt-3 mb-2">Sample Headline</h4>
                    <p class="text-muted small">This is how your kicker will appear above a news headline</p>
                </div>
                
                <?php if (isset($kicker)): ?>
                    <hr>
                    <div class="kicker-stats">
                        <h6>Usage Statistics</h6>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="stat-item">
                                    <strong><?= $kicker['usage_count'] ?></strong>
                                    <small class="text-muted d-block">Times Used</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-item">
                                    <strong><?= date('M d, Y', strtotime($kicker['created_at'])) ?></strong>
                                    <small class="text-muted d-block">Created</small>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const textInput = document.getElementById('text');
    const colorInput = document.getElementById('color');
    const colorHex = document.getElementById('color-hex');
    const preview = document.getElementById('kicker-preview');
    
    // Update preview when text changes
    textInput.addEventListener('input', function() {
        preview.textContent = this.value || 'বিশেষ খবর';
    });
    
    // Update preview when color changes
    colorInput.addEventListener('input', function() {
        const color = this.value;
        preview.style.color = color;
        colorHex.value = color;
    });
    
    // Sync hex input with color picker
    colorHex.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            colorInput.value = this.value;
            preview.style.color = this.value;
        }
    });
    
    // Initialize preview
    if (textInput.value) {
        preview.textContent = textInput.value;
    }
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const text = document.getElementById('text').value.trim();
    const color = document.getElementById('color').value;
    
    if (!text) {
        e.preventDefault();
        alert('Please enter a kicker text.');
        document.getElementById('text').focus();
        return false;
    }
    
    if (!color || !color.match(/^#[0-9A-Fa-f]{6}$/)) {
        e.preventDefault();
        alert('Please select a valid color.');
        document.getElementById('color').focus();
        return false;
    }
});
</script>

<style>
.kicker-preview {
    font-size: 1.1em;
    font-weight: bold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
    margin-bottom: 0.5rem;
    padding: 0.5rem 0.75rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
    border-left: 3px solid currentColor;
}

.preview-container {
    min-height: 100px;
    padding: 1rem;
    background-color: #f8f9fa;
    border-radius: 0.5rem;
    border: 1px solid #e9ecef;
}

.stat-item {
    text-align: center;
    padding: 0.5rem;
    background-color: #f8f9fa;
    border-radius: 0.375rem;
}

.form-control-color {
    height: 3rem;
    cursor: pointer;
}

#color-hex {
    font-family: monospace;
    font-size: 0.9em;
}
</style>

<?= $this->endSection() ?>
