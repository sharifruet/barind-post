<?php

// Test script to check existing images functionality
require_once 'app/Config/Database.php';

try {
    $db = \Config\Database::connect();
    
    echo "=== Testing Existing Images Feature ===\n\n";
    
    // Check if images table exists
    $result = $db->query("SHOW TABLES LIKE 'images'");
    if ($result->getNumRows() == 0) {
        echo "❌ Images table does not exist!\n";
        exit(1);
    }
    echo "✅ Images table exists\n";
    
    // Check how many images are in the database
    $result = $db->query("SELECT COUNT(*) as count FROM images");
    $count = $result->getRow()->count;
    echo "📊 Total images in database: $count\n";
    
    if ($count == 0) {
        echo "❌ No images found in database!\n";
        echo "This is why the existing images feature shows no images.\n";
        echo "\nPossible solutions:\n";
        echo "1. Upload some images first using the 'Upload New Image' feature\n";
        echo "2. Check if the database was properly initialized\n";
        echo "3. Verify that the ImageUpload controller is working\n";
        exit(1);
    }
    
    // Show some sample images
    echo "\n📸 Sample images in database:\n";
    $result = $db->query("SELECT id, image_name, image_path, caption, created_at FROM images ORDER BY created_at DESC LIMIT 5");
    foreach ($result->getResultArray() as $image) {
        echo "- ID: {$image['id']}, Name: {$image['image_name']}, Path: {$image['image_path']}\n";
    }
    
    // Test the ImageModel
    echo "\n🔧 Testing ImageModel...\n";
    require_once 'app/Models/ImageModel.php';
    $imageModel = new \App\Models\ImageModel();
    
    try {
        $images = $imageModel->getAllImagesWithUsage();
        echo "✅ ImageModel::getAllImagesWithUsage() returned " . count($images) . " images\n";
        
        if (count($images) > 0) {
            $sample = $images[0];
            echo "📋 Sample image data:\n";
            echo "- ID: {$sample['id']}\n";
            echo "- Name: {$sample['image_name']}\n";
            echo "- Path: {$sample['image_path']}\n";
            echo "- Usage Count: {$sample['usage_count']}\n";
            echo "- Created: {$sample['created_at']}\n";
        }
    } catch (Exception $e) {
        echo "❌ ImageModel error: " . $e->getMessage() . "\n";
    }
    
    // Check if images exist in filesystem
    echo "\n📁 Checking image files...\n";
    $result = $db->query("SELECT image_path FROM images LIMIT 3");
    foreach ($result->getResultArray() as $image) {
        $fullPath = __DIR__ . '/' . $image['image_path'];
        if (file_exists($fullPath)) {
            echo "✅ File exists: {$image['image_path']}\n";
        } else {
            echo "❌ File missing: {$image['image_path']}\n";
        }
    }
    
    echo "\n🎯 Conclusion:\n";
    if ($count > 0) {
        echo "✅ Database has images - the existing images feature should work\n";
        echo "💡 If you're still seeing 'no images', check:\n";
        echo "   1. Browser console for JavaScript errors\n";
        echo "   2. Network tab for failed AJAX requests\n";
        echo "   3. Server logs for PHP errors\n";
    } else {
        echo "❌ No images in database - upload some images first\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database connection error: " . $e->getMessage() . "\n";
}
