<?php
/**
 * Test Fonts in Docker Container
 * 
 * Run this inside the Docker container to verify font installation
 * Usage: docker-compose exec app php test_fonts_docker.php
 */

echo "=== Font Test in Docker Container ===\n\n";

// Test 1: Check if system fonts are available
echo "1. Checking system fonts:\n";
$systemFonts = [
    '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
    '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
    '/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf',
    '/usr/share/fonts/truetype/noto/NotoSans-Bold.ttf'
];

foreach ($systemFonts as $font) {
    if (file_exists($font)) {
        echo "   ✅ $font exists\n";
        $fileSize = filesize($font);
        echo "      Size: " . number_format($fileSize) . " bytes\n";
    } else {
        echo "   ❌ $font missing\n";
    }
}

// Test 2: Check alternative fonts
echo "\n2. Checking alternative fonts:\n";
$alternativeFonts = [
    '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
    '/System/Library/Fonts/Supplemental/Arial Unicode MS.ttf',
    '/System/Library/Fonts/Arial.ttf'
];

foreach ($alternativeFonts as $font) {
    if (file_exists($font)) {
        echo "   ✅ $font exists\n";
    } else {
        echo "   ❌ $font missing\n";
    }
}

// Test 3: Test font rendering
echo "\n3. Testing font rendering:\n";
if (extension_loaded('gd')) {
    $image = imagecreatetruecolor(200, 100);
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    imagefill($image, 0, 0, $white);
    
    // Try each font
    $fonts = array_merge($systemFonts, $alternativeFonts);
    $fontFound = false;
    
    foreach ($fonts as $font) {
        if (file_exists($font)) {
            $result = imagettftext($image, 12, 0, 10, 30, $black, $font, 'Test বাংলা');
            if ($result !== false) {
                echo "   ✅ Can render text with: " . basename($font) . "\n";
                $fontFound = true;
                break;
            } else {
                echo "   ⚠️  Cannot render text with: " . basename($font) . "\n";
            }
        }
    }
    
    if (!$fontFound) {
        echo "   ❌ No fonts can render text\n";
    }
    
    imagedestroy($image);
} else {
    echo "   ❌ GD library not available\n";
}

// Test 4: Check font cache
echo "\n4. Checking font cache:\n";
$fontCacheDir = '/var/cache/fontconfig/';
if (is_dir($fontCacheDir)) {
    echo "   ✅ Font cache directory exists\n";
    $cacheFiles = glob($fontCacheDir . '*');
    echo "      Cache files: " . count($cacheFiles) . "\n";
} else {
    echo "   ⚠️  Font cache directory not found\n";
}

echo "\n=== Font Test Summary ===\n";
echo "If Noto Sans Bengali fonts are missing, rebuild the Docker container.\n";
echo "If fonts exist but rendering fails, check GD library configuration.\n";

echo "\n=== End Font Test ===\n"; 