<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-image me-2"></i>Photo Card Generator</h2>
                <div class="text-muted">
                    <small>Generate social media photo cards from news articles</small>
                </div>
            </div>

            <!-- Alert Messages -->
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= session()->getFlashdata('success') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= session()->getFlashdata('error') ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- News Selection Panel -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-newspaper me-2"></i>Select News Article</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="newsSelect" class="form-label">Choose a news article:</label>
                                <select class="form-select" id="newsSelect">
                                    <option value="">-- Select a news article --</option>
                                    <?php foreach ($news as $item): ?>
                                        <option value="<?= $item['id'] ?>" 
                                                data-title="<?= esc($item['title']) ?>"
                                                data-image="<?= esc(get_image_url($item['image_url'] ?? '')) ?>"
                                                data-date="<?= $item['published_at'] ?>">
                                            <?= esc($item['title']) ?> (<?= date('d M, Y', strtotime($item['published_at'])) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="templateSelect" class="form-label">Choose template:</label>
                                <select class="form-select" id="templateSelect">
                                    <option value="default">Default (White Background)</option>
                                    <option value="red">Red Background</option>
                                    <option value="gradient">Gradient Background</option>
                                </select>
                            </div>

                            <div class="d-grid">
                                <button type="button" class="btn btn-primary" id="generateBtn" disabled>
                                    <i class="fas fa-magic me-2"></i>Generate Photo Card
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Panel -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview</h5>
                        </div>
                        <div class="card-body">
                            <div id="previewContainer" class="text-center">
                                <div class="text-muted py-5">
                                    <i class="fas fa-image fa-3x mb-3"></i>
                                    <p>Select a news article to see preview</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Generated Photo Card -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-download me-2"></i>Generated Photo Card</h5>
                        </div>
                        <div class="card-body">
                            <div id="resultContainer" class="text-center">
                                <div class="text-muted py-5">
                                    <i class="fas fa-image fa-3x mb-3"></i>
                                    <p>Generated photo card will appear here</p>
                                </div>
                            </div>
                            
                            <div id="downloadSection" class="mt-3" style="display: none;">
                                <div class="d-grid gap-2">
                                    <a href="#" class="btn btn-success" id="downloadBtn" download>
                                        <i class="fas fa-download me-2"></i>Download Photo Card
                                    </a>
                                    <button type="button" class="btn btn-outline-secondary" id="copyUrlBtn">
                                        <i class="fas fa-link me-2"></i>Copy Image URL
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <h5>Generating Photo Card...</h5>
                <p class="text-muted">Please wait while we create your photo card</p>
                <button type="button" class="btn btn-outline-secondary mt-3" onclick="hideModal()">
                    <i class="fas fa-times me-2"></i>Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.preview-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.generated-image {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.news-preview {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
}

.news-preview h6 {
    color: #495057;
    margin-bottom: 8px;
}

.news-preview p {
    color: #6c757d;
    font-size: 0.9rem;
    margin-bottom: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newsSelect = document.getElementById('newsSelect');
    const templateSelect = document.getElementById('templateSelect');
    const generateBtn = document.getElementById('generateBtn');
    const previewContainer = document.getElementById('previewContainer');
    const resultContainer = document.getElementById('resultContainer');
    const downloadSection = document.getElementById('downloadSection');
    const downloadBtn = document.getElementById('downloadBtn');
    const copyUrlBtn = document.getElementById('copyUrlBtn');
    const loadingModalElement = document.getElementById('loadingModal');
    const loadingModal = new bootstrap.Modal(loadingModalElement);
    
    // Debug function to check modal state
    function debugModal() {
        console.log('Modal element:', loadingModalElement);
        console.log('Modal instance:', loadingModal);
        console.log('Modal is visible:', loadingModalElement.classList.contains('show'));
    }
    
    // Fallback function to hide modal
    function hideModal() {
        try {
            loadingModal.hide();
        } catch (e) {
            console.log('Bootstrap modal hide failed, using fallback...');
            // Fallback: remove modal classes manually
            loadingModalElement.classList.remove('show');
            document.body.classList.remove('modal-open');
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
        }
    }
    
    // Make hideModal globally accessible
    window.hideModal = hideModal;

    // Enable/disable generate button based on selection
    newsSelect.addEventListener('change', function() {
        generateBtn.disabled = !this.value;
        updatePreview();
    });

    templateSelect.addEventListener('change', updatePreview);

    function updatePreview() {
        const selectedOption = newsSelect.options[newsSelect.selectedIndex];
        if (!selectedOption.value) {
            previewContainer.innerHTML = `
                <div class="text-muted py-5">
                    <i class="fas fa-image fa-3x mb-3"></i>
                    <p>Select a news article to see preview</p>
                </div>
            `;
            return;
        }

        const title = selectedOption.dataset.title;
        const imageUrl = selectedOption.dataset.image;
        const date = new Date(selectedOption.dataset.date).toLocaleDateString('en-US', {
            day: 'numeric',
            month: 'short',
            year: 'numeric'
        });

        let previewHtml = `
            <div class="news-preview">
                <h6><i class="fas fa-newspaper me-2"></i>Selected Article</h6>
                <p><strong>Title:</strong> ${title}</p>
                <p><strong>Date:</strong> ${date}</p>
        `;

        if (imageUrl) {
            previewHtml += `
                <p><strong>Has Image:</strong> <span class="text-success"><i class="fas fa-check"></i> Yes</span></p>
                <img src="${imageUrl}" class="preview-image mt-2" alt="News image" style="max-height: 150px;">
            `;
        } else {
            previewHtml += `<p><strong>Has Image:</strong> <span class="text-warning"><i class="fas fa-times"></i> No</span></p>`;
        }

        previewHtml += '</div>';
        previewContainer.innerHTML = previewHtml;
    }

    // Generate photo card
    generateBtn.addEventListener('click', function() {
        const newsId = newsSelect.value;
        const template = templateSelect.value;

        if (!newsId) {
            alert('Please select a news article first.');
            return;
        }

        // Show loading modal
        console.log('Showing loading modal...');
        debugModal();
        loadingModal.show();

        // Set a timeout to hide modal if request takes too long
        const modalTimeout = setTimeout(() => {
            console.log('Modal timeout reached, hiding modal...');
            hideModal();
        }, 30000); // 30 seconds timeout

        // Make AJAX request
        fetch('/admin/photo-card-generator/generate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: `news_id=${newsId}&template=${template}`
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Clear timeout and hide loading modal
            clearTimeout(modalTimeout);
            console.log('Response received, hiding modal...');
            debugModal();
            hideModal();
            
            if (data.success) {
                // Show generated image
                resultContainer.innerHTML = `
                    <div class="text-success mb-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <p class="mt-2">Photo card generated successfully!</p>
                    </div>
                    <img src="/${data.image_url}" class="generated-image" alt="Generated photo card">
                `;
                
                // Show download section
                downloadSection.style.display = 'block';
                downloadBtn.href = `/${data.image_url}`;
                downloadBtn.download = `photo_card_${newsId}_${Date.now()}.png`;
                
                // Copy URL functionality
                copyUrlBtn.onclick = function() {
                    const imageUrl = `${window.location.origin}/${data.image_url}`;
                    navigator.clipboard.writeText(imageUrl).then(() => {
                        // Show temporary success message
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-check me-2"></i>URL Copied!';
                        this.classList.remove('btn-outline-secondary');
                        this.classList.add('btn-success');
                        
                        setTimeout(() => {
                            this.innerHTML = originalText;
                            this.classList.remove('btn-success');
                            this.classList.add('btn-outline-secondary');
                        }, 2000);
                    });
                };
            } else {
                resultContainer.innerHTML = `
                    <div class="text-danger">
                        <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                        <p>Error: ${data.message}</p>
                    </div>
                `;
                downloadSection.style.display = 'none';
            }
        })
        .catch(error => {
            // Clear timeout and hide loading modal
            clearTimeout(modalTimeout);
            console.log('Error occurred, hiding modal...');
            debugModal();
            hideModal();
            resultContainer.innerHTML = `
                <div class="text-danger">
                    <i class="fas fa-exclamation-circle fa-2x mb-3"></i>
                    <p>Error: Failed to generate photo card. Please try again.</p>
                </div>
            `;
            downloadSection.style.display = 'none';
            console.error('Error:', error);
        });
    });
});
</script>

<?= $this->endSection() ?> 