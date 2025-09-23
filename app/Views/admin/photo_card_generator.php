<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>


<script>
   const banglaMonths = ["জানুয়ারি", "ফেব্রুয়ারি", "মার্চ", "এপ্রিল", "মে", "জুন", "জুলাই", "আগস্ট", "সেপ্টেম্বর", "অক্টোবর", "নভেম্বর", "ডিসেম্বর"];

    // Convert English digits to Bangla digits
    function toBanglaDigits(number) {
      const banglaDigits = ["০","১","২","৩","৪","৫","৬","৭","৮","৯"];
      return number.toString().split("").map(d => banglaDigits[d] || d).join("");
    }

    // Format date as "১০ জুন ২০২৫"
    function formatBanglaDate(dateString) {
      const date = new Date(dateString);
      const day = toBanglaDigits(date.getDate());
      const month = banglaMonths[date.getMonth()];
      const year = toBanglaDigits(date.getFullYear());
      return `${month} ${day}, ${year}`;
    }
</script>

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
                                                data-date="<?= $item['published_at'] ?>"
                                                data-category="<?= esc($item['category_name'] ?? 'সংবাদ') ?>">
                                            <?= esc($item['title']) ?> (<?= date('d M, Y', strtotime($item['published_at'])) ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Template Selection -->
                            <div class="mb-3">
                                <label for="templateSelect" class="form-label">Choose template:</label>
                                <select class="form-select" id="templateSelect">
                                    <option value="template-1">Template 1</option>
                                    <option value="template-2">Template 2</option>
                                </select>
                            </div>

                            <!-- Custom Title Text -->
                            <div class="mb-3" id="customTitleSection">
                                <label for="customTitle" class="form-label">Custom Title Text:</label>
                                <textarea class="form-control" id="customTitle" rows="3" placeholder="Enter custom title text or leave empty to use news title"></textarea>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Leave empty to use the original news title, or enter custom text for the photo card.
                                </small>
                            </div>

                            <!-- Customization Options -->
                            <div class="mb-3">
                                <label class="form-label">Customization:</label>
                                <div class="row">
                                    <div class="col-4">
                                        <label for="titleSize" class="form-label">Title Size:</label>
                                        <input type="range" class="form-range" id="titleSize" min="30" max="80" value="48">
                                        <small class="text-muted" id="titleSizeValue">48px</small>
                                    </div>
                                    <div class="col-4">
                                        <label for="titleColor" class="form-label">Title Color:</label>
                                        <input type="color" class="form-control form-control-color" id="titleColor" value="#D51111">
                                    </div>
                                    <div class="col-4">
                                        <label for="backgroundColor" class="form-label">Background Color:</label>
                                        <input type="color" class="form-control form-control-color" id="backgroundColor" value="#ffffff">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <small class="text-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Titles will be automatically wrapped to a maximum of 4 lines. Long titles will be truncated with "..." if needed.
                                    </small>
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
// Global variables
let canvas, ctx, newsSelect, templateSelect, titleSize, titleSizeValue, titleColor, backgroundColor, customTitle, customTitleSection, backgroundColorSection;
let generateBtn, previewContainer, resultContainer, downloadSection, downloadBtn, copyUrlBtn;
let logo; // Global logo variable

// Initialize the application
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DOM elements
    newsSelect = document.getElementById('newsSelect');
    templateSelect = document.getElementById('templateSelect');
    titleSize = document.getElementById('titleSize');
    titleSizeValue = document.getElementById('titleSizeValue');
    titleColor = document.getElementById('titleColor');
    backgroundColor = document.getElementById('backgroundColor');
    customTitle = document.getElementById('customTitle');
    customTitleSection = document.getElementById('customTitleSection');
    backgroundColorSection = document.getElementById('backgroundColorSection');
    generateBtn = document.getElementById('generateBtn');
    previewContainer = document.getElementById('previewContainer');
    resultContainer = document.getElementById('resultContainer');
    downloadSection = document.getElementById('downloadSection');
    downloadBtn = document.getElementById('downloadBtn');
    copyUrlBtn = document.getElementById('copyUrlBtn');
    canvas = document.getElementById('photoCardCanvas');
    ctx = canvas.getContext('2d');

    // Set up event listeners
    setupEventListeners();
});

// Event Listeners Setup
function setupEventListeners() {
    // Update title size display
    titleSize.addEventListener('input', function() {
        titleSizeValue.textContent = this.value + 'px';
        updatePreview();
    });

    // Update preview when selection changes
    newsSelect.addEventListener('change', function() {
        // Auto-populate custom title field with news title
        const selectedOption = this.selectedOptions[0];
        if (selectedOption && customTitle) {
            customTitle.value = selectedOption.dataset.title || '';
        }
        updatePreview();
    });
    templateSelect.addEventListener('change', function() {
        // Set template-specific color defaults
        if (this.value === 'template-2') {
            // Set Template 2 defaults: white text on red background
            titleColor.value = '#ffffff';
            backgroundColor.value = '#D51111';
        } else {
            // Set Template 1 defaults: red text on white background
            titleColor.value = '#D51111';
            backgroundColor.value = '#ffffff';
        }
        updatePreview();
    });
    titleColor.addEventListener('change', updatePreview);
    backgroundColor.addEventListener('change', updatePreview);
    customTitle.addEventListener('input', updatePreview);

    // Generate button click handler
    generateBtn.addEventListener('click', handleGenerateClick);
}

// Generate button click handler
function handleGenerateClick() {
    try {
        console.log('Generate button clicked');
        const selectedOption = newsSelect.selectedOptions[0];
        if (!selectedOption) {
            alert('Please select a news article first.');
            return;
        }

        const originalTitle = selectedOption.dataset.title;
        const lead = selectedOption.dataset.lead;
        const content = selectedOption.dataset.content;
        const imageUrl = selectedOption.dataset.image;
        const date = selectedOption.dataset.date;
        const category = selectedOption.dataset.category;
        const template = templateSelect.value;
        const fontSize = parseInt(titleSize.value);
        const color = titleColor.value;
        const bgColor = backgroundColor.value;
        
        // Use custom title if provided, otherwise use original title
        const title = customTitle.value.trim() ? customTitle.value.trim() : originalTitle;
        
        console.log('Generating photo card with:', {
            title: title,
            template: template,
            imageUrl: imageUrl,
            category: category
        });

        // Show loading state
        generateBtn.disabled = true;
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating...';

        // Generate the photo card
        generatePhotoCard(title, lead, content, imageUrl, date, category, template, fontSize, color, bgColor, false);

        // Reset button
        setTimeout(() => {
            generateBtn.disabled = false;
            generateBtn.innerHTML = '<i class="fas fa-magic me-2"></i>Generate Photo Card';
        }, 1000);
    } catch (error) {
        console.error('Error generating photo card:', error);
        alert('Error generating photo card: ' + error.message);
        generateBtn.disabled = false;
        generateBtn.innerHTML = '<i class="fas fa-magic me-2"></i>Generate Photo Card';
    }
}

// Update preview function
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

        const originalTitle = selectedOption.dataset.title;
        const lead = selectedOption.dataset.lead;
        const content = selectedOption.dataset.content;
        const imageUrl = selectedOption.dataset.image;
        const date = selectedOption.dataset.date;
        const category = selectedOption.dataset.category;
        const template = templateSelect.value;
        const fontSize = parseInt(titleSize.value);
        const color = titleColor.value;
        const bgColor = backgroundColor.value;
        
        // Use custom title if provided, otherwise use original title
        const title = customTitle.value.trim() ? customTitle.value.trim() : originalTitle;

        // Generate preview
        generatePhotoCard(title, lead, content, imageUrl, date, category, template, fontSize, color, bgColor, true);
}

function generatePhotoCard(title, lead, content, imageUrl, date, category, template, fontSize, color, bgColor, isPreview = false) {
    generatePhotocardX(
        imageUrl,
        '<?= base_url('public/logo.png') ?>',
        title,
        'বরিন্দ পোস্ট',
        category,
        formatBanglaDate(date), fontSize, color, bgColor, template
    ).then(dataUrl => {
        // Use the dataUrl (e.g., display in an img element or download)
        displayResult(isPreview, canvas.width, canvas.height);
        console.log('Photocard generated successfully');
    }).catch(error => {
        console.error('Error generating photocard:', error);
    });
   
}

async function generatePhotocardX(imageUrl, logoUrl, title, name, section, date, fontSize, color, bgColor, template) {
    return new Promise((resolve, reject) => {
        // Load the main image
        const img = new Image();
        img.crossOrigin = 'anonymous';
        
        img.onload = function() {
            // Calculate dimensions
            const originalWidth = img.width;
            const originalHeight = img.height;
            const targetWidth = 800;
            const aspectRatio = originalHeight / originalWidth;
            const scaledHeight = targetWidth * aspectRatio;
            
            // Text section height and footer height
            const textSectionHeight = 250;
            const footerHeight = 80;
            const totalHeight = scaledHeight + textSectionHeight + footerHeight;
            
            // Set canvas dimensions
            canvas.width = targetWidth;
            canvas.height = totalHeight;

            console.log('Canvas dimensions:', {width: canvas.width, height: canvas.height});
            
            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);

            console.log('ctx', ctx);
            
            // Draw the main image (resized to fit width)
            ctx.drawImage(img, 0, 0, targetWidth, scaledHeight);
            
            console.log('Image drawn successfully');
            
            // Draw white text section
            ctx.fillStyle = bgColor;
            ctx.fillRect(0, scaledHeight, targetWidth, textSectionHeight);
            
            // Configure text styling for title
            ctx.fillStyle = color;
            ctx.font = `${fontSize}px "Noto Sans Bengali", Arial, sans-serif`;
            ctx.textAlign = 'center';
         
            // Word wrap the title text
            const maxWidth = targetWidth - 40; // 20px padding on each side
            const lineHeight = fontSize*1.1;
            const words = title.split(' ');
            const lines = [];
            let currentLine = '';
            
            for (let word of words) {
                const testLine = currentLine + (currentLine ? ' ' : '') + word;
                const metrics = ctx.measureText(testLine);
                
                if (metrics.width > maxWidth && currentLine !== '') {
                    lines.push(currentLine);
                    currentLine = word;
                } else {
                    currentLine = testLine;
                }
            }
            if (currentLine) {
                lines.push(currentLine);
            }
            
            // Draw title lines (max 4 lines)
            const maxLines = 4;
            const startY = scaledHeight + 50;
            
            for (let i = 0; i < Math.min(lines.length, maxLines); i++) {
                let lineText = lines[i];
                if (i === maxLines - 1 && lines.length > maxLines) {
                    // Add ellipsis if text is truncated
                    lineText = lineText + '...';
                }
                ctx.fillText(lineText,targetWidth/2, startY + (i * lineHeight) + (4-lines.length)*33 );
            }
            
            // Draw gray footer
            const footerY = scaledHeight + textSectionHeight;
            if(template === 'template-1'){
                ctx.fillStyle = '#f0f0f0';
            }else{
                ctx.fillStyle = '#DDDDDD';

            }
            ctx.fillRect(0, footerY, targetWidth, footerHeight);

            ctx.strokeStyle = '#999999';;
            ctx.rect((targetWidth/2-75), footerY+45, 150, 30);
            ctx.stroke();

            ctx.font = '20px "Noto Sans Bengali", Arial, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillStyle = '#999999';
            ctx.fillText('বিস্তারিত কমেন্টে', targetWidth/2, footerY+68);
 
            
            // Load and draw logo
            const logo = new Image();
            logo.crossOrigin = 'anonymous';
            
            logo.onload = function() {
                // Calculate logo dimensions (150px width, maintain aspect ratio)
                const logoWidth = 65;
                const logoAspectRatio = logo.height / logo.width;
                const logoHeight = logoWidth * logoAspectRatio;
                
                // Center logo vertically in footer
                const logoY = footerY + (footerHeight - logoHeight) / 2;
                ctx.drawImage(logo, 20, logoY, logoWidth, logoHeight);
                
                // Draw footer text
                ctx.fillStyle = '#666666';
                ctx.font = 'bold 34px "Noto Sans Bengali", Arial, sans-serif';
                ctx.textAlign = 'left';
                
                const textX = 20 + logoWidth + 20; // Logo width + padding
                const textY = footerY + footerHeight / 2 + 14; // Center vertically with slight offset
                
                const footerText = `${name}`;
                ctx.fillText(footerText, textX, textY);

                ctx.font = '24px "Noto Sans Bengali", Arial, sans-serif';
                ctx.textAlign = 'end';
                ctx.fillText(`${date}`, targetWidth-4, textY);
                
                resolve(canvas.toDataURL());
            };
            
            logo.onerror = function() {
                //If logo fails to load, still draw the text
                ctx.fillStyle = '#666666';
                ctx.font = '30px "Noto Sans Bengali", Arial, sans-serif';
                ctx.textAlign = 'left';
                
                const textX = 20;
                const textY = footerY + footerHeight / 2 + 6;
                
                const footerText = `${name} | ${section} | ${date}`;
                ctx.fillText(footerText, textX, textY);
                
                resolve(canvas.toDataURL());
            };
            
            logo.src = logoUrl;
        };
        
        img.onerror = function() {
            reject(new Error('Failed to load main image'));
        };
        
        img.src = imageUrl;

       
    });
}


    // Display result function
    function displayResult(isPreview, width, height) {
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