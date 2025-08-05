<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<style>
/* Ensure content textarea is always visible and focusable */
#content {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    position: relative !important;
    z-index: 1 !important;
}

#content:focus {
    outline: 2px solid #007bff !important;
    outline-offset: 2px !important;
}

#content.is-invalid {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Ensure CKEditor doesn't hide the original textarea completely */
.ck-editor__editable {
    min-height: 200px !important;
}

/* Image selection modal styles */
.image-select-card {
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.image-select-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.image-select-card .card-img-top {
    transition: opacity 0.2s;
}

.image-select-card:hover .card-img-top {
    opacity: 0.8;
}

#existingImagesModal .modal-dialog {
    max-width: 90%;
}

#existingImagesGrid {
    max-height: 60vh;
    overflow-y: auto;
}

/* Button active states */
.btn.active {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.btn-outline-secondary.active {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

.btn-outline-info.active {
    background-color: #0dcaf0;
    border-color: #0dcaf0;
    color: white;
}

/* CKEditor container */
#ckeditor-container {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    min-height: 400px;
}

/* Hidden textarea for form submission */
#content {
    display: none !important;
}
</style>
<?php 
$isEdit = isset($news) && $news; 
$userRole = session('user_role');
$isReporter = $userRole === 'reporter';
?>
<h2 class="mb-4"><?= $isEdit ? 'Edit News' : 'Create News' ?></h2>

<?php if ($isReporter): ?>
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        <strong>Reporter Notice:</strong> As a reporter, you can only create and edit news articles as drafts. 
        An editor will review and publish your articles.
    </div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" action="<?= $isEdit ? '/admin/news/edit/' . esc($news['id']) : '/admin/news/create' ?>">
    <div class="row g-3 mb-3">
        <div class="col-md-12">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= $isEdit ? esc($news['title']) : '' ?>" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Subtitle</label>
            <input type="text" name="subtitle" class="form-control" value="<?= $isEdit ? esc($news['subtitle']) : '' ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Lead Text</label>
            <textarea name="lead_text" class="form-control" rows="2"><?= $isEdit ? esc($news['lead_text']) : '' ?></textarea>
        </div>
        <div class="col-md-12">
            <div class="alert alert-info small mb-2">
                <strong>New Image Upload System:</strong> Upload images with captions using the form below. Images will be stored separately and can be managed individually.
            </div>
        </div>
        
        <!-- Main Image Section -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Main Image</h5>
                </div>
                <div class="card-body">
                    <!-- Image Selection Options -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <button type="button" id="uploadNewBtn" class="btn btn-primary w-100">
                                <i class="fas fa-upload"></i> Upload New Image
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" id="selectExistingBtn" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-images"></i> Select from Existing
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button type="button" id="externalUrlBtn" class="btn btn-outline-info w-100">
                                <i class="fas fa-link"></i> Use External URL
                            </button>
                        </div>
                    </div>
                    
                    <!-- External URL Section (initially hidden) -->
                    <div id="externalUrlSection" class="row g-3" style="display: none;">
                        <div class="col-md-12">
                            <label class="form-label">External Image URL</label>
                            <input type="text" name="image_url" class="form-control" value="<?= $isEdit ? esc($news['image_url']) : '' ?>" placeholder="Enter external image URL">
                            <small class="form-text text-muted">Provide a direct link to an external image</small>
                        </div>
                    </div>
                    
                    <!-- Image Caption and Alt Text Fields -->
                    <div class="row g-3 mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Image Caption</label>
                            <input type="text" name="image_caption" class="form-control" value="<?= $isEdit ? esc($news['image_caption']) : '' ?>" placeholder="Enter image caption">
                            <small class="form-text text-muted">Caption will be displayed with the image</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Image Alt Text</label>
                            <input type="text" name="image_alt_text" class="form-control" value="<?= $isEdit ? esc($news['image_alt_text']) : '' ?>" placeholder="Enter alt text for accessibility">
                            <small class="form-text text-muted">Important for accessibility and SEO</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Section - Full Width -->
        <div class="row g-3 mb-3">
            <div class="col-md-12">
                <label class="form-label">Content</label>
                <div id="ckeditor-container">
                    <textarea name="content" id="content" class="form-control" rows="15" required tabindex="0" style="display: none;"><?= $isEdit ? esc($news['content']) : '' ?></textarea>
                </div>
                <div class="invalid-feedback">
                    Please provide content for the news article.
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">Select Category</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= esc($cat['id']) ?>" <?= $isEdit && $news['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= esc($cat['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Tags</label>
            <select name="tags[]" class="form-select" multiple>
                <?php foreach ($tags as $tag): ?>
                    <option value="<?= esc($tag['id']) ?>" <?= isset($selectedTagIds) && in_array($tag['id'], $selectedTagIds ?? []) ? 'selected' : '' ?>><?= esc($tag['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php 
        $userRole = session('user_role');
        $isReporter = $userRole === 'reporter';
        ?>
        
        <?php if ($isReporter): ?>
            <!-- Hidden input for reporters - always draft -->
            <input type="hidden" name="status" value="draft">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <div class="form-control-plaintext text-muted">
                    <i class="fas fa-info-circle"></i> Draft (Reporters can only create drafts)
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" required>
                    <option value="draft" <?= $isEdit && $news['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="published" <?= $isEdit && $news['status'] == 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="archived" <?= $isEdit && $news['status'] == 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
        <?php endif; ?>
        <?php if ($isEdit): ?>
        <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?= esc($news['slug']) ?>">
            <small class="form-text text-muted">Unique code for the article. You can edit it if needed.</small>
        </div>
        <?php else: ?>
        <input type="hidden" name="slug" id="slug" value="">
        <?php endif; ?>
        <div class="col-md-4">
            <label class="form-label">Source</label>
            <input type="text" name="source" class="form-control" value="<?= $isEdit ? esc($news['source']) : '' ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Dateline</label>
            <input type="text" name="dateline" class="form-control" value="<?= $isEdit ? esc($news['dateline']) : '' ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Word Count</label>
            <input type="number" name="word_count" class="form-control" value="<?= $isEdit ? esc($news['word_count']) : '' ?>">
        </div>
        <div class="col-md-2">
            <label class="form-label">Language</label>
            <select name="language" class="form-select" required>
                <option value="en" <?= $isEdit && $news['language'] == 'en' ? 'selected' : '' ?>>English</option>
                <option value="bn" <?= $isEdit && $news['language'] == 'bn' ? 'selected' : '' ?>>Bengali</option>
            </select>
        </div>
        <?php if (!$isReporter): ?>
            <div class="col-md-4">
                <label class="form-label">Published At</label>
                <input type="datetime-local" name="published_at" class="form-control" value="<?= $isEdit && $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '' ?>">
            </div>
        <?php endif; ?>
        <div class="col-md-4">
            <div class="form-check mt-4">
                <input class="form-check-input" type="checkbox" name="featured" value="1" id="featured" <?= $isEdit && $news['featured'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="featured">
                    Featured Article
                </label>
            </div>
        </div>
    </div>
    <button type="submit" class="btn btn-success">Save</button>
    <a href="/admin/news" class="btn btn-secondary">Cancel</a>
</form>

<!-- Existing Images Modal -->
<div class="modal fade" id="existingImagesModal" tabindex="-1" aria-labelledby="existingImagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="existingImagesModalLabel">Select Existing Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="existingImagesGrid">
                    <!-- Images will be loaded here -->
                </div>
                <div id="existingImagesLoading" class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="existingImagesEmpty" class="text-center py-4" style="display: none;">
                    <p class="text-muted">No images found. Upload some images first.</p>
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
                <h5 class="modal-title" id="uploadImageModalLabel">Upload New Image</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Select Image File</label>
                        <input type="file" id="imageUpload" class="form-control" accept="image/*">
                        <small class="form-text text-muted">Max size: 5MB. Supported: JPG, PNG, GIF, WebP</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image Caption</label>
                        <input type="text" id="imageCaption" class="form-control" placeholder="Enter image caption">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image Alt Text</label>
                        <input type="text" id="imageAltText" class="form-control" placeholder="Enter alt text for accessibility">
                    </div>
                </div>
                <div id="uploadProgress" class="progress mt-3" style="display: none;">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <div id="uploadPreview" class="mt-3" style="display: none;">
                    <!-- Upload preview will be shown here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="uploadImageBtn" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Image
                </button>
            </div>
        </div>
    </div>
</div>

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
        let previewSection = document.getElementById('imagePreview');
        if (!previewSection) {
            previewSection = document.createElement('div');
            previewSection.id = 'imagePreview';
            previewSection.className = 'col-md-12 mt-3';
            document.querySelector('.card-body').appendChild(previewSection);
        }
        
        // Add base URL if the image URL is relative
        const fullImageUrl = imageData.preview_url.startsWith('http') ? imageData.preview_url : window.location.origin + '/' + imageData.preview_url;
        
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
                document.querySelector('.card-body').appendChild(previewSection);
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
        
        fetch('/image-upload/existing-images', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (data.success && data.images.length > 0) {
                data.images.forEach(image => {
                    const imageCard = createImageCard(image);
                    grid.appendChild(imageCard);
                });
            } else {
                empty.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error loading images:', error);
            loading.style.display = 'none';
            empty.style.display = 'block';
            empty.innerHTML = '<p class="text-danger">Error loading images. Please try again.</p>';
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
                    <button type="button" class="btn btn-primary btn-sm w-100" onclick="selectExistingImage(${image.id}, '${image.image_path}', '${image.caption || ''}', '${image.alt_text || ''}')">
                        <i class="fas fa-check"></i> Select
                    </button>
                </div>
            </div>
        `;
        
        return col;
    }
});

function selectExistingImage(imageId, imagePath, caption, altText) {
    // Update form fields
    document.querySelector('input[name="image_url"]').value = imagePath;
    document.querySelector('input[name="image_caption"]').value = caption;
    document.querySelector('input[name="image_alt_text"]').value = altText;
    
    // Show preview with proper base URL handling
    showImagePreview({
        image_path: imagePath,
        caption: caption,
        alt_text: altText,
        preview_url: imagePath
    });
    
    // Close modal
    const modal = bootstrap.Modal.getInstance(document.getElementById('existingImagesModal'));
    if (modal) {
        modal.hide();
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
                }
            })
                .then(editor => {
                    console.log('CKEditor initialized successfully');
                    
                    // Store editor instance globally for sync functionality
                    window.ckEditorInstance = editor;
                    
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


</script>
<?= $this->endSection() ?> 