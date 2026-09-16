<?php
// Script to create bottom left overlay of noname.png on Slide1.jpeg with Bengali text

// Check if GD extension is available
if (!extension_loaded('gd')) {
    die("GD extension is not available. Please install GD extension for PHP.\n");
}

// Define file paths
$baseImagePath = __DIR__ . '/public/Slide1.jpeg';
$overlayImagePath = __DIR__ . '/public/noname.png';
$outputImagePath = __DIR__ . '/public/overlay_bottom_left.jpg';

// Check if images exist
if (!file_exists($baseImagePath)) {
    die("Base image not found: $baseImagePath\n");
}

if (!file_exists($overlayImagePath)) {
    die("Overlay image not found: $overlayImagePath\n");
}

try {
    // Load images
    $baseImage = imagecreatefromjpeg($baseImagePath);
    $overlayImage = imagecreatefrompng($overlayImagePath);
    
    if (!$baseImage || !$overlayImage) {
        die("Failed to load images\n");
    }
    
    // Get dimensions
    $baseWidth = imagesx($baseImage);
    $baseHeight = imagesy($baseImage);
    $overlayWidth = imagesx($overlayImage);
    $overlayHeight = imagesy($overlayImage);
    
    echo "Base image: {$baseWidth}x{$baseHeight}\n";
    echo "Overlay image: {$overlayWidth}x{$overlayHeight}\n";
    
    // Position overlay at bottom left
    $x = 0;
    $y = $baseHeight - $overlayHeight;
    
    echo "Position: Bottom Left (x={$x}, y={$y})\n";
    
    // Enable alpha blending
    imagealphablending($baseImage, true);
    imagesavealpha($baseImage, true);
    
    // Copy overlay onto base image
    $result = imagecopy($baseImage, $overlayImage, $x, $y, 0, 0, $overlayWidth, $overlayHeight);
    
    if (!$result) {
        die("Failed to overlay images\n");
    }
    
    // Add Bengali text on the overlay using imagettftext with a system font
    $text = "বারিন্দ পোস্ট";
    $textSize = 24; // Increased size for better visibility
    $textColor = imagecolorallocate($baseImage, 255, 255, 255); // White color
    $shadowColor = imagecolorallocate($baseImage, 0, 0, 0); // Black shadow
    
    // Try to find a suitable font for Bengali text (prioritizing ligature support)
    $fontPath = null;
    $possibleFonts = [
        '/usr/share/fonts/truetype/fonts-beng-extra/Mukti.ttf', // Best for ligatures
        '/usr/share/fonts/truetype/fonts-beng-extra/Ani.ttf', // Good for ligatures
        '/usr/share/fonts/truetype/fonts-beng-extra/LikhanNormal.ttf', // Good for ligatures
        '/usr/share/fonts/truetype/lohit-bengali/Lohit-Bengali.ttf', // Fallback
        '/usr/share/fonts/truetype/noto/NotoSansBengali-Regular.ttf',
        '/usr/share/fonts/truetype/noto/NotoSansBengali-Bold.ttf',
        '/usr/share/fonts/truetype/noto/NotoSerifBengali-Regular.ttf',
        '/usr/share/fonts/truetype/noto/NotoSansBengaliUI-Regular.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
        '/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf',
        '/usr/share/fonts/truetype/ubuntu/Ubuntu-Regular.ttf',
        '/System/Library/Fonts/Arial.ttf', // macOS
        '/System/Library/Fonts/Helvetica.ttc', // macOS
        'C:/Windows/Fonts/arial.ttf', // Windows
        'C:/Windows/Fonts/calibri.ttf' // Windows
    ];
    
    foreach ($possibleFonts as $font) {
        if (file_exists($font)) {
            $fontPath = $font;
            break;
        }
    }
    
    if (!$fontPath) {
        // Fallback to imagestring if no suitable font found
        echo "Warning: No suitable font found for Bengali text. Using fallback method.\n";
        $textX = (int)($x + ($overlayWidth / 2) - (strlen($text) * 6));
        $textY = (int)($y + ($overlayHeight / 2) - 10);
        imagestring($baseImage, 5, $textX + 1, $textY + 1, $text, $shadowColor);
        imagestring($baseImage, 5, $textX, $textY, $text, $textColor);
    } else {
        // Use imagettftext with proper font
        echo "Using font: $fontPath\n";
        
        // Get text bounding box to center it properly
        $bbox = imagettfbbox($textSize, 0, $fontPath, $text);
        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[5];
        
        // Calculate text position (center of the overlay)
        $textX = (int)($x + ($overlayWidth / 2) - ($textWidth / 2));
        $textY = (int)($y + ($overlayHeight / 2) + ($textHeight / 2));
        
        // Add shadow for better visibility
        imagettftext($baseImage, $textSize, 0, $textX + 2, $textY + 2, $shadowColor, $fontPath, $text);
        
        // Add main text
        imagettftext($baseImage, $textSize, 0, $textX, $textY, $textColor, $fontPath, $text);
    }
    
    echo "Added Bengali text: '$text'\n";
    
    // Save the result
    $saveResult = imagejpeg($baseImage, $outputImagePath, 90);
    
    if (!$saveResult) {
        die("Failed to save output image\n");
    }
    
    echo "Success! Overlay image with text created at: $outputImagePath\n";
    echo "Output image dimensions: {$baseWidth}x{$baseHeight}\n";
    
    // Clean up
    imagedestroy($baseImage);
    imagedestroy($overlayImage);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 