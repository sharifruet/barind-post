<?php
/**
 * Test file for image upload and selection functionality
 * This file can be used to test the new image features
 */

// Test the ImageUpload controller methods
echo "Testing Image Upload Functionality\n";
echo "==================================\n\n";

// Test 1: Check if ImageUpload controller exists
if (file_exists('app/Controllers/ImageUpload.php')) {
    echo "✓ ImageUpload controller exists\n";
} else {
    echo "✗ ImageUpload controller not found\n";
}

// Test 2: Check if ImageModel exists
if (file_exists('app/Models/ImageModel.php')) {
    echo "✓ ImageModel exists\n";
} else {
    echo "✗ ImageModel not found\n";
}

// Test 3: Check if routes are configured
$routesContent = file_get_contents('app/Config/Routes.php');
if (strpos($routesContent, '/image-upload/existing-images') !== false) {
    echo "✓ Existing images route configured\n";
} else {
    echo "✗ Existing images route not found\n";
}

if (strpos($routesContent, '/image-upload/upload') !== false) {
    echo "✓ Image upload route configured\n";
} else {
    echo "✗ Image upload route not found\n";
}

// Test 4: Check if news form has the new functionality
$newsFormContent = file_get_contents('app/Views/admin/news_form.php');
if (strpos($newsFormContent, 'selectExistingBtn') !== false) {
    echo "✓ Existing images selection button found\n";
} else {
    echo "✗ Existing images selection button not found\n";
}

if (strpos($newsFormContent, 'existingImagesModal') !== false) {
    echo "✓ Existing images modal found\n";
} else {
    echo "✗ Existing images modal not found\n";
}

if (strpos($newsFormContent, 'loadExistingImages') !== false) {
    echo "✓ Load existing images function found\n";
} else {
    echo "✗ Load existing images function not found\n";
}

// Test 5: Check if Admin controller handles image data correctly
$adminControllerContent = file_get_contents('app/Controllers/Admin.php');
if (strpos($adminControllerContent, 'image_caption') !== false && strpos($adminControllerContent, 'image_alt_text') !== false) {
    echo "✓ Admin controller handles image caption and alt text\n";
} else {
    echo "✗ Admin controller missing image caption/alt text handling\n";
}

echo "\nTest completed!\n";
echo "To test the functionality:\n";
echo "1. Start the server: php spark serve\n";
echo "2. Go to /admin/news/create\n";
echo "3. Try uploading a new image with caption and alt text\n";
echo "4. Try selecting from existing images\n";
echo "5. Verify the data is saved correctly\n";
?> 