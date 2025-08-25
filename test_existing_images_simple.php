<?php
// Simple test for existing images functionality
// This can be accessed via web browser

// Load CodeIgniter
require_once 'app/Config/Database.php';

echo "<h1>Testing Existing Images Feature</h1>";

try {
    $db = \Config\Database::connect();
    
    echo "<h2>Database Connection</h2>";
    echo "✅ Database connected successfully<br>";
    
    // Check if images table exists
    $result = $db->query("SHOW TABLES LIKE 'images'");
    if ($result->getNumRows() == 0) {
        echo "❌ Images table does not exist!<br>";
        exit;
    }
    echo "✅ Images table exists<br>";
    
    // Check table structure
    echo "<h2>Images Table Structure</h2>";
    $result = $db->query("DESCRIBE images");
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($result->getResultArray() as $row) {
        echo "<tr>";
        echo "<td>{$row['Field']}</td>";
        echo "<td>{$row['Type']}</td>";
        echo "<td>{$row['Null']}</td>";
        echo "<td>{$row['Key']}</td>";
        echo "<td>{$row['Default']}</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Check how many images are in the database
    $result = $db->query("SELECT COUNT(*) as count FROM images");
    $count = $result->getRow()->count;
    echo "<h2>Image Count</h2>";
    echo "📊 Total images in database: <strong>$count</strong><br>";
    
    if ($count == 0) {
        echo "<div style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999; margin: 10px 0;'>";
        echo "<strong>❌ No images found in database!</strong><br>";
        echo "This is why the existing images feature shows no images.<br><br>";
        echo "<strong>Possible solutions:</strong><br>";
        echo "1. Upload some images first using the 'Upload New Image' feature<br>";
        echo "2. Check if the database was properly initialized<br>";
        echo "3. Verify that the ImageUpload controller is working<br>";
        echo "</div>";
    } else {
        // Show some sample images
        echo "<h2>Sample Images</h2>";
        $result = $db->query("SELECT id, image_name, image_path, caption, created_at FROM images ORDER BY created_at DESC LIMIT 5");
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Name</th><th>Path</th><th>Caption</th><th>Created</th></tr>";
        foreach ($result->getResultArray() as $image) {
            echo "<tr>";
            echo "<td>{$image['id']}</td>";
            echo "<td>{$image['image_name']}</td>";
            echo "<td>{$image['image_path']}</td>";
            echo "<td>{$image['caption']}</td>";
            echo "<td>{$image['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Test the ImageModel
        echo "<h2>ImageModel Test</h2>";
        require_once 'app/Models/ImageModel.php';
        $imageModel = new \App\Models\ImageModel();
        
        try {
            $images = $imageModel->getAllImagesWithUsage();
            echo "✅ ImageModel::getAllImagesWithUsage() returned <strong>" . count($images) . "</strong> images<br>";
            
            if (count($images) > 0) {
                $sample = $images[0];
                echo "<div style='background: #e6ffe6; padding: 10px; border: 1px solid #99ff99; margin: 10px 0;'>";
                echo "<strong>Sample image data:</strong><br>";
                echo "- ID: {$sample['id']}<br>";
                echo "- Name: {$sample['image_name']}<br>";
                echo "- Path: {$sample['image_path']}<br>";
                echo "- Usage Count: {$sample['usage_count']}<br>";
                echo "- Created: {$sample['created_at']}<br>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999; margin: 10px 0;'>";
            echo "❌ ImageModel error: " . $e->getMessage() . "<br>";
            echo "</div>";
        }
        
        // Check if images exist in filesystem
        echo "<h2>File System Check</h2>";
        $result = $db->query("SELECT image_path FROM images LIMIT 3");
        foreach ($result->getResultArray() as $image) {
            $fullPath = __DIR__ . '/' . $image['image_path'];
            if (file_exists($fullPath)) {
                echo "✅ File exists: {$image['image_path']}<br>";
            } else {
                echo "❌ File missing: {$image['image_path']}<br>";
            }
        }
        
        echo "<h2>Conclusion</h2>";
        echo "<div style='background: #e6ffe6; padding: 10px; border: 1px solid #99ff99; margin: 10px 0;'>";
        echo "✅ Database has images - the existing images feature should work<br>";
        echo "💡 If you're still seeing 'no images', check:<br>";
        echo "   1. Browser console for JavaScript errors<br>";
        echo "   2. Network tab for failed AJAX requests<br>";
        echo "   3. Server logs for PHP errors<br>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffe6e6; padding: 10px; border: 1px solid #ff9999; margin: 10px 0;'>";
    echo "❌ Database connection error: " . $e->getMessage() . "<br>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='/admin/news/create'>← Back to News Create Page</a></p>";
?>
