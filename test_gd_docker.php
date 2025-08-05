<?php
/**
 * Test GD Library in Docker Container
 * 
 * Run this inside the Docker container to verify GD library installation
 * Usage: docker-compose exec app php test_gd_docker.php
 */

echo "=== GD Library Test in Docker Container ===\n\n";

// Test 1: Check if GD extension is loaded
echo "1. Checking GD extension:\n";
if (extension_loaded('gd')) {
    echo "   ✅ GD extension is loaded\n";
    
    // Get GD info
    $gdInfo = gd_info();
    echo "   - GD Version: " . $gdInfo['GD Version'] . "\n";
    echo "   - FreeType Support: " . ($gdInfo['FreeType Support'] ? 'Yes' : 'No') . "\n";
    echo "   - JPEG Support: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - PNG Support: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "\n";
} else {
    echo "   ❌ GD extension is NOT loaded\n";
    exit(1);
}

// Test 2: Test basic image creation
echo "\n2. Testing basic image creation:\n";
try {
    $image = imagecreatetruecolor(100, 100);
    if ($image) {
        echo "   ✅ Can create image resource\n";
        
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        
        if ($white !== false && $black !== false) {
            echo "   ✅ Can allocate colors\n";
        } else {
            echo "   ❌ Cannot allocate colors\n";
        }
        
        // Fill background
        imagefill($image, 0, 0, $white);
        echo "   ✅ Can fill image\n";
        
        // Test text rendering
        $fontPath = '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf';
        if (file_exists($fontPath)) {
            $result = imagettftext($image, 12, 0, 10, 50, $black, $fontPath, 'Test');
            if ($result !== false) {
                echo "   ✅ Can render text with TTF fonts\n";
            } else {
                echo "   ⚠️  Cannot render text with TTF fonts\n";
            }
        } else {
            echo "   ⚠️  TTF font not found, trying system fonts\n";
            // Try system fonts
            $systemFonts = [
                '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
                '/System/Library/Fonts/Arial.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf'
            ];
            
            $fontFound = false;
            foreach ($systemFonts as $font) {
                if (file_exists($font)) {
                    $result = imagettftext($image, 12, 0, 10, 50, $black, $font, 'Test');
                    if ($result !== false) {
                        echo "   ✅ Can render text with font: $font\n";
                        $fontFound = true;
                        break;
                    }
                }
            }
            
            if (!$fontFound) {
                echo "   ⚠️  No suitable fonts found for text rendering\n";
            }
        }
        
        imagedestroy($image);
        echo "   ✅ Can destroy image resource\n";
    } else {
        echo "   ❌ Cannot create image resource\n";
    }
} catch (Exception $e) {
    echo "   ❌ Error testing image creation: " . $e->getMessage() . "\n";
}

// Test 3: Test file operations
echo "\n3. Testing file operations:\n";
$testDir = '/var/www/html/public/uploads/photo_cards/';
if (is_dir($testDir)) {
    echo "   ✅ Photo cards directory exists\n";
    if (is_writable($testDir)) {
        echo "   ✅ Photo cards directory is writable\n";
    } else {
        echo "   ❌ Photo cards directory is not writable\n";
    }
} else {
    echo "   ⚠️  Photo cards directory does not exist\n";
    if (mkdir($testDir, 0755, true)) {
        echo "   ✅ Created photo cards directory\n";
    } else {
        echo "   ❌ Failed to create photo cards directory\n";
    }
}

// Test 4: Test external image download
echo "\n4. Testing external image download:\n";
$testUrl = 'https://via.placeholder.com/100x100';
$context = stream_context_create(['http' => ['timeout' => 10]]);
$imageData = @file_get_contents($testUrl, false, $context);

if ($imageData !== false) {
    echo "   ✅ Can download external images\n";
    
    // Test if we can create image from downloaded data
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
echo "If all tests pass, the photo card generation should work.\n";
echo "If any tests fail, check the Docker build and PHP configuration.\n";

echo "\n=== End Test ===\n"; 