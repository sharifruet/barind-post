<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php $isEdit = isset($news) && $news; ?>
<h2 class="mb-4"><?= $isEdit ? 'Edit News' : 'Create News' ?></h2>
<form method="post" enctype="multipart/form-data" action="<?= $isEdit ? '/admin/news/edit/' . esc($news['id']) : '/admin/news/create' ?>">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="<?= $isEdit ? esc($news['title']) : '' ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Subtitle</label>
            <input type="text" name="subtitle" class="form-control" value="<?= $isEdit ? esc($news['subtitle']) : '' ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Lead Text</label>
            <textarea name="lead_text" class="form-control" rows="2"><?= $isEdit ? esc($news['lead_text']) : '' ?></textarea>
        </div>
        <div class="col-md-12">
            <div class="alert alert-info small mb-2">
                You can either upload an image or provide an image URL. <strong>If both are provided, the uploaded image will be used.</strong>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image Upload</label>
            <input type="file" name="image" class="form-control">
                            <?php if ($isEdit && !empty($news['image_url']) && strpos($news['image_url'], '/public/uploads/news/') === 0): ?>
                <img src="<?= esc($news['image_url']) ?>" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 200px;">
            <?php endif; ?>
            <!-- Button to open image selection modal -->
            <button type="button" class="btn btn-outline-primary mt-2" id="selectExistingImageBtn">Select from existing images</button>
        </div>
        <div class="col-md-6">
            <label class="form-label">Image URL</label>
            <input type="text" name="image_url" class="form-control" value="<?= $isEdit ? esc($news['image_url']) : '' ?>">
        </div>
        <div class="col-md-12">
            <label class="form-label">Content</label>
            <textarea name="content" id="content" class="form-control" rows="6" required><?= $isEdit ? esc($news['content']) : '' ?></textarea>
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
        <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="draft" <?= $isEdit && $news['status'] == 'draft' ? 'selected' : '' ?>>Draft</option>
                <option value="published" <?= $isEdit && $news['status'] == 'published' ? 'selected' : '' ?>>Published</option>
                <option value="archived" <?= $isEdit && $news['status'] == 'archived' ? 'selected' : '' ?>>Archived</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" id="slug" class="form-control" value="<?= $isEdit ? esc($news['slug']) : '' ?>">
            <small class="form-text text-muted">Auto-generated from title. You can edit it if needed.</small>
        </div>
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
        <div class="col-md-4">
            <label class="form-label">Published At</label>
            <input type="datetime-local" name="published_at" class="form-control" value="<?= $isEdit && $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '' ?>">
        </div>
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
<!-- Image Selection Modal -->
<div class="modal" tabindex="-1" id="existingImageModal">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Select an Existing Image</h5>
        <button type="button" class="btn-close" id="closeImageModal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="existingImagesGrid" class="row g-2"></div>
      </div>
    </div>
  </div>
</div>
<style>
/* Modal basic styles */
.modal { display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100vw; height: 100vh; overflow: auto; background: rgba(0,0,0,0.5); }
.modal.show { display: block; }
.modal-dialog { margin: 5% auto; }
#existingImagesGrid img { max-width: 100px; max-height: 100px; cursor: pointer; border: 2px solid transparent; border-radius: 4px; }
#existingImagesGrid img.selected { border-color: #007bff; }
</style>
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
                ClassicEditor.create(document.querySelector('#content'))
                    .then(editor => {
                        console.log('CKEditor initialized successfully');
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
// JS for image selection modal
const selectBtn = document.getElementById('selectExistingImageBtn');
const modal = document.getElementById('existingImageModal');
const closeModalBtn = document.getElementById('closeImageModal');
const imagesGrid = document.getElementById('existingImagesGrid');
const imageUrlInput = document.querySelector('input[name="image_url"]');

selectBtn.addEventListener('click', function() {
  modal.classList.add('show');
  // Fetch images only if not already loaded
  if (!imagesGrid.hasChildNodes()) {
    fetch('/admin/news/images-list')
      .then(res => res.json())
      .then(images => {
        imagesGrid.innerHTML = '';
        images.forEach(img => {
          const col = document.createElement('div');
          col.className = 'col-6 col-md-3 text-center';
          col.innerHTML = `<img src="${img.url}" alt="${img.name}" title="${img.name}" data-url="${img.url}" class="img-thumbnail mb-1"><div class='small text-truncate'>${img.name}</div>`;
          col.querySelector('img').addEventListener('click', function() {
            // Set image URL input and close modal
            imageUrlInput.value = img.url;
            modal.classList.remove('show');
          });
          imagesGrid.appendChild(col);
        });
      });
  }
});
closeModalBtn.addEventListener('click', function() {
  modal.classList.remove('show');
});
// Optional: close modal on outside click
modal.addEventListener('click', function(e) {
  if (e.target === modal) modal.classList.remove('show');
});
</script>
<script>
// Auto-generate slug from title
document.querySelector('input[name="title"]').addEventListener('input', function() {
    const title = this.value;
    const slug = title
        .toLowerCase()
        .replace(/[\u0980-\u09FF]/g, function(match) {
            // Bengali to English transliteration
            const bengaliToEnglish = {
                'অ': 'a', 'আ': 'aa', 'ই': 'i', 'ঈ': 'ii', 'উ': 'u', 'ঊ': 'uu',
                'ঋ': 'ri', 'এ': 'e', 'ঐ': 'oi', 'ও': 'o', 'ঔ': 'ou',
                'ক': 'k', 'খ': 'kh', 'গ': 'g', 'ঘ': 'gh', 'ঙ': 'ng',
                'চ': 'ch', 'ছ': 'chh', 'জ': 'j', 'ঝ': 'jh', 'ঞ': 'ny',
                'ট': 't', 'ঠ': 'th', 'ড': 'd', 'ঢ': 'dh', 'ণ': 'n',
                'ত': 't', 'থ': 'th', 'দ': 'd', 'ধ': 'dh', 'ন': 'n',
                'প': 'p', 'ফ': 'ph', 'ব': 'b', 'ভ': 'bh', 'ম': 'm',
                'য': 'y', 'র': 'r', 'ল': 'l', 'শ': 'sh', 'ষ': 'sh',
                'স': 's', 'হ': 'h', 'ড়': 'r', 'ঢ়': 'rh', 'য়': 'y',
                'ৎ': 't', 'ং': 'ng', 'ঃ': 'h', 'ঁ': 'n',
                'া': 'a', 'ি': 'i', 'ী': 'i', 'ু': 'u', 'ূ': 'u',
                'ৃ': 'ri', 'ে': 'e', 'ৈ': 'oi', 'ো': 'o', 'ৌ': 'ou',
                '্': '', 'ঁ': 'n'
            };
            return bengaliToEnglish[match] || match;
        })
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/[\s-]+/g, '-')
        .trim('-');
    document.getElementById('slug').value = slug;
});
</script>
<?= $this->endSection() ?> 