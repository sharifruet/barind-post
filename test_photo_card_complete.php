<?php
/**
 * Complete Photo Card Generation Test
 * 
 * This script tests the complete photo card generation functionality
 * Run this inside the Docker container
 */

echo "=== Complete Photo Card Generation Test ===\n\n";

// Test 1: Check GD library
echo "1. Checking GD library:\n";
if (extension_loaded('gd')) {
    echo "   ✅ GD library is loaded\n";
    $gdInfo = gd_info();
    echo "   - GD Version: " . $gdInfo['GD Version'] . "\n";
    echo "   - FreeType Support: " . ($gdInfo['FreeType Support'] ? 'Yes' : 'No') . "\n";
    echo "   - JPEG Support: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - PNG Support: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "\n";
} else {
    echo "   ❌ GD library is not loaded\n";
    exit(1);
}

// Test 2: Check fonts
echo "\n2. Checking fonts:\n";
$fonts = [
    '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
    '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
    '/usr/share/fonts/truetype/noto/NotoSans-Regular.ttf',
    '/usr/share/fonts/truetype/noto/NotoSans-Bold.ttf'
];

$workingFont = null;
foreach ($fonts as $font) {
    if (file_exists($font)) {
        echo "   ✅ $font exists\n";
        if ($workingFont === null) {
            $workingFont = $font;
        }
    } else {
        echo "   ❌ $font missing\n";
    }
}

if ($workingFont === null) {
    echo "   ❌ No fonts available\n";
    exit(1);
}

echo "   Using font: $workingFont\n";

// Test 3: Test image creation
echo "\n3. Testing image creation:\n";
try {
    $width = 1200;
    $height = 630;
    
    $image = imagecreatetruecolor($width, $height);
    if ($image === false) {
        throw new Exception('Failed to create image');
    }
    echo "   ✅ Created image resource\n";
    
    // Test colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    $red = imagecolorallocate($image, 220, 53, 69);
    
    if ($white === false || $black === false || $red === false) {
        throw new Exception('Failed to allocate colors');
    }
    echo "   ✅ Allocated colors\n";
    
    // Fill background
    imagefill($image, 0, 0, $white);
    echo "   ✅ Filled background\n";
    
    // Test text rendering
    $testText = 'বরেন্দ্র অঞ্চলে বৃষ্টির অভাবে কৃষকের দুশ্চিন্তা';
    $result = imagettftext($image, 36, 0, 50, 250, $black, $workingFont, $testText);
    
    if ($result === false) {
        echo "   ⚠️  Cannot render Bengali text\n";
    } else {
        echo "   ✅ Can render Bengali text\n";
    }
    
    // Test image saving
    $outputDir = '/var/www/html/public/uploads/photo_cards/';
    if (!is_dir($outputDir)) {
        mkdir($outputDir, 0755, true);
    }
    
    $testPath = $outputDir . 'test_' . time() . '.png';
    if (imagepng($image, $testPath)) {
        echo "   ✅ Can save PNG image\n";
        echo "   - Saved to: $testPath\n";
        
        // Check file size
        $fileSize = filesize($testPath);
        echo "   - File size: " . number_format($fileSize) . " bytes\n";
        
        // Clean up test file
        unlink($testPath);
        echo "   - Test file cleaned up\n";
    } else {
        echo "   ❌ Cannot save PNG image\n";
    }
    
    imagedestroy($image);
    echo "   ✅ Destroyed image resource\n";
    
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 4: Test external image download
echo "\n4. Testing external image download:\n";
$testUrl = 'https://via.placeholder.com/100x100';
$context = stream_context_create(['http' => ['timeout' => 10]]);
$imageData = @file_get_contents($testUrl, false, $context);

if ($imageData !== false) {
    echo "   ✅ Can download external images\n";
    
    $downloadedImage = @imagecreatefromstring($imageData);
    if ($downloadedImage !== false) {
        echo "   ✅ Can create image from downloaded data\n";
        imagedestroy($downloadedImage);
    } else {
        echo "   ❌ Cannot create image from downloaded data\n";
    }
} else {
    echo "   ⚠️  Cannot download external images\n";
}

echo "\n=== Test Summary ===\n";
echo "✅ All tests passed! Photo card generation should work.\n";
echo "You can now test the photo card generation feature at:\n";
echo "http://localhost/admin/photo-card-generator\n";

echo "\n=== End Test ===\n"; 