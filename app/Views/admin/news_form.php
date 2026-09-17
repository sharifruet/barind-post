<?php
$isEdit     = isset($news) && $news;
$userRole   = session('user_role');
$isReporter = $userRole === 'reporter';
$title      = $isEdit ? 'Edit article' : 'New article';

// Reporter-role options: the author's own name plus any roles assigned to them.
$reporterRoleModel = new \App\Models\ReporterRoleModel();
$userModel         = new \App\Models\UserModel();
$userReporterRoles = $reporterRoleModel->getUserRoles(session('user_id'));

if ($isEdit) {
    $creator      = $userModel->find($news['author_id']);
    $ownNameLabel = $creator ? $creator['name'] : 'Unknown User';
} else {
    $currentUser  = $userModel->find(session('user_id'));
    $ownNameLabel = $currentUser ? $currentUser['name'] : 'Unknown User';
}
$selectedReporterRole = $isEdit ? ($news['reporterRole'] ?? '') : $ownNameLabel;

$v = static fn (string $key, $default = '') => $isEdit ? esc($news[$key] ?? $default) : esc($default);
?>
<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <div class="page-header__eyebrow"><?= $isEdit ? 'Editing' : 'Compose' ?></div>
        <h1 class="page-header__title"><?= $isEdit ? 'Edit article' : 'New article' ?></h1>
        <?php if ($isEdit): ?>
            <p class="page-header__sub">#<?= esc($news['id']) ?> &middot; <span class="status status--<?= esc($news['status'], 'attr') ?>"><?= esc(ucfirst($news['status'])) ?></span></p>
        <?php endif; ?>
    </div>
    <div class="page-header__actions">
        <?php if ($isEdit && ! empty($news['slug']) && $news['status'] === 'published'): ?>
            <a href="/news/<?= esc(rawurlencode($news['slug']), 'attr') ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-up-right-from-square me-1"></i> View on site
            </a>
        <?php endif; ?>
        <a href="/admin/news" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> All news</a>
    </div>
</div>

<?php if ($isReporter): ?>
    <div class="alert alert-info mb-3">
        <i class="fas fa-circle-info me-1"></i>
        As a reporter you save drafts only — an editor reviews and publishes them.
    </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" action="<?= $isEdit ? '/admin/news/edit/' . esc($news['id']) : '/admin/news/create' ?>"><?= csrf_field() ?>
<div class="row g-3">

    <!-- ============================ Main column ============================ -->
    <div class="col-lg-8">

        <div class="panel">
            <div class="panel__head">
                <div class="panel__title">Headline</div>
            </div>
            <div class="panel__body">

                <div class="field-group">
                    <label class="form-label">Kicker <span class="panel__hint">— short label shown above the headline</span></label>
                    <div class="row g-2">
                        <div class="col-md-8">
                            <select id="kicker-select" class="form-select" onchange="handleKickerSelection()">
                                <option value="">— Choose an existing kicker or create new —</option>
                                <!-- Existing kickers are loaded here -->
                            </select>
                            <input type="text" id="kicker-input" name="kicker" class="form-control bengali-input mt-2"
                                   value="<?= $v('kicker') ?>" placeholder="New kicker text" style="display: none;">
                        </div>
                        <div class="col-md-4">
                            <input type="color" id="kicker-color" name="kicker_color" class="form-control form-control-color"
                                   value="<?= $isEdit && ! empty($news['kicker_color']) ? esc($news['kicker_color']) : '#c8102e' ?>" title="Kicker colour">
                            <div class="form-text">Kicker colour</div>
                        </div>
                    </div>
                </div>

                <div class="field-group">
                    <label class="form-label">Title <span class="req">*</span></label>
                    <input type="text" name="title" class="form-control form-control-lg bengali-input" value="<?= $v('title') ?>" required autofocus>
                </div>

                <div class="field-group">
                    <label class="form-label">Subtitle <span class="panel__hint">— the standfirst under the headline, one sentence; also the summary in lists, search results and social previews</span></label>
                    <input type="text" name="subtitle" class="form-control bengali-input" value="<?= $v('subtitle') ?>" maxlength="255">
                </div>

                <div class="field-group">
                    <label class="form-label">Key points <span class="panel__hint">— one per line, 3–5 short facts; shown as an “এক নজরে” box above the body (a single line is shown as an intro paragraph instead)</span></label>
                    <textarea name="lead_text" class="form-control bengali-input" rows="5" placeholder="প্রতি লাইনে একটি মূল তথ্য"><?= $v('lead_text') ?></textarea>
                </div>

                <div class="field-group">
                    <label class="form-label">Byline</label>
                    <select name="reporterRole" class="form-select bengali-input">
                        <option value="">— Select —</option>
                        <option value="<?= esc($ownNameLabel) ?>" <?= $selectedReporterRole === $ownNameLabel ? 'selected' : '' ?>><?= esc($ownNameLabel) ?></option>
                        <?php foreach ($userReporterRoles as $role): ?>
                            <option value="<?= esc($role['name']) ?>" <?= $selectedReporterRole === $role['name'] ? 'selected' : '' ?>><?= esc($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($userReporterRoles)): ?>
                        <div class="form-text">No reporter roles are assigned to you yet — ask an admin if you need one.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel__head">
                <div class="panel__title">Lead image</div>
                <span class="panel__hint">Upload, reuse an existing image, or link an external one</span>
            </div>
            <!-- The upload scripts inject the preview into this element by id. -->
            <div class="panel__body card-body" id="imagePanelBody">
                <div class="image-source mb-3">
                    <button type="button" id="uploadNewBtn" class="btn btn-primary">
                        <i class="fas fa-upload me-1"></i> Upload new
                    </button>
                    <button type="button" id="selectExistingBtn" class="btn btn-outline-secondary">
                        <i class="fas fa-images me-1"></i> Choose existing
                    </button>
                    <button type="button" id="externalUrlBtn" class="btn btn-outline-info">
                        <i class="fas fa-link me-1"></i> External URL
                    </button>
                </div>

                <div id="externalUrlSection" class="row g-3 mb-3" style="display: none;">
                    <div class="col-12">
                        <label class="form-label">Image URL</label>
                        <input type="text" name="image_url" class="form-control" value="<?= $v('image_url') ?>" placeholder="https://…">
                        <div class="form-text">A direct link to an image hosted elsewhere.</div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Caption</label>
                        <input type="text" name="image_caption" class="form-control bengali-input" value="<?= $v('image_caption') ?>" placeholder="Shown under the image">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alt text</label>
                        <input type="text" name="image_alt_text" class="form-control bengali-input" value="<?= $v('image_alt_text') ?>" placeholder="Describe the image for accessibility and SEO">
                    </div>
                </div>
            </div>
        </div>

        <div class="panel">
            <div class="panel__head">
                <div class="panel__title">Story <span class="req">*</span></div>
                <div class="d-flex gap-2">
                    <button type="button" id="addRelatedBtn" class="btn btn-secondary btn-sm">
                        <i class="fas fa-link me-1"></i> Link a story
                    </button>
                    <button type="button" id="addHtmlBtn" class="btn btn-secondary btn-sm">
                        <i class="fas fa-code me-1"></i> Embed HTML
                    </button>
                </div>
            </div>
            <div class="panel__body">
                <div id="ckeditor-container">
                    <textarea name="content" id="content" class="form-control" rows="15" required tabindex="0" style="display: none;"><?= $v('content') ?></textarea>
                </div>
                <div class="invalid-feedback">Please write the story before saving.</div>
            </div>
        </div>
    </div>

    <!-- ============================ Rail ============================ -->
    <div class="col-lg-4">
        <div class="editor-rail">

            <div class="panel">
                <div class="panel__head"><div class="panel__title">Publish</div></div>
                <div class="panel__body">
                    <?php if ($isReporter): ?>
                        <input type="hidden" name="status" value="draft">
                        <div class="field-group">
                            <label class="form-label">Status</label>
                            <div class="panel__note"><i class="fas fa-circle-info me-1"></i> Saved as a draft for editorial review.</div>
                        </div>
                    <?php else: ?>
                        <div class="field-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" <?= $isEdit && $news['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $isEdit && $news['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                                <option value="archived" <?= $isEdit && $news['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                        <div class="field-group">
                            <label class="form-label">Publish date</label>
                            <input type="datetime-local" name="published_at" class="form-control"
                                   value="<?= $isEdit && ! empty($news['published_at']) ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '' ?>">
                            <div class="form-text">Leave empty to use the moment it's published.</div>
                        </div>
                    <?php endif; ?>

                    <div class="field-group">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured" <?= $isEdit && $news['featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="featured">Featured on the front page</label>
                        </div>
                    </div>

                    <hr class="hr-rule">
                    <div class="editor-actions">
                        <button type="submit" class="btn btn-accent"><i class="fas fa-check me-1"></i> <?= $isEdit ? 'Save changes' : 'Save article' ?></button>
                        <a href="/admin/news" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head"><div class="panel__title">Classification</div></div>
                <div class="panel__body">
                    <div class="field-group">
                        <label class="form-label">Category <span class="req">*</span></label>
                        <select name="category_id" class="form-select bengali-input" required>
                            <option value="">— Select —</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= esc($cat['id']) ?>" <?= $isEdit && $news['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="form-label">Tags <span class="panel__hint">— hold Ctrl/Cmd to pick several</span></label>
                        <select name="tags[]" class="form-select bengali-input" multiple size="7">
                            <?php foreach ($tags as $tag): ?>
                                <option value="<?= esc($tag['id']) ?>" <?= isset($selectedTagIds) && in_array($tag['id'], $selectedTagIds ?? []) ? 'selected' : '' ?>><?= esc($tag['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel__head"><div class="panel__title">Details</div></div>
                <div class="panel__body">
                    <?php if ($isEdit): ?>
                        <div class="field-group">
                            <label class="form-label">Slug</label>
                            <input type="text" name="slug" id="slug" class="form-control" value="<?= esc($news['slug']) ?>">
                            <div class="form-text">Part of the public URL. Change with care — old links will break.</div>
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="slug" id="slug" value="">
                    <?php endif; ?>
                    <div class="field-group">
                        <label class="form-label">Source</label>
                        <input type="text" name="source" class="form-control bengali-input" value="<?= $v('source') ?>" placeholder="e.g. নিজস্ব প্রতিবেদক, BSS">
                    </div>
                    <div class="field-group">
                        <label class="form-label">Dateline</label>
                        <input type="text" name="dateline" class="form-control bengali-input" value="<?= $v('dateline') ?>" placeholder="e.g. রাজশাহী">
                    </div>
                    <div class="row g-2">
                        <div class="col-6 field-group">
                            <label class="form-label">Word count</label>
                            <input type="number" name="word_count" class="form-control" value="<?= $v('word_count') ?>">
                        </div>
                        <div class="col-6 field-group">
                            <label class="form-label">Language</label>
                            <select name="language" class="form-select" required>
                                <option value="bn" <?= ($isEdit && $news['language'] === 'bn') || ! $isEdit ? 'selected' : '' ?>>Bangla</option>
                                <option value="en" <?= $isEdit && $news['language'] === 'en' ? 'selected' : '' ?>>English</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
</form>

<!-- Existing Images Modal -->
<div class="modal fade" id="existingImagesModal" tabindex="-1" aria-labelledby="existingImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="existingImagesModalLabel">Choose an existing image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3" id="existingImagesGrid">
                    <!-- Images are loaded here -->
                </div>
                <div id="existingImagesLoading" class="text-center py-4">
                    <div class="spinner-border" role="status"><span class="visually-hidden">Loading…</span></div>
                </div>
                <div id="existingImagesEmpty" class="empty" style="display: none;">
                    <i class="fas fa-images"></i>
                    No images yet — upload one first.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Upload New Image Modal -->
<div class="modal fade" id="uploadImageModal" tabindex="-1" aria-labelledby="uploadImageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="uploadImageModalLabel">Upload an image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Image file</label>
                        <input type="file" id="imageUpload" class="form-control" accept="image/*">
                        <div class="form-text">Up to 5 MB — JPG, PNG, GIF or WebP.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Caption</label>
                        <input type="text" id="imageCaption" class="form-control bengali-input" placeholder="Shown under the image">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Alt text</label>
                        <input type="text" id="imageAltText" class="form-control bengali-input" placeholder="Describe the image">
                    </div>
                </div>
                <div id="uploadProgress" class="progress mt-3" style="display: none;">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div id="uploadPreview" class="mt-3" style="display: none;">
                    <!-- Upload preview is shown here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="uploadImageBtn" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i> Upload
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Link a story: insert another article's title + link into the body -->
<div class="modal fade" id="relatedStoryModal" tabindex="-1" aria-labelledby="relatedStoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="relatedStoryModalLabel">Link a story</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="search" id="relatedSearch" class="form-control bengali-input mb-3" placeholder="Search published stories by title…" autocomplete="off">
                <div class="form-text mb-2">Inserts an “আরও পড়ুন” block with the story's title and link at the cursor.</div>
                <div class="related-list" id="relatedResults"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn     = document.getElementById('addRelatedBtn');
    var modalEl = document.getElementById('relatedStoryModal');
    var input   = document.getElementById('relatedSearch');
    var list    = document.getElementById('relatedResults');
    if (!btn || !modalEl || !input || !list) return;

    var excludeId = <?= $isEdit ? (int) $news['id'] : 0 ?>;
    var modal = null;   // created lazily: Bootstrap's bundle loads after this script
    var timer;

    function escHtml(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    }

    function load(q) {
        list.innerHTML = '<div class="empty">Searching…</div>';
        fetch('/admin/news/search?q=' + encodeURIComponent(q || '') + '&exclude=' + excludeId, {credentials: 'same-origin'})
            .then(function (r) { return r.json(); })
            .then(function (rows) {
                if (!rows.length) { list.innerHTML = '<div class="empty">No published stories match.</div>'; return; }
                list.innerHTML = rows.map(function (r) {
                    return '<button type="button" class="related-pick" data-slug="' + escHtml(r.slug) + '" data-title="' + escHtml(r.title) + '">'
                        + '<span class="related-pick__title bengali-text">' + escHtml(r.title) + '</span>'
                        + '<span class="related-pick__meta">' + escHtml(r.published_at || '') + '</span>'
                        + '</button>';
                }).join('');
            })
            .catch(function () { list.innerHTML = '<div class="empty">Could not load stories.</div>'; });
    }

    /**
     * Only elements the classic CKEditor build keeps (blockquote, p, strong,
     * a[href]) — anything custom is stripped on the next save. The public page
     * turns this exact shape into a styled callout (render_article_body()).
     */
    function insertRelatedStory(slug, title) {
        var href = '/news/' + encodeURIComponent(slug);
        var html = '<blockquote><p><strong>আরও পড়ুন:</strong> <a href="' + href + '">' + escHtml(title) + '</a></p></blockquote>';

        if (window.ckEditorInstance) {
            var ed = window.ckEditorInstance;
            var view  = ed.data.processor.toView(html);
            var model = ed.data.toModel(view);
            ed.model.insertContent(model);
            ed.editing.view.focus();
        } else {
            var ta = document.querySelector('#content');
            if (ta) {
                var p = ta.selectionStart || ta.value.length;
                ta.value = ta.value.slice(0, p) + html + ta.value.slice(p);
            }
        }
    }

    btn.addEventListener('click', function () {
        if (!modal) modal = new bootstrap.Modal(modalEl);
        input.value = '';
        load('');
        modal.show();
        setTimeout(function () { input.focus(); }, 200);
    });

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { load(input.value.trim()); }, 250);
    });

    list.addEventListener('click', function (e) {
        var pick = e.target.closest('.related-pick');
        if (!pick) return;
        insertRelatedStory(pick.getAttribute('data-slug'), pick.getAttribute('data-title'));
        if (modal) modal.hide();
    });
});
</script>

<!-- AJAX Image Upload Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadBtn = document.getElementById('uploadImageBtn');
    const imageInput = document.getElementById('imageUpload');
    const captionInput = document.getElementById('imageCaption');
    const altTextInput = document.getElementById('imageAltText');
    const progressBar = document.getElementById('uploadProgress');
    const selectExistingBtn = document.getElementById('selectExistingBtn');
    const uploadNewBtn = document.getElementById('uploadNewBtn');
    const externalUrlBtn = document.getElementById('externalUrlBtn');
    const externalUrlSection = document.getElementById('externalUrlSection');
    
    // Show existing image preview if editing
    <?php if ($isEdit && $news['image_url']): ?>
    showExistingImage();
    <?php endif; ?>
    
    // Handle upload new image button
    if (uploadNewBtn) {
        uploadNewBtn.addEventListener('click', function() {
            const modal = new bootstrap.Modal(document.getElementById('uploadImageModal'));
            modal.show();
        });
    }
    
    // Handle existing images selection
    if (selectExistingBtn) {
        selectExistingBtn.addEventListener('click', function() {
            loadExistingImages();
            const modal = new bootstrap.Modal(document.getElementById('existingImagesModal'));
            modal.show();
        });
    }
    
    // Handle external URL button
    if (externalUrlBtn) {
        externalUrlBtn.addEventListener('click', function() {
            externalUrlSection.style.display = 'block';
            this.classList.add('active');
            uploadNewBtn.classList.remove('active');
            selectExistingBtn.classList.remove('active');
        });
    }
    
    // Handle upload new image button click
    if (uploadNewBtn) {
        uploadNewBtn.addEventListener('click', function() {
            uploadNewBtn.classList.add('active');
            selectExistingBtn.classList.remove('active');
            externalUrlBtn.classList.remove('active');
            externalUrlSection.style.display = 'none';
        });
    }
    
    // Handle select existing button click
    if (selectExistingBtn) {
        selectExistingBtn.addEventListener('click', function() {
            selectExistingBtn.classList.add('active');
            uploadNewBtn.classList.remove('active');
            externalUrlBtn.classList.remove('active');
            externalUrlSection.style.display = 'none';
        });
    }
    
    // Handle AJAX image upload
    if (uploadBtn) {
        uploadBtn.addEventListener('click', function() {
            const file = imageInput.files[0];
            if (!file) {
                alert('Please select an image to upload');
                return;
            }
            
            const formData = new FormData();
            formData.append('image', file);
            formData.append('caption', captionInput.value);
            formData.append('alt_text', altTextInput.value);
            
            // Show progress bar
            progressBar.style.display = 'block';
            const progressBarInner = progressBar.querySelector('.progress-bar');
            progressBarInner.style.width = '0%';
            
            // Disable upload button
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
            
            fetch('/image-upload/upload', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update form fields with uploaded image data
                    document.querySelector('input[name="image_url"]').value = data.image.image_path;
                    document.querySelector('input[name="image_caption"]').value = data.image.caption || '';
                    document.querySelector('input[name="image_alt_text"]').value = data.image.alt_text || '';
                    
                    // Show success preview in main form
                    showImagePreview(data.image);
                    
                    // Clear upload form
                    imageInput.value = '';
                    captionInput.value = '';
                    altTextInput.value = '';
                    
                    // Close modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('uploadImageModal'));
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Show success message
                    showAlert('Image uploaded successfully!', 'success');
                } else {
                    showAlert('Upload failed: ' + data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                showAlert('Upload failed. Please try again.', 'danger');
            })
            .finally(() => {
                // Hide progress bar and re-enable button
                progressBar.style.display = 'none';
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload Image';
            });
        });
    }
    
    function showImagePreview(imageData) {
        console.log('showImagePreview called with:', imageData);
        
        let previewSection = document.getElementById('imagePreview');
        if (!previewSection) {
            previewSection = document.createElement('div');
            previewSection.id = 'imagePreview';
            previewSection.className = 'col-md-12 mt-3';
            document.getElementById('imagePanelBody').appendChild(previewSection);
        }
        
        // Add base URL if the image URL is relative
        const fullImageUrl = imageData.preview_url.startsWith('http') ? imageData.preview_url : window.location.origin + '/' + imageData.preview_url;
        console.log('showImagePreview - fullImageUrl:', fullImageUrl);
        
        previewSection.innerHTML = `
            <div class="alert alert-success">
                <h6>Selected Image:</h6>
                <div class="row">
                    <div class="col-md-3">
                        <img src="${fullImageUrl}" class="img-fluid rounded" alt="${imageData.alt_text || ''}" style="max-height: 150px; object-fit: cover;">
                    </div>
                    <div class="col-md-9">
                        <p><strong>Caption:</strong> ${imageData.caption || 'No caption'}</p>
                        <p><strong>Alt Text:</strong> ${imageData.alt_text || 'No alt text'}</p>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImagePreview()">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        console.log('Preview section updated');
    }
    
    function showExistingImage() {
        const imageUrl = '<?= $isEdit ? esc($news['image_url']) : '' ?>';
        const caption = '<?= $isEdit ? esc($news['image_caption']) : '' ?>';
        const altText = '<?= $isEdit ? esc($news['image_alt_text']) : '' ?>';
        
        if (imageUrl) {
            let previewSection = document.getElementById('imagePreview');
            if (!previewSection) {
                previewSection = document.createElement('div');
                previewSection.id = 'imagePreview';
                previewSection.className = 'col-md-12 mt-3';
                document.getElementById('imagePanelBody').appendChild(previewSection);
            }
            
            // Add base URL if the image URL is relative
            const fullImageUrl = imageUrl.startsWith('http') ? imageUrl : window.location.origin + '/' + imageUrl;
            
            previewSection.innerHTML = `
                <div class="alert alert-info">
                    <h6>Current Image:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <img src="${fullImageUrl}" class="img-fluid rounded" alt="${altText}" style="max-height: 150px; object-fit: cover;">
                        </div>
                        <div class="col-md-9">
                            <p><strong>Caption:</strong> ${caption || 'No caption'}</p>
                            <p><strong>Alt Text:</strong> ${altText || 'No alt text'}</p>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeImagePreview()">
                                <i class="fas fa-times"></i> Remove
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        // Insert alert at the top of the form
        const form = document.querySelector('form');
        if (form) {
            form.insertBefore(alertDiv, form.firstChild);
        }
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    function loadExistingImages() {
        const grid = document.getElementById('existingImagesGrid');
        const loading = document.getElementById('existingImagesLoading');
        const empty = document.getElementById('existingImagesEmpty');
        
        // Show loading
        loading.style.display = 'block';
        empty.style.display = 'none';
        grid.innerHTML = '';
        
        console.log('Loading existing images...');
        
        fetch('/image-upload/existing-images', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error('HTTP error! status: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            loading.style.display = 'none';
            
            if (data.success && data.images && data.images.length > 0) {
                console.log('Found', data.images.length, 'images');
                data.images.forEach(image => {
                    const imageCard = createImageCard(image);
                    grid.appendChild(imageCard);
                });
            } else {
                console.log('No images found or error in response');
                empty.style.display = 'block';
                if (data.message) {
                    empty.innerHTML = '<p class="text-warning">' + data.message + '</p>';
                }
            }
        })
        .catch(error => {
            console.error('Error loading images:', error);
            loading.style.display = 'none';
            empty.style.display = 'block';
            empty.innerHTML = '<p class="text-danger">Error loading images: ' + error.message + '</p>';
        });
    }
    
    function createImageCard(image) {
        const col = document.createElement('div');
        col.className = 'col-md-4 col-lg-3 mb-3';
        
        // Add base URL if the image URL is relative
        const fullImageUrl = image.preview_url.startsWith('http') ? image.preview_url : window.location.origin + '/' + image.preview_url;
        
        col.innerHTML = `
            <div class="card h-100 image-select-card" data-image-id="${image.id}">
                <img src="${fullImageUrl}" class="card-img-top" alt="${image.alt_text || ''}" style="height: 150px; object-fit: cover;">
                <div class="card-body">
                    <h6 class="card-title">${image.caption || 'No caption'}</h6>
                    <p class="card-text small text-muted">
                        <strong>Alt Text:</strong> ${image.alt_text || 'No alt text'}<br>
                        <strong>Usage:</strong> ${image.usage_count} times<br>
                        <strong>Uploaded:</strong> ${new Date(image.uploaded_at).toLocaleDateString()}
                    </p>
                                            <button type="button" class="btn btn-primary btn-sm w-100 select-image-btn" 
                                data-image-id="${image.id}" 
                                data-image-path="${image.image_path}" 
                                data-caption="${image.caption || ''}" 
                                data-alt-text="${image.alt_text || ''}">
                            <i class="fas fa-check"></i> Select
                        </button>
                </div>
            </div>
        `;
        
                    // Add event listener to the button
            const button = col.querySelector('.select-image-btn');
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const imageId = this.getAttribute('data-image-id');
                const imagePath = this.getAttribute('data-image-path');
                const caption = this.getAttribute('data-caption');
                const altText = this.getAttribute('data-alt-text');
                selectExistingImage(imageId, imagePath, caption, altText);
            });
        
        return col;
    }
});

function selectExistingImage(imageId, imagePath, caption, altText) {
    console.log('selectExistingImage called with:', { imageId, imagePath, caption, altText });
    
    // Update form fields
    document.querySelector('input[name="image_url"]').value = imagePath;
    document.querySelector('input[name="image_caption"]').value = caption;
    document.querySelector('input[name="image_alt_text"]').value = altText;
    
    // Show preview with proper base URL handling
    const fullImageUrl = imagePath.startsWith('http') ? imagePath : window.location.origin + '/' + imagePath;
    console.log('Full image URL:', fullImageUrl);
    
    showImagePreview({
        image_path: imagePath,
        caption: caption,
        alt_text: altText,
        preview_url: fullImageUrl
    });
    
    // Close modal immediately
    const modalElement = document.getElementById('existingImagesModal');
    if (modalElement) {
        // Try to get existing instance first
        let bsModal = bootstrap.Modal.getInstance(modalElement);
        if (!bsModal) {
            // Create new instance if none exists
            bsModal = new bootstrap.Modal(modalElement);
        }
        bsModal.hide();
        console.log('Modal closed using Bootstrap Modal');
    }
    
    // Show success message
    showAlert('Image selected successfully!', 'success');
}

function removeImagePreview() {
    const previewSection = document.getElementById('imagePreview');
    if (previewSection) {
        previewSection.remove();
    }
    
    // Clear form fields
    document.querySelector('input[name="image_url"]').value = '';
    document.querySelector('input[name="image_caption"]').value = '';
    document.querySelector('input[name="image_alt_text"]').value = '';
}
</script>

<!-- CKEditor 5 with multiple fallback sources -->
<script>
// Tweet Embed Plugin for CKEditor
class TweetEmbedPlugin {
    constructor(editor) {
        this.editor = editor;
        this._defineSchema();
        this._defineConverters();
        this._addToolbarButton();
    }

    _defineSchema() {
        const schema = this.editor.model.schema;
        
        schema.register('tweetEmbed', {
            isObject: true,
            allowWhere: '$block',
            allowAttributes: ['tweetUrl', 'tweetId']
        });
    }

    _defineConverters() {
        const conversion = this.editor.conversion;
        
        // Downcast converter
        conversion.for('downcast').elementToElement({
            model: 'tweetEmbed',
            view: (modelElement, viewWriter) => {
                const tweetUrl = modelElement.getAttribute('tweetUrl');
                const tweetId = modelElement.getAttribute('tweetId');
                
                const tweetContainer = viewWriter.createContainerElement('div', {
                    class: 'tweet-embed-container',
                    'data-tweet-url': tweetUrl,
                    'data-tweet-id': tweetId
                });
                
                const tweetEmbed = viewWriter.createRawElement('div', {
                    class: 'tweet-embed-placeholder'
                }, function(domElement) {
                    // Load Twitter widget script if not already loaded
                    if (!window.twttr) {
                        const script = document.createElement('script');
                        script.src = 'https://platform.twitter.com/widgets.js';
                        script.charset = 'utf-8';
                        document.head.appendChild(script);
                    }
                    
                    // Create tweet embed
                    setTimeout(() => {
                        if (window.twttr && tweetId) {
                            window.twttr.widgets.createTweet(tweetId, domElement, {
                                conversation: 'none',
                                cards: 'visible',
                                theme: 'light'
                            });
                        } else if (tweetUrl) {
                            // Fallback for URL-based embedding
                            domElement.innerHTML = `
                                <div style="border: 1px solid #e1e8ed; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                                    <div style="display: flex; align-items: center; margin-bottom: 10px;">
                                        <div style="width: 48px; height: 48px; background: #1da1f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="white">
                                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <div style="font-weight: bold; color: #14171a;">Twitter</div>
                                            <div style="color: #657786; font-size: 14px;">@twitter</div>
                                        </div>
                                    </div>
                                    <div style="color: #14171a; line-height: 1.4;">
                                        <a href="${tweetUrl}" target="_blank" style="color: #1da1f2; text-decoration: none;">
                                            View Tweet on Twitter
                                        </a>
                                    </div>
                                </div>
                            `;
                        }
                    }, 100);
                });
                
                return tweetContainer;
            }
        });
        
        // Upcast converter
        conversion.for('upcast').elementToElement({
            model: 'tweetEmbed',
            view: {
                name: 'div',
                classes: 'tweet-embed-container'
            }
        });
    }

    _addToolbarButton() {
        const editor = this.editor;
        
        editor.ui.componentFactory.add('tweetEmbed', locale => {
            const view = new editor.ui.ButtonView(locale);
            
            view.set({
                label: 'Embed Tweet',
                icon: '<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
                tooltip: true
            });
            
            view.on('execute', () => {
                this._showTweetDialog();
            });
            
            return view;
        });
    }

    _showTweetDialog() {
        const editor = this.editor;
        
        // Create modal dialog
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        
        const dialog = document.createElement('div');
        dialog.style.cssText = `
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        `;
        
        dialog.innerHTML = `
            <h3 style="margin-top: 0; color: #333;">Embed Tweet</h3>
            <p style="color: #666; margin-bottom: 20px;">Enter a Twitter URL or Tweet ID to embed:</p>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Tweet URL or ID:</label>
                <input type="text" id="tweetInput" placeholder="https://twitter.com/user/status/123456789 or 123456789" 
                       style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button id="cancelTweet" style="padding: 8px 16px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button id="embedTweet" style="padding: 8px 16px; background: #1da1f2; color: white; border: none; border-radius: 4px; cursor: pointer;">Embed Tweet</button>
            </div>
        `;
        
        modal.appendChild(dialog);
        document.body.appendChild(modal);
        
        const input = dialog.querySelector('#tweetInput');
        const embedBtn = dialog.querySelector('#embedTweet');
        const cancelBtn = dialog.querySelector('#cancelTweet');
        
        // Focus input
        input.focus();
        
        // Handle embed button
        embedBtn.addEventListener('click', () => {
            const value = input.value.trim();
            if (value) {
                this._insertTweet(value);
                document.body.removeChild(modal);
            } else {
                alert('Please enter a valid Twitter URL or Tweet ID');
            }
        });
        
        // Handle cancel button
        cancelBtn.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        // Handle Enter key
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                embedBtn.click();
            }
        });
        
        // Handle Escape key
        modal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.body.removeChild(modal);
            }
        });
        
        // Close on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }

    _insertTweet(value) {
        const editor = this.editor;
        
        // Extract tweet ID from URL or use as ID
        let tweetId = null;
        let tweetUrl = value;
        
        // Check if it's a URL
        if (value.includes('twitter.com') || value.includes('x.com')) {
            const urlMatch = value.match(/\/status\/(\d+)/);
            if (urlMatch) {
                tweetId = urlMatch[1];
            }
        } else {
            // Assume it's a tweet ID
            tweetId = value;
        }
        
        editor.model.change(writer => {
            const tweetElement = writer.createElement('tweetEmbed', {
                tweetUrl: tweetUrl,
                tweetId: tweetId
            });
            
            editor.model.insertContent(tweetElement);
        });
    }
}

// Function to load CKEditor with multiple fallback sources
function loadCKEditor() {
    const sources = [
        'https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js',
        'https://cdnjs.cloudflare.com/ajax/libs/ckeditor5/40.2.0/classic/ckeditor.js',
        'https://unpkg.com/@ckeditor/ckeditor5-build-classic@40.2.0/build/ckeditor.js'
    ];
    
    let currentSource = 0;
    
    function tryLoad() {
        if (currentSource >= sources.length) {
            // All sources failed, use fallback
            console.log('All CKEditor sources failed, using fallback textarea');
            const textarea = document.querySelector('#content');
            if (textarea) {
                textarea.style.minHeight = '400px';
                textarea.style.fontFamily = 'monospace';
                textarea.style.padding = '15px';
                textarea.style.border = '1px solid #ccc';
                textarea.style.borderRadius = '4px';
                textarea.placeholder = 'Enter your content here... (Rich text editor unavailable)';
                textarea.style.display = 'block';
                textarea.style.visibility = 'visible';
                textarea.style.opacity = '1';
                textarea.removeAttribute('disabled');
                textarea.removeAttribute('readonly');
                
                // Add some basic formatting buttons
                const toolbar = document.createElement('div');
                toolbar.style.marginBottom = '10px';
                toolbar.innerHTML = `
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatText('bold')"><strong>B</strong></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatText('italic')"><em>I</em></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="formatText('underline')"><u>U</u></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-1" onclick="insertText('<br>')">Line Break</button>
                `;
                textarea.parentNode.insertBefore(toolbar, textarea);
            }
            return;
        }
        
        const script = document.createElement('script');
        script.src = sources[currentSource];
        script.onload = function() {
            console.log('CKEditor loaded from:', sources[currentSource]);
            // Try to create CKEditor
            if (typeof ClassicEditor !== 'undefined') {
                            ClassicEditor.create(document.querySelector('#content'), {
                // Enhanced toolbar with more features
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                    'alignment', '|',
                    'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'outdent', 'indent', '|',
                    'blockQuote', 'code', 'codeBlock', '|',
                    'insertTable', 'tableColumn', 'tableRow', 'mergeTableCells', '|',
                    'imageUpload', 'imageStyle:full', 'imageStyle:side', '|',
                    'horizontalLine', 'specialCharacters', '|',
                    'undo', 'redo'
                ],
                // Image upload configuration
                simpleUpload: {
                    uploadUrl: '/image-upload/upload',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                },
                // Table configuration
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells',
                        'tableProperties',
                        'tableCellProperties'
                    ]
                },
                // Heading configuration
                heading: {
                    options: [
                        { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                        { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                        { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                        { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
                    ]
                },
                // Font size options
                fontSize: {
                    options: [10, 12, 14, 'default', 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 48, 60, 72, 96]
                },
                // Font family options
                fontFamily: {
                    options: [
                        'default',
                        'Arial, Helvetica, sans-serif',
                        'Courier New, Courier, monospace',
                        'Georgia, serif',
                        'Lucida Sans Unicode, Lucida Grande, sans-serif',
                        'Tahoma, Geneva, sans-serif',
                        'Times New Roman, Times, serif',
                        'Trebuchet MS, Helvetica, sans-serif',
                        'Verdana, Geneva, sans-serif'
                    ]
                },
                // Image styles
                image: {
                    styles: [
                        'full',
                        'side',
                        'alignLeft',
                        'alignCenter',
                        'alignRight'
                    ],
                    resizeOptions: [
                        {
                            name: 'resizeImage:original',
                            value: null,
                            label: 'Original'
                        },
                        {
                            name: 'resizeImage:50',
                            value: '50',
                            label: '50%'
                        },
                        {
                            name: 'resizeImage:75',
                            value: '75',
                            label: '75%'
                        }
                    ],
                    resizeUnit: '%'
                },
                // Custom plugins - disabled for now to fix loading issue
                // extraPlugins: [TweetEmbedPlugin]
            })
                .then(editor => {
                    console.log('CKEditor initialized successfully');
                    
                    // Store editor instance globally for sync functionality
                    window.ckEditorInstance = editor;
                    
                    // Add custom upload adapter for better debugging
                    editor.plugins.get('FileRepository').createUploadAdapter = function(loader) {
                        console.log('Creating upload adapter for CKEditor');
                        return {
                            upload: function() {
                                console.log('Upload started in CKEditor');
                                return loader.file.then(file => {
                                    console.log('File to upload:', file.name, file.size);
                                    const formData = new FormData();
                                    formData.append('upload', file);
                                    
                                    return fetch('/image-upload/upload', {
                                        method: 'POST',
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest'
                                        },
                                        body: formData
                                    });
                                }).then(response => {
                                    console.log('Upload response status:', response.status);
                                    return response.json();
                                }).then(data => {
                                    console.log('Upload response data:', data);
                                    if (data.error) {
                                        console.error('Upload error:', data.error.message);
                                        throw new Error(data.error.message);
                                    }
                                    return {
                                        default: data.url
                                    };
                                }).catch(error => {
                                    console.error('Upload failed:', error);
                                    throw error;
                                });
                            }
                        };
                    };
                    
                    // Ensure the editor content is properly synced with the textarea for form submission
                    editor.model.document.on('change:data', () => {
                        const data = editor.getData();
                        document.querySelector('#content').value = data;
                    });
                    
                    // Handle form submission to ensure content is included
                    const form = document.querySelector('form');
                    if (form) {
                        form.addEventListener('submit', function(e) {
                            // Update textarea with editor content before submission
                            const editorData = editor.getData();
                            document.querySelector('#content').value = editorData;
                            
                            // Basic validation
                            if (!editorData.trim()) {
                                e.preventDefault();
                                alert('Please provide content for the news article.');
                                editor.focus();
                                return false;
                            }
                        });
                    }
                })
                .catch(error => {
                    console.error('CKEditor initialization failed:', error);
                    tryLoad(); // Try next source
                });
            } else {
                tryLoad(); // Try next source
            }
        };
        script.onerror = function() {
            console.log('Failed to load CKEditor from:', sources[currentSource]);
            currentSource++;
            tryLoad();
        };
        document.head.appendChild(script);
    }
    
    tryLoad();
}

// Load CKEditor when page is ready
document.addEventListener('DOMContentLoaded', loadCKEditor);

// Tweet embed functionality
document.addEventListener('DOMContentLoaded', function() {
    const addTweetBtn = document.getElementById('addTweetBtn');
    
    const addHtmlBtn = document.getElementById('addHtmlBtn');
    if (addHtmlBtn) {
        addHtmlBtn.addEventListener('click', function() {
            showHtmlDialog();
        });
    }
    

    
    function showHtmlDialog() {
        // Create modal dialog
        const modal = document.createElement('div');
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        `;
        
        const dialog = document.createElement('div');
        dialog.style.cssText = `
            background: white;
            padding: 30px;
            border-radius: 8px;
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        `;
        
        dialog.innerHTML = `
            <h3 style="margin-top: 0; color: #333;">Add HTML Embed</h3>
            <p style="color: #666; margin-bottom: 20px;">Paste any HTML code to embed it in your content:</p>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">HTML Code:</label>
                <textarea id="htmlInput" placeholder="<div>Your HTML code here...</div>" 
                       style="width: 100%; height: 200px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; font-family: monospace; resize: vertical;"></textarea>
                <small style="color: #666;">You can embed iframes, embeds, custom HTML, or any other HTML content.</small>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Preview:</label>
                <div id="htmlPreview" style="border: 1px solid #ddd; border-radius: 4px; padding: 15px; min-height: 100px; background: #f8f9fa;">
                    <p style="color: #666; text-align: center; margin: 0;">Preview will appear here...</p>
                </div>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button id="cancelHtml" style="padding: 8px 16px; border: 1px solid #ddd; background: #f8f9fa; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button id="embedHtml" style="padding: 8px 16px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Add HTML</button>
            </div>
        `;
        
        modal.appendChild(dialog);
        document.body.appendChild(modal);
        
        const input = dialog.querySelector('#htmlInput');
        const embedBtn = dialog.querySelector('#embedHtml');
        const cancelBtn = dialog.querySelector('#cancelHtml');
        const preview = dialog.querySelector('#htmlPreview');
        
        // Focus input
        input.focus();
        
        // Live preview
        input.addEventListener('input', function() {
            const html = this.value.trim();
            if (html) {
                preview.innerHTML = html;
            } else {
                preview.innerHTML = '<p style="color: #666; text-align: center; margin: 0;">Preview will appear here...</p>';
            }
        });
        
        // Handle embed button
        embedBtn.addEventListener('click', () => {
            const value = input.value.trim();
            if (value) {
                insertHtmlEmbed(value);
                document.body.removeChild(modal);
            } else {
                alert('Please enter HTML code to embed');
            }
        });
        
        // Handle cancel button
        cancelBtn.addEventListener('click', () => {
            document.body.removeChild(modal);
        });
        
        // Handle Escape key
        modal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.body.removeChild(modal);
            }
        });
        
        // Close on outside click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                document.body.removeChild(modal);
            }
        });
    }
    
    function insertHtmlEmbed(htmlCode) {
        // Create clean HTML embed container
        const htmlEmbed = `
            <div class="html-embed-container" style="margin: 20px 0;">
                ${htmlCode}
            </div>
        `;
        
        // Insert into CKEditor if available
        if (window.ckEditorInstance) {
            const editor = window.ckEditorInstance;
            const viewFragment = editor.data.processor.toView(htmlEmbed);
            const modelFragment = editor.data.toModel(viewFragment);
            editor.model.insertContent(modelFragment);
            
            console.log('HTML embed inserted successfully');
        } else {
            // Fallback: insert into textarea
            const textarea = document.querySelector('#content');
            if (textarea) {
                const cursorPos = textarea.selectionStart;
                const textBefore = textarea.value.substring(0, cursorPos);
                const textAfter = textarea.value.substring(cursorPos);
                textarea.value = textBefore + htmlEmbed + textAfter;
                textarea.focus();
            }
        }
    }
    

});

// Basic text formatting functions for fallback
function formatText(type) {
    const textarea = document.querySelector('#content');
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const selectedText = textarea.value.substring(start, end);
    
    let formattedText = '';
    switch(type) {
        case 'bold':
            formattedText = `<strong>${selectedText}</strong>`;
            break;
        case 'italic':
            formattedText = `<em>${selectedText}</em>`;
            break;
        case 'underline':
            formattedText = `<u>${selectedText}</u>`;
            break;
    }
    
    textarea.value = textarea.value.substring(0, start) + formattedText + textarea.value.substring(end);
    textarea.focus();
}

function insertText(text) {
    const textarea = document.querySelector('#content');
    const start = textarea.selectionStart;
    textarea.value = textarea.value.substring(0, start) + text + textarea.value.substring(start);
    textarea.focus();
}
</script>

<script>
// Form validation to ensure content field is always focusable
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const contentField = document.querySelector('#content');
    
    if (form && contentField) {
        // Ensure content field is always accessible
        contentField.style.display = 'block';
        contentField.style.visibility = 'visible';
        contentField.style.opacity = '1';
        contentField.removeAttribute('disabled');
        contentField.removeAttribute('readonly');
        
        // Add form validation
        form.addEventListener('submit', function(e) {
            const content = contentField.value.trim();
            if (!content) {
                e.preventDefault();
                contentField.focus();
                contentField.classList.add('is-invalid');
                alert('Please provide content for the news article.');
                return false;
            } else {
                contentField.classList.remove('is-invalid');
            }
            

        });
        
        // Remove invalid class when user starts typing
        contentField.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
    }
});

// Auto-generate unique code for clean URLs (only in edit mode)
document.querySelector('input[name="title"]').addEventListener('input', function() {
    const slugField = document.getElementById('slug');
    
    // Only generate slug if the field is visible (edit mode)
    if (slugField.type === 'text') {
        // Generate 10-character random alphanumeric string (including lowercase)
        const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let randomString = '';
        
        for (let i = 0; i < 10; i++) {
            randomString += characters.charAt(Math.floor(Math.random() * characters.length));
        }
        
        // Create unique code: 10-character random (no prefix)
        const uniqueCode = randomString;
        
        slugField.value = uniqueCode;
    }
});

// Kicker Management JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const kickerSelect = document.getElementById('kicker-select');
    const kickerInput = document.getElementById('kicker-input');
    const kickerColor = document.getElementById('kicker-color');
    
    // Load existing kickers
    loadKickers();
    
    // Handle manual input in text field
    kickerInput.addEventListener('input', function() {
        if (this.value.trim()) {
            kickerSelect.value = '';
        }
    });
    
    // Add option to create new kicker
    kickerSelect.addEventListener('focus', function() {
        if (this.value === '' && !this.querySelector('option[value="new"]')) {
            const newOption = document.createElement('option');
            newOption.value = 'new';
            newOption.textContent = '✏️ Create new kicker...';
            this.appendChild(newOption);
        }
    });
});

function loadKickers() {
    fetch('/admin/kickers/api')
        .then(response => response.json())
        .then(kickers => {
            const kickerSelect = document.getElementById('kicker-select');
            
            // Clear existing options except the first one
            kickerSelect.innerHTML = '<option value="">-- Choose from existing or create new --</option>';
            
            // Add existing kickers
            kickers.forEach(kicker => {
                const option = document.createElement('option');
                option.value = kicker.text;
                option.textContent = `${kicker.text} (${kicker.usage_count} uses)`;
                option.setAttribute('data-color', kicker.color);
                kickerSelect.appendChild(option);
            });
            
            // Add create new option
            const newOption = document.createElement('option');
            newOption.value = 'new';
            newOption.textContent = '✏️ Create new kicker...';
            kickerSelect.appendChild(newOption);
            
            // Set current value if editing
            <?php if ($isEdit && !empty($news['kicker'])): ?>
                kickerSelect.value = '<?= esc($news['kicker']) ?>';
                handleKickerSelection();
            <?php endif; ?>
        })
        .catch(error => {
            console.error('Error loading kickers:', error);
        });
}

function handleKickerSelection() {
    const kickerSelect = document.getElementById('kicker-select');
    const kickerInput = document.getElementById('kicker-input');
    const kickerColor = document.getElementById('kicker-color');
    
    const selectedValue = kickerSelect.value;
    
    if (selectedValue === 'new') {
        // Show text input for new kicker
        kickerSelect.style.display = 'none';
        kickerInput.style.display = 'block';
        kickerInput.focus();
        kickerInput.value = '';
    } else if (selectedValue === '') {
        // Clear everything
        kickerInput.style.display = 'none';
        kickerSelect.style.display = 'block';
        kickerInput.value = '';
        kickerColor.value = '#dc3545';
    } else {
        // Use existing kicker
        kickerInput.style.display = 'none';
        kickerSelect.style.display = 'block';
        kickerInput.value = selectedValue;
        
        // Auto-fill color
        const selectedOption = kickerSelect.options[kickerSelect.selectedIndex];
        const color = selectedOption.getAttribute('data-color');
        if (color) {
            kickerColor.value = color;
        }
    }
}

// Handle Enter key in kicker input to go back to dropdown
document.getElementById('kicker-input').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        this.style.display = 'none';
        document.getElementById('kicker-select').style.display = 'block';
        document.getElementById('kicker-select').value = this.value;
    }
});

// Handle blur event to go back to dropdown if empty
document.getElementById('kicker-input').addEventListener('blur', function() {
    if (!this.value.trim()) {
        this.style.display = 'none';
        document.getElementById('kicker-select').style.display = 'block';
        document.getElementById('kicker-select').value = '';
    }
});

</script>
<?= $this->endSection() ?> 