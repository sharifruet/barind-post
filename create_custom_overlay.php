<?php
// Script to create bottom left overlay with customizable Bengali text

// Check if GD extension is available
if (!extension_loaded('gd')) {
    die("GD extension is not available. Please install GD extension for PHP.\n");
}

// Configuration - you can change these values
$config = [
    'baseImage' => __DIR__ . '/public/Slide1.jpeg',
    'overlayImage' => __DIR__ . '/public/noname.png',
    'outputImage' => __DIR__ . '/public/overlay_custom.jpg',
    'text' => 'বারিন্দ পোস্ট', // Change this text as needed
    'textSize' => 24, // Font size for imagettftext (in points)
    'textColor' => [255, 255, 255], // White color [R, G, B]
    'shadowColor' => [0, 0, 0], // Black shadow [R, G, B]
    'position' => 'bottom-left', // 'bottom-left', 'center', 'top-left', etc.
    'quality' => 90
];

// Check if images exist
if (!file_exists($config['baseImage'])) {
    die("Base image not found: {$config['baseImage']}\n");
}

if (!file_exists($config['overlayImage'])) {
    die("Overlay image not found: {$config['overlayImage']}\n");
}

try {
    // Load images
    $baseImage = imagecreatefromjpeg($config['baseImage']);
    $overlayImage = imagecreatefrompng($config['overlayImage']);
    
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
    
    // Calculate overlay position
    switch ($config['position']) {
        case 'bottom-left':
            $x = 0;
            $y = $baseHeight - $overlayHeight;
            break;
        case 'center':
            $x = ($baseWidth - $overlayWidth) / 2;
            $y = ($baseHeight - $overlayHeight) / 2;
            break;
        case 'top-left':
            $x = 0;
            $y = 0;
            break;
        case 'top-right':
            $x = $baseWidth - $overlayWidth;
            $y = 0;
            break;
        case 'bottom-right':
            $x = $baseWidth - $overlayWidth;
            $y = $baseHeight - $overlayHeight;
            break;
        default:
            $x = 0;
            $y = $baseHeight - $overlayHeight;
    }
    
    echo "Position: {$config['position']} (x={$x}, y={$y})\n";
    
    // Enable alpha blending
    imagealphablending($baseImage, true);
    imagesavealpha($baseImage, true);
    
    // Copy overlay onto base image
    $result = imagecopy($baseImage, $overlayImage, $x, $y, 0, 0, $overlayWidth, $overlayHeight);
    
    if (!$result) {
        die("Failed to overlay images\n");
    }
    
    // Create colors
    $textColor = imagecolorallocate($baseImage, $config['textColor'][0], $config['textColor'][1], $config['textColor'][2]);
    $shadowColor = imagecolorallocate($baseImage, $config['shadowColor'][0], $config['shadowColor'][1], $config['shadowColor'][2]);
    
    // Try to find a suitable font for Bengali text
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
        $textX = (int)($x + ($overlayWidth / 2) - (strlen($config['text']) * 6));
        $textY = (int)($y + ($overlayHeight / 2) - 10);
        imagestring($baseImage, $config['textSize'], $textX + 1, $textY + 1, $config['text'], $shadowColor);
        imagestring($baseImage, $config['textSize'], $textX, $textY, $config['text'], $textColor);
    } else {
        // Use imagettftext with proper font
        echo "Using font: $fontPath\n";
        
        // Get text bounding box to center it properly
        $bbox = imagettfbbox($config['textSize'], 0, $fontPath, $config['text']);
        $textWidth = $bbox[4] - $bbox[0];
        $textHeight = $bbox[1] - $bbox[5];
        
        // Calculate text position (center of the overlay)
        $textX = (int)($x + ($overlayWidth / 2) - ($textWidth / 2));
        $textY = (int)($y + ($overlayHeight / 2) + ($textHeight / 2));
        
        // Add shadow for better visibility
        imagettftext($baseImage, $config['textSize'], 0, $textX + 2, $textY + 2, $shadowColor, $fontPath, $config['text']);
        
        // Add main text
        imagettftext($baseImage, $config['textSize'], 0, $textX, $textY, $textColor, $fontPath, $config['text']);
    }
    
    echo "Added Bengali text: '{$config['text']}'\n";
    
    // Save the result
    $saveResult = imagejpeg($baseImage, $config['outputImage'], $config['quality']);
    
    if (!$saveResult) {
        die("Failed to save output image\n");
    }
    
    echo "Success! Overlay image with text created at: {$config['outputImage']}\n";
    echo "Output image dimensions: {$baseWidth}x{$baseHeight}\n";
    echo "Text: '{$config['text']}' at position ({$textX}, {$textY})\n";
    
    // Clean up
    imagedestroy($baseImage);
    imagedestroy($overlayImage);
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 