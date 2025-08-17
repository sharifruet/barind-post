<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<!-- Google Fonts - Noto Sans Bengali -->
<link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-image me-2"></i>Photo Card Generator</h2>
                <div class="text-muted">
                    <small>Generate social media photo cards from news articles using front-end JavaScript</small>
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
                                                data-lead="<?= esc($item['lead'] ?? '') ?>"
                                                data-content="<?= esc($item['content'] ?? '') ?>"
                                                data-image="<?= esc(get_image_url($item['image_url'] ?? '')) ?>"
                                                data-date="<?= $item['published_at'] ?>">
                                            <?= esc($item['title']) ?> (<?= date('d M, Y', strtotime($item['published_at'])) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Template Selection -->
                            <div class="mb-3">
                                <label for="templateSelect" class="form-label">Choose template:</label>
                                <select class="form-select" id="templateSelect">
                                    <option value="default">Default (White)</option>
                                    <option value="red">Red Background</option>
                                    <option value="gradient">Gradient Background</option>
                                    <option value="dark">Dark Theme</option>
                                </select>
                            </div>

                            <!-- Customization Options -->
                            <div class="mb-3">
                                <label class="form-label">Customization:</label>
                                <div class="row">
                                    <div class="col-6">
                                        <label for="titleSize" class="form-label">Title Size:</label>
                                        <input type="range" class="form-range" id="titleSize" min="30" max="80" value="48">
                                        <small class="text-muted" id="titleSizeValue">48px</small>
                                    </div>
                                    <div class="col-6">
                                        <label for="titleColor" class="form-label">Title Color:</label>
                                        <input type="color" class="form-control form-control-color" id="titleColor" value="#96031a">
                                    </div>
                                </div>
                            </div>

                            <!-- Generate Button -->
                            <button type="button" class="btn btn-primary btn-lg w-100" id="generateBtn">
                                <i class="fas fa-magic me-2"></i>Generate Photo Card
                            </button>
                        </div>
                    </div>

                    <!-- Preview Panel -->
                    <div class="card shadow-sm mt-4">
                        <div class="card-header bg-info text-white">
                            <h5 class="mb-0"><i class="fas fa-eye me-2"></i>Preview</h5>
                        </div>
                        <div class="card-body">
                            <div id="previewContainer" class="text-center">
                                <div class="text-muted">
                                    <i class="fas fa-image fa-3x mb-3"></i>
                                    <p>Select a news article to see preview</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Result Panel -->
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0"><i class="fas fa-download me-2"></i>Generated Photo Card</h5>
                        </div>
                        <div class="card-body">
                            <div id="resultContainer" class="text-center">
                                <div class="text-muted">
                                    <i class="fas fa-image fa-3x mb-3"></i>
                                    <p>Generate a photo card to see the result</p>
                                </div>
                            </div>

                            <!-- Download Section -->
                            <div id="downloadSection" style="display: none;" class="mt-4">
                                <div class="d-grid gap-2">
                                    <button type="button" class="btn btn-success" id="downloadBtn">
                                        <i class="fas fa-download me-2"></i>Download Photo Card
                                    </button>
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

<!-- Hidden Canvas for Image Generation -->
<canvas id="photoCardCanvas" style="display: none;"></canvas>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const newsSelect = document.getElementById('newsSelect');
    const templateSelect = document.getElementById('templateSelect');
    const titleSize = document.getElementById('titleSize');
    const titleSizeValue = document.getElementById('titleSizeValue');
    const titleColor = document.getElementById('titleColor');
    const generateBtn = document.getElementById('generateBtn');
    const previewContainer = document.getElementById('previewContainer');
    const resultContainer = document.getElementById('resultContainer');
    const downloadSection = document.getElementById('downloadSection');
    const downloadBtn = document.getElementById('downloadBtn');
    const copyUrlBtn = document.getElementById('copyUrlBtn');
    const canvas = document.getElementById('photoCardCanvas');
    const ctx = canvas.getContext('2d');

    // Update title size display
    titleSize.addEventListener('input', function() {
        titleSizeValue.textContent = this.value + 'px';
        updatePreview();
    });

    // Update preview when selection changes
    newsSelect.addEventListener('change', updatePreview);
    templateSelect.addEventListener('change', updatePreview);
    titleColor.addEventListener('change', updatePreview);

    function updatePreview() {
        const selectedOption = newsSelect.selectedOptions[0];
        if (!selectedOption) {
            previewContainer.innerHTML = `
                <div class="text-muted">
                    <i class="fas fa-image fa-3x mb-3"></i>
                    <p>Select a news article to see preview</p>
                </div>
            `;
            return;
        }

        const title = selectedOption.dataset.title;
        const lead = selectedOption.dataset.lead;
        const content = selectedOption.dataset.content;
        const imageUrl = selectedOption.dataset.image;
        const date = selectedOption.dataset.date;
        const template = templateSelect.value;
        const fontSize = parseInt(titleSize.value);
        const color = titleColor.value;

        // Generate preview
        generatePhotoCard(title, lead, content, imageUrl, date, template, fontSize, color, true);
    }

    function generatePhotoCard(title, lead, content, imageUrl, date, template, fontSize, color, isPreview = false) {
        // Set canvas dimensions based on news image
        let width = 1200; // Default width
        let height = 630; // Default height
        
        // If we have a news image, get its dimensions
        if (imageUrl && imageUrl !== '') {
            const newsImage = new Image();
            newsImage.crossOrigin = 'anonymous';
            newsImage.onload = function() {
                // Set canvas width to exactly match news image width
                width = newsImage.width;
                height = newsImage.height; // Use exact image height
                
                // Set canvas dimensions
                canvas.width = width;
                canvas.height = height;
                
                // Now generate the photo card with the correct dimensions
                generatePhotoCardContent(title, lead, content, imageUrl, date, template, fontSize, color, isPreview);
            };
            newsImage.onerror = function() {
                // Use default dimensions if image fails to load
                canvas.width = width;
                canvas.height = height;
                generatePhotoCardContent(title, lead, content, imageUrl, date, template, fontSize, color, isPreview);
            };
            newsImage.src = imageUrl;
        } else {
            // No image, use default dimensions
            canvas.width = width;
            canvas.height = height;
            generatePhotoCardContent(title, lead, content, imageUrl, date, template, fontSize, color, isPreview);
        }
    }

    function generatePhotoCardContent(title, lead, content, imageUrl, date, template, fontSize, color, isPreview = false) {
        const width = canvas.width;
        const height = canvas.height;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Set background based on template
        switch (template) {
            case 'red':
                ctx.fillStyle = '#dc3545';
                ctx.fillRect(0, 0, width, height);
                break;
            case 'gradient':
                const gradient = ctx.createLinearGradient(0, 0, 0, height);
                gradient.addColorStop(0, '#dc3545');
                gradient.addColorStop(1, '#c82333');
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, width, height);
                break;
            case 'dark':
                ctx.fillStyle = '#343a40';
                ctx.fillRect(0, 0, width, height);
                break;
            default:
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);
                break;
        }

        // Load logo image for later use
        const logo = new Image();
        logo.crossOrigin = 'anonymous';
        logo.onload = function() {
            // Continue with image loading - logo will be drawn on top later
            loadNewsImage();
        };
        logo.onerror = function() {
            // Continue without logo
            loadNewsImage();
        };
        logo.src = '/logo.png';

        function loadNewsImage() {
            if (imageUrl && imageUrl !== '') {
                const newsImage = new Image();
                newsImage.crossOrigin = 'anonymous';
                newsImage.onload = function() {
                    // Since canvas dimensions match the image exactly, draw the full image
                    ctx.drawImage(newsImage, 0, 0, width, height);
                    
                    // Draw logo on top of the image
                    drawLogo();
                    
                    // Add text overlay
                    addTextOverlay();
                };
                newsImage.onerror = function() {
                    // Continue without news image
                    drawLogo();
                    addTextOverlay();
                };
                newsImage.src = imageUrl;
            } else {
                drawLogo();
                addTextOverlay();
            }
        }

        function drawLogo() {
            if (logo.complete && logo.naturalWidth !== 0) {
                const logoWidth = width * 0.05; // 5% of image width
                const logoHeight = (logo.height / logo.width) * logoWidth;
                const backgroundPadding = Math.max(5, logoWidth * 0.1); // Proportional padding
                const logoX = backgroundPadding; // Position logo with padding from left edge
                const logoY = backgroundPadding; // Position logo with padding from top edge
                
                // Calculate font size based on image resolution
                let siteNameFontSize = Math.max(12, logoWidth * 0.2);
                if (width < 800) { // Low resolution images
                    siteNameFontSize = Math.max(8, logoWidth * 0.15); // Smaller font for low res
                }
                
                // Ensure text fits within background
                const textHeight = siteNameFontSize * 1.2;
                const totalHeight = logoHeight + textHeight + (backgroundPadding * 2);
                const maxTextHeight = totalHeight - logoHeight - (backgroundPadding * 2);
                
                if (textHeight > maxTextHeight) {
                    siteNameFontSize = Math.max(6, maxTextHeight / 1.2); // Adjust font size to fit
                }
                
                // Draw semi-transparent white background behind logo and site name
                const backgroundHeight = totalHeight;
                ctx.fillStyle = 'rgba(255, 255, 255, 0.6)';
                ctx.fillRect(2, 2, logoWidth + (backgroundPadding * 2), backgroundHeight);
                
                // Draw the logo
                ctx.drawImage(logo, logoX, logoY, logoWidth, logoHeight);
                
                // Draw site name below logo in Bengali
                ctx.font = `${siteNameFontSize}px "Noto Sans Bengali", Arial, sans-serif`;
                ctx.fillStyle = '#000000';
                ctx.textAlign = 'center';
                const siteNameX = logoX + (logoWidth / 2);
                const siteNameY = logoY + logoHeight + (siteNameFontSize * 1.2);
                ctx.fillText('বরিন্দ পোস্ট', siteNameX, siteNameY);
            }
        }

        function addTextOverlay() {
            // Set font and ensure opaque text rendering
            ctx.font = `bold ${fontSize}px "Noto Sans Bengali", Arial, sans-serif`;
            ctx.textAlign = 'center';
            ctx.globalAlpha = 1.0; // Ensure full opacity
            
            // Set text color from the color picker
            ctx.fillStyle = color;
            
            // Use the news title for display
            let displayText = title;
            
            // Calculate text position - overlay on the image
            const titleX = width / 2; // Center horizontally
            const titleY = height - 15; // Position text at very bottom
            const maxTitleWidth = width - 100;
            
            // Wrap text
            const words = displayText.split(' ');
            const lines = [];
            let currentLine = words[0];
            
            for (let i = 1; i < words.length; i++) {
                const word = words[i];
                const width = ctx.measureText(currentLine + ' ' + word).width;
                if (width < maxTitleWidth) {
                    currentLine += ' ' + word;
                } else {
                    lines.push(currentLine);
                    currentLine = word;
                }
            }
            lines.push(currentLine);
            
            // Draw title lines with full-width semi-transparent background
            const lineHeight = fontSize + 10;
            const titlePadding = 15;
            const titleMargin = 20;
            
            // Calculate background dimensions - full width
            const backgroundWidth = width; // Full width of image
            const backgroundHeight = (lines.length * lineHeight) + (titlePadding * 2);
            const backgroundX = 0; // Start from left edge
            const backgroundY = titleY - fontSize - titlePadding;
            
            // Draw semi-transparent white background
            ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
            ctx.fillRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);
            
            // Set text color from the color picker before drawing text
            ctx.fillStyle = color;
            
            // Draw title lines
            lines.forEach((line, index) => {
                const y = titleY + (index * lineHeight);
                if (y < height - 2) { // Allow text to go very close to bottom
                    ctx.fillText(line, titleX, y);
                }
            });
            
            // Display result
            if (isPreview) {
                // Show preview - maintain aspect ratio
                const previewCanvas = document.createElement('canvas');
                const previewWidth = 400;
                const previewHeight = Math.round((previewWidth * height) / width);
                previewCanvas.width = previewWidth;
                previewCanvas.height = previewHeight;
                const previewCtx = previewCanvas.getContext('2d');
                previewCtx.drawImage(canvas, 0, 0, previewWidth, previewHeight);
                
                previewContainer.innerHTML = `
                    <img src="${previewCanvas.toDataURL()}" class="img-fluid" alt="Preview">
                `;
            } else {
                // Show full result
                resultContainer.innerHTML = `
                    <div class="text-success mb-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                        <p class="mt-2">Photo card generated successfully!</p>
                    </div>
                    <img src="${canvas.toDataURL()}" class="img-fluid" alt="Generated photo card">
                `;
                
                // Show download section
                downloadSection.style.display = 'block';
                
                // Setup download
                downloadBtn.onclick = function() {
                    const link = document.createElement('a');
                    link.download = `photo_card_${newsSelect.value}_${Date.now()}.png`;
                    link.href = canvas.toDataURL();
                    link.click();
                };
                
                // Setup copy URL
                copyUrlBtn.onclick = function() {
                    const imageDataUrl = canvas.toDataURL();
                    navigator.clipboard.writeText(imageDataUrl).then(() => {
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
            }
        }
    }

    // Generate button click handler
    generateBtn.addEventListener('click', function() {
        const selectedOption = newsSelect.selectedOptions[0];
        if (!selectedOption) {
            alert('Please select a news article first.');
            return;
        }

        const title = selectedOption.dataset.title;
        const lead = selectedOption.dataset.lead;
        const content = selectedOption.dataset.content;
        const imageUrl = selectedOption.dataset.image;
        const date = selectedOption.dataset.date;
        const template = templateSelect.value;
        const fontSize = parseInt(titleSize.value);
        const color = titleColor.value;

        // Show loading state
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';

        // Generate the photo card
        generatePhotoCard(title, lead, content, imageUrl, date, template, fontSize, color, false);

        // Reset button
        setTimeout(() => {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic me-2"></i>Generate Photo Card';
        }, 1000);
    });
});
</script>

<style>
.generated-image {
    max-width: 100%;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

#previewContainer img {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.form-range {
    height: 6px;
}

.form-control-color {
    width: 100%;
    height: 38px;
}
</style>

<?= $this->endSection() ?> 