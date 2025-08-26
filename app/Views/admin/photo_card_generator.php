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
                                    <option value="default">Default (White)</option>
                                    <option value="header_footer">Header & Footer Layout</option>
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
                                <div class="mt-2">
                                    <small class="text-info">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Titles will be automatically wrapped to a maximum of 2 lines. Long titles will be truncated with "..." if needed.
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
let canvas, ctx, newsSelect, templateSelect, titleSize, titleSizeValue, titleColor;
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
    newsSelect.addEventListener('change', updatePreview);
    templateSelect.addEventListener('change', updatePreview);
    titleColor.addEventListener('change', updatePreview);

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

        const title = selectedOption.dataset.title;
        const lead = selectedOption.dataset.lead;
        const content = selectedOption.dataset.content;
        const imageUrl = selectedOption.dataset.image;
        const date = selectedOption.dataset.date;
        const category = selectedOption.dataset.category;
        const template = templateSelect.value;
        const fontSize = parseInt(titleSize.value);
        const color = titleColor.value;
        
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
        generatePhotoCard(title, lead, content, imageUrl, date, category, template, fontSize, color, false);

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

        const title = selectedOption.dataset.title;
        const lead = selectedOption.dataset.lead;
        const content = selectedOption.dataset.content;
        const imageUrl = selectedOption.dataset.image;
        const date = selectedOption.dataset.date;
        const category = selectedOption.dataset.category;
        const template = templateSelect.value;
        const fontSize = parseInt(titleSize.value);
        const color = titleColor.value;

        // Generate preview
        generatePhotoCard(title, lead, content, imageUrl, date, category, template, fontSize, color, true);
}

function generatePhotoCard(title, lead, content, imageUrl, date, category, template, fontSize, color, isPreview = false) {
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
                
                // Store original image dimensions for layout calculations
                const originalImageHeight = newsImage.height;
                
                // For header_footer template, calculate total height including title and footer
                if (template === 'header_footer') {
                    const imageHeight = originalImageHeight; // Image takes full height
                    const titleHeight = Math.max(200, originalImageHeight * 0.2); // Minimum 200px or 20% of height
                    const footerHeight = Math.max(80, originalImageHeight * 0.1); // Minimum 80px or 10% of height
                    height = imageHeight + titleHeight + footerHeight;
                } else {
                    height = originalImageHeight; // Use exact image height for other templates
                }
                
                // Set canvas dimensions
                canvas.width = width;
                canvas.height = height;
                
                // Now generate the photo card with the correct dimensions
                generatePhotoCardContent(title, lead, content, imageUrl, date, category, template, fontSize, color, isPreview, originalImageHeight);
            };
            newsImage.onerror = function() {
                console.log('News image failed to load, using default dimensions');
                // Use default dimensions if image fails to load
                canvas.width = width;
                canvas.height = height;
                generatePhotoCardContent(title, lead, content, imageUrl, date, category, template, fontSize, color, isPreview, height);
            };
            newsImage.src = imageUrl;
        } else {
            console.log('No image URL provided, using default dimensions');
            // No image, use default dimensions
            canvas.width = width;
            canvas.height = height;
            generatePhotoCardContent(title, lead, content, imageUrl, date, category, template, fontSize, color, isPreview, height);
        }
}

function generatePhotoCardContent(title, lead, content, imageUrl, date, category, template, fontSize, color, isPreview = false, originalImageHeight = null) {
        console.log('generatePhotoCardContent called with:', {
            title: title,
            template: template,
            category: category,
            imageUrl: imageUrl
        });
        
        const width = canvas.width;
        const height = canvas.height;

        // Clear canvas
        ctx.clearRect(0, 0, width, height);

        // Set background based on template
        switch (template) {
            case 'header_footer':
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);
                break;
            default:
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, width, height);
                break;
        }

        // Load logo image for later use
        logo = new Image();
        logo.crossOrigin = 'anonymous';
        logo.onload = function() {
            console.log('Logo loaded successfully');
            // Continue with image loading - logo will be drawn on top later
            loadNewsImage(imageUrl, category, template, width, height, originalImageHeight, fontSize, color, title, isPreview, date);
        };
        logo.onerror = function() {
            console.error('Logo failed to load');
            // Continue without logo
            loadNewsImage(imageUrl, category, template, width, height, originalImageHeight, fontSize, color, title, isPreview, date);
        };
        logo.src = '/logo.png';
        console.log('Logo source set to:', logo.src);

        displayResult(isPreview, width, height);
    }

    // Load news image function
    function loadNewsImage(imageUrl, category, template, width, height, originalImageHeight, fontSize, color, title, isPreview, date) {
        console.log('loadNewsImage called with imageUrl:', imageUrl);
        if (imageUrl && imageUrl !== '') {
            const newsImage = new Image();
            newsImage.crossOrigin = 'anonymous';
            newsImage.onload = function() {
                console.log('News image loaded successfully');
                if (template === 'header_footer') {
                    console.log('Drawing image for header_footer template');
                    // For header_footer template, use exact image dimensions
                    // Use the exact image dimensions - full height
                    const drawWidth = newsImage.width;
                    const drawHeight = newsImage.height;
                    const drawX = (width - drawWidth) / 2; // Center horizontally
                    const drawY = 0; // Start from top
                    
                    console.log('Image drawing params:', {
                        drawWidth: drawWidth,
                        drawHeight: drawHeight,
                        drawX: drawX,
                        drawY: drawY
                    });
                    
                    ctx.drawImage(newsImage, drawX, drawY, drawWidth, drawHeight);
                } else {
                    // For other templates, draw the full image
                    ctx.drawImage(newsImage, 0, 0, width, height);
                }
                
                // Add text overlay first
                addTextOverlay(category, template, fontSize, color, title, width, height, originalImageHeight, isPreview, date);
                
                // Draw logo LAST to ensure it's on top of everything
                setTimeout(() => {
                    console.log('Drawing logo LAST - complete:', logo.complete, 'naturalWidth:', logo.naturalWidth, 'src:', logo.src);
                    
                    // Force logo to load if not ready
                    if (!logo.complete || logo.naturalWidth === 0) {
                        console.log('Logo not ready, trying to reload...');
                        logo.onload = function() {
                            drawLogoOnTop(template, width, height, originalImageHeight);
                        };
                        logo.src = logo.src; // Reload
                    } else {
                        drawLogoOnTop(template, width, height, originalImageHeight);
                    }
                }, 200); // Increased delay to ensure everything else is drawn first
                
                drawLogoOnTop(template, width, height, originalImageHeight);
            };
            newsImage.onerror = function() {
                // Continue without news image
                drawLogo(template, width, height, originalImageHeight);
                addTextOverlay(category, template, fontSize, color, title, width, height, originalImageHeight, isPreview, date);
            };
            newsImage.src = imageUrl;
        } else {
            drawLogo(template, width, height, originalImageHeight);
            addTextOverlay(category, template, fontSize, color, title, width, height, originalImageHeight, isPreview, date);
        }
    }

    // Draw logo function
    function drawLogo(template, width, height, originalImageHeight) {
        console.log('drawLogo called, logo complete:', logo.complete, 'naturalWidth:', logo.naturalWidth);
        if (logo.complete && logo.naturalWidth !== 0) {
            if (template === 'header_footer') {
                // For header_footer template, logo goes in title section
                // Use the original image height for layout calculations
                const imageHeight = originalImageHeight || height; // Use original image height if available
                const titleHeight = Math.max(200, imageHeight * 0.2); // Minimum 200px or 20% of image height
                const footerHeight = Math.max(60, imageHeight * 0.08); // Minimum 60px or 8% of image height
                
                // Position logo 50% on image and 50% on title background
                const logoWidth = 80; // Fixed logo size
                const logoHeight = (logo.height / logo.width) * logoWidth;
                const logoX = (width - logoWidth) / 2; // Center horizontally
                const logoY = imageHeight - (logoHeight / 2); // 50% on image, 50% on title background
                
                // Draw silver semi-transparent background behind logo
                const backgroundPadding = 10; // Padding around logo
                const backgroundWidth = logoWidth + (backgroundPadding * 2);
                const backgroundHeight = logoHeight + (backgroundPadding * 2);
                const backgroundX = logoX - backgroundPadding;
                const backgroundY = logoY - backgroundPadding;
                
                // Draw semi-transparent silver background
                ctx.fillStyle = 'rgba(192, 192, 192, 0.8)'; // Silver with 80% opacity
                ctx.fillRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);
                
                // Draw the logo on top
                console.log('Drawing logo at:', logoX, logoY, 'size:', logoWidth, 'x', logoHeight);
                ctx.drawImage(logo, logoX, logoY, logoWidth, logoHeight);
            } else {
                // For other templates, use original positioning
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
    }

    // Add text overlay function
    function addTextOverlay(category, template, fontSize, color, title, width, height, originalImageHeight, isPreview, date) {
        console.log('addTextOverlay called with template:', template, 'category:', category);
        if (template === 'header_footer') {
            console.log('Calling addHeaderFooterTextOverlay');
            addHeaderFooterTextOverlay(category, template, fontSize, color, title, width, height, originalImageHeight, isPreview, date);
        } else {
            console.log('Calling addDefaultTextOverlay');
            addDefaultTextOverlay(template, fontSize, color, title, width, height, isPreview);
        }
    }

    // Add header footer text overlay function
    function addHeaderFooterTextOverlay(category, template, fontSize, color, title, width, height, originalImageHeight, isPreview, date) {
        // Set font and ensure opaque text rendering
        ctx.font = `bold ${fontSize}px "Noto Sans Bengali", Arial, sans-serif`;
        ctx.textAlign = 'center';
        ctx.globalAlpha = 1.0; // Ensure full opacity
        
        // Set text color from the color picker
        ctx.fillStyle = color;
        
        // Use the news title for display with fallback
        let displayText = title || 'No Title Available';
        
        console.log('Title debug:', {
            title: title,
            displayText: displayText,
            fontSize: fontSize
        });
        
        // Calculate layout: Image → Title → Footer
        // Use the original image height for layout calculations
        const imageHeight = originalImageHeight || height; // Use original image height if available
        const titleHeight = Math.max(200, imageHeight * 0.2); // Minimum 200px or 20% of image height
        const footerHeight = Math.max(60, imageHeight * 0.08); // Minimum 60px or 8% of image height
        
        const titleX = width / 2; // Center horizontally
        const lineHeight = fontSize + 35; // Increased line spacing from +25 to +35
        const titlePadding = 15;
        const maxTitleWidth = width - 100;
        
        // Wrap text to maximum 2 lines
        // Ensure displayText is a string before splitting
        if (typeof displayText !== 'string') {
            displayText = String(displayText || 'No Title Available');
        }
        const words = displayText.split(' ');
        const lines = [];
        let currentLine = words[0] || '';
        
        for (let i = 1; i < words.length; i++) {
            const word = words[i];
            const testLine = currentLine + ' ' + word;
            const testWidth = ctx.measureText(testLine).width;
            
            if (testWidth < maxTitleWidth) {
                currentLine = testLine;
            } else {
                if (lines.length === 0) {
                    lines.push(currentLine);
                    currentLine = word;
                } else {
                    const remainingWords = words.slice(i);
                    const secondLine = remainingWords.join(' ');
                    const secondLineWidth = ctx.measureText(secondLine).width;
                    
                    if (secondLineWidth <= maxTitleWidth) {
                        lines.push(currentLine);
                        lines.push(secondLine);
                        break;
                    } else {
                        let truncatedLine = '';
                        for (let j = 0; j < remainingWords.length; j++) {
                            const testTruncated = truncatedLine + (j > 0 ? ' ' : '') + remainingWords[j] + '...';
                            if (ctx.measureText(testTruncated).width <= maxTitleWidth) {
                                truncatedLine = testTruncated;
                            } else {
                                break;
                            }
                        }
                        lines.push(currentLine);
                        lines.push(truncatedLine || '...');
                        break;
                    }
                }
            }
        }
        
        if (lines.length === 0) {
            lines.push(currentLine);
        } else if (lines.length === 1 && currentLine !== lines[0]) {
            lines.push(currentLine);
        }
        
        if (lines.length > 2) {
            lines.splice(2);
        }
        
        // Position title in the title section, after the logo
        const titleY = imageHeight + 120; // Position after logo in title section
        
        // Draw red background for entire title section
        const titleBackgroundY = imageHeight; // Start from end of image
        
        // Debug logging
        console.log('Layout debug:', {
            imageHeight: imageHeight,
            titleHeight: titleHeight,
            titleY: titleY,
            titleBackgroundY: titleBackgroundY,
            canvasHeight: canvas.height,
            lines: lines
        });
        ctx.fillStyle = '#96031a'; // Red background
        ctx.fillRect(0, titleBackgroundY, width, titleHeight);
        
        // Draw title lines with white text
        ctx.fillStyle = '#ffffff'; // White text on red background
        ctx.font = `bold ${fontSize}px "Noto Sans Bengali", Arial, sans-serif`; // Ensure font is set
        ctx.textAlign = 'center';
        ctx.globalAlpha = 1.0; // Ensure full opacity
        
        lines.forEach((line, index) => {
            const y = titleY - ((lines.length - 1 - index) * lineHeight) / 2;
            // Use canvas height instead of original height
            if (y >= 0 && y < canvas.height) {
                console.log('Drawing title line:', line, 'at Y:', y); // Debug log
                ctx.fillText(line, titleX, y);
            }
        });
        
        // Draw footer right after title section
        const footerY = (originalImageHeight || height) + titleHeight; // Position right after title section
        
        // Draw footer background
        ctx.fillStyle = '#000000'; // Black background
        ctx.fillRect(0, footerY, width, footerHeight);
        
        // Draw footer text
        ctx.font = `16px "Noto Sans Bengali", Arial, sans-serif`;
        ctx.fillStyle = '#ffffff';
        
        // Left side: "Barind Post | Category"
        ctx.textAlign = 'left';
        const leftText = `বরিন্দ পোস্ট | ${category}`;
        ctx.fillText(leftText, 20, footerY + (footerHeight / 2) + 5);
        
        // Right side: Date
        ctx.textAlign = 'right';
        const dateText = new Date(date).toLocaleDateString('bn-BD', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        ctx.fillText(dateText, width - 20, footerY + (footerHeight / 2) + 5);
        
        // Display result
        displayResult(isPreview, width, height);
    }

    // Add default text overlay function
    function addDefaultTextOverlay(template, fontSize, color, title, width, height, isPreview) {
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
        const lineHeight = fontSize + 10;
        const titlePadding = 15;
        const maxTitleWidth = width - 100;
        
        // Wrap text to maximum 2 lines
        const words = displayText.split(' ');
        const lines = [];
        let currentLine = words[0];
        
        // Calculate the total height needed for the text (will be calculated after lines are determined)
        let totalTextHeight = 0;
        let titleY = 0;
        
        for (let i = 1; i < words.length; i++) {
            const word = words[i];
            const testLine = currentLine + ' ' + word;
            const testWidth = ctx.measureText(testLine).width;
            
            if (testWidth < maxTitleWidth) {
                currentLine = testLine;
            } else {
                // If we already have 1 line and adding this word would exceed width,
                // check if we can fit it on the second line
                if (lines.length === 0) {
                    lines.push(currentLine);
                    currentLine = word;
                } else {
                    // We already have 1 line, try to fit remaining words on second line
                    const remainingWords = words.slice(i);
                    const secondLine = remainingWords.join(' ');
                    const secondLineWidth = ctx.measureText(secondLine).width;
                    
                    if (secondLineWidth <= maxTitleWidth) {
                        // All remaining words fit on second line
                        lines.push(currentLine);
                        lines.push(secondLine);
                        break;
                    } else {
                        // Second line is too long, truncate with ellipsis
                        let truncatedLine = '';
                        for (let j = 0; j < remainingWords.length; j++) {
                            const testTruncated = truncatedLine + (j > 0 ? ' ' : '') + remainingWords[j] + '...';
                            if (ctx.measureText(testTruncated).width <= maxTitleWidth) {
                                truncatedLine = testTruncated;
                            } else {
                                break;
                            }
                        }
                        lines.push(currentLine);
                        lines.push(truncatedLine || '...');
                        break;
                    }
                }
            }
        }
        
        // Add the last line if we haven't reached 2 lines yet
        if (lines.length === 0) {
            lines.push(currentLine);
        } else if (lines.length === 1 && currentLine !== lines[0]) {
            lines.push(currentLine);
        }
        
        // Ensure we don't exceed 2 lines
        if (lines.length > 2) {
            lines.splice(2);
        }
        
        // Calculate the total height needed for the text
        totalTextHeight = (lines.length * lineHeight) + (titlePadding * 2);
        
        // Position text at the bottom of the image, accounting for multiple lines
        if (lines.length === 1) {
            titleY = height - 15; // Single line at bottom
        } else {
            // For 2 lines, adjust position to ensure both lines fit
            titleY = height - 15 - ((lines.length - 1) * lineHeight);
        }
        
        // Draw title lines with full-width semi-transparent background
        const titleMargin = 20;
        
        // Calculate background dimensions - full width
        const backgroundWidth = width; // Full width of image
        const backgroundHeight = (lines.length * lineHeight) + (titlePadding * 2);
        const backgroundX = 0; // Start from left edge
        const backgroundY = titleY - (lines.length * lineHeight) - titlePadding;
        
        // Draw semi-transparent white background
        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.fillRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);
        
        // Set text color from the color picker before drawing text
        ctx.fillStyle = color;
        
        // Draw title lines
        lines.forEach((line, index) => {
            // For 2-line text, position from bottom up
            const y = titleY - ((lines.length - 1 - index) * lineHeight);
            if (y >= 0 && y < height) { // Ensure text is within canvas bounds
                ctx.fillText(line, titleX, y);
            }
        });
        
        // Display result
        displayResult(isPreview, width, height);
    }

    // Draw footer function
    function drawFooter(category, originalImageHeight = null, date = null) {
        const footerHeight = Math.max(60, (originalImageHeight || height) * 0.08); // Minimum 60px or 8% of height
        const footerY = height - footerHeight; // This should be right after the title section
        
        // Draw footer background
        ctx.fillStyle = '#000000'; // Black background
        ctx.fillRect(0, footerY, width, footerHeight);
        
        // Draw footer text
        ctx.font = `16px "Noto Sans Bengali", Arial, sans-serif`;
        ctx.fillStyle = '#ffffff';
        
        // Left side: "Barind Post | Category"
        ctx.textAlign = 'left';
        const leftText = `বরিন্দ পোস্ট | ${category}`;
        ctx.fillText(leftText, 20, footerY + (footerHeight / 2) + 5);
        
        // Right side: Date
        ctx.textAlign = 'right';
        const dateText = new Date(date).toLocaleDateString('bn-BD', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        ctx.fillText(dateText, width - 20, footerY + (footerHeight / 2) + 5);
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

    // Draw logo on top function
    function drawLogoOnTop(template, width, height, originalImageHeight) {
        console.log('drawLogoOnTop called - logo ready:', logo.complete, 'naturalWidth:', logo.naturalWidth);
        
        // Draw a black rectangle of 100x100 in the center
        const rectSize = 100;
        const rectX = (width - rectSize) / 2; // Center horizontally
        const rectY = (height - rectSize) / 2; // Center vertically
        
        console.log('Drawing black rectangle in center:', {
            rectSize: rectSize,
            rectX: rectX,
            rectY: rectY,
            width: width,
            height: height
        });
        
        // Draw black rectangle in center
        ctx.fillStyle = '#000000'; // Black color
        ctx.fillRect(rectX, rectY, rectSize, rectSize);
        
        // Draw a black square with size 12% of image width for header_footer template
        if (template === 'header_footer') {
            const squareSize = width * 0.12; // 12% of image width
            const squareX = 20; // 20px from left edge
            const squareY = 20; // 20px from top edge
            
            console.log('Drawing black square for header_footer template:', {
                squareSize: squareSize,
                squareX: squareX,
                squareY: squareY,
                width: width
            });
            
            // Draw black square
            ctx.fillStyle = '#000000'; // Black color
            ctx.fillRect(squareX, squareY, squareSize, squareSize);
            
            // Draw red border around square for debugging
            ctx.strokeStyle = '#ff0000';
            ctx.lineWidth = 3;
            ctx.strokeRect(squareX, squareY, squareSize, squareSize);
        }
        
        if (logo.complete && logo.naturalWidth !== 0) {
            console.log('Drawing logo on top of everything');
            
            // Draw a simple logo in top-right corner for all templates
            const logoWidth = 80; // Made slightly larger
            const logoHeight = (logo.height / logo.width) * logoWidth;
            const logoX = width - logoWidth - 20;
            const logoY = 20;
            
            // Draw silver background
            const backgroundPadding = 10;
            const backgroundWidth = logoWidth + (backgroundPadding * 2);
            const backgroundHeight = logoHeight + (backgroundPadding * 2);
            const backgroundX = logoX - backgroundPadding;
            const backgroundY = logoY - backgroundPadding;
            
            // Draw background
            ctx.fillStyle = 'rgba(192, 192, 192, 0.9)';
            ctx.fillRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);
            
            // Draw red border for debugging
            ctx.strokeStyle = '#ff0000';
            ctx.lineWidth = 3; // Made border thicker
            ctx.strokeRect(backgroundX, backgroundY, backgroundWidth, backgroundHeight);
            
            // Draw logo
            console.log('Drawing logo at:', logoX, logoY, 'size:', logoWidth, 'x', logoHeight);
            ctx.drawImage(logo, logoX, logoY, logoWidth, logoHeight);
            
            // Also draw a test rectangle to verify drawing is working
            ctx.fillStyle = '#00ff00'; // Green
            ctx.fillRect(10, 10, 50, 50); // Small green square in top-left
        } else {
            console.log('Logo still not ready after reload');
            // Draw a test rectangle anyway to verify drawing works
            ctx.fillStyle = '#00ff00'; // Green
            ctx.fillRect(10, 10, 50, 50); // Small green square in top-left
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