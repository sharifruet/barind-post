<?php
/**
 * Debug script for Photo Card Generation 500 Error
 * 
 * This script helps identify the cause of the 500 error
 */

echo "=== Photo Card Generation Debug ===\n\n";

// Test 1: Check if we can access the basic files
echo "1. Checking file access:\n";
$files = [
    'app/Controllers/Admin.php',
    'app/Views/admin/photo_card_generator.php',
    'app/Helpers/slug_helper.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists\n";
    } else {
        echo "   ❌ $file missing\n";
    }
}

// Test 2: Check PHP extensions
echo "\n2. Checking PHP extensions:\n";
$extensions = ['gd', 'mbstring', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "   ✅ $ext extension loaded\n";
    } else {
        echo "   ❌ $ext extension missing\n";
    }
}

// Test 3: Check directory permissions
echo "\n3. Checking directory permissions:\n";
$dirs = [
    'public/uploads/',
    'public/uploads/photo_cards/',
    'writable/logs/'
];

foreach ($dirs as $dir) {
    if (is_dir($dir)) {
        echo "   ✅ $dir exists\n";
        if (is_writable($dir)) {
            echo "   ✅ $dir is writable\n";
        } else {
            echo "   ❌ $dir is not writable\n";
        }
    } else {
        echo "   ❌ $dir missing\n";
    }
}

// Test 4: Check if we can create a simple image
echo "\n4. Testing GD library functionality:\n";
if (extension_loaded('gd')) {
    try {
        $image = imagecreatetruecolor(100, 100);
        if ($image) {
            echo "   ✅ Can create image resource\n";
            
            $white = imagecolorallocate($image, 255, 255, 255);
            if ($white !== false) {
                echo "   ✅ Can allocate colors\n";
            } else {
                echo "   ❌ Cannot allocate colors\n";
            }
            
            imagedestroy($image);
            echo "   ✅ Can destroy image resource\n";
        } else {
            echo "   ❌ Cannot create image resource\n";
        }
    } catch (Exception $e) {
        echo "   ❌ GD test failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "   ❌ GD library not available\n";
}

// Test 5: Check CodeIgniter configuration
echo "\n5. Checking CodeIgniter setup:\n";
if (file_exists('app/Config/App.php')) {
    echo "   ✅ App config exists\n";
} else {
    echo "   ❌ App config missing\n";
}

if (file_exists('index.php')) {
    echo "   ✅ index.php exists\n";
} else {
    echo "   ❌ index.php missing\n";
}

// Test 6: Check for common error causes
echo "\n6. Common error causes:\n";

// Check if Exception class is available
if (class_exists('Exception')) {
    echo "   ✅ Exception class available\n";
} else {
    echo "   ❌ Exception class missing\n";
}

// Check if we can use file_get_contents
$testUrl = 'https://via.placeholder.com/100x100';
$context = stream_context_create(['http' => ['timeout' => 5]]);
$testData = @file_get_contents($testUrl, false, $context);
if ($testData !== false) {
    echo "   ✅ Can download external images\n";
} else {
    echo "   ⚠️  Cannot download external images (may cause issues)\n";
}

echo "\n=== Debug Summary ===\n";
echo "If you're getting a 500 error, check:\n";
echo "1. PHP error logs in writable/logs/\n";
echo "2. Web server error logs\n";
echo "3. Make sure GD library is installed\n";
echo "4. Check file permissions on upload directories\n";
echo "5. Verify all required files exist\n";

echo "\nTo test the photo card generation:\n";
echo "1. Visit /admin/photo-card-test (if logged in as admin)\n";
echo "2. Check the response for specific error messages\n";
echo "3. Look at the browser's developer tools Network tab\n";

echo "\n=== End Debug ===\n"; 