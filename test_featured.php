<?php
// Simple test script to check featured functionality
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
require_once $pathsPath;

$paths = new Config\Paths();
require_once $paths->systemDirectory . '/Boot.php';

// Boot the application
$app = new CodeIgniter\CodeIgniter(new Config\Paths());
$app->initialize();

// Test database connection and featured column
try {
    $db = \Config\Database::connect();
    
    // Check if featured column exists
    $result = $db->query("SHOW COLUMNS FROM news LIKE 'featured'");
    if ($result->getNumRows() > 0) {
        echo "✅ Featured column exists in news table\n";
        
        // Test updating featured status
        $newsModel = new \App\Models\NewsModel();
        $news = $newsModel->first();
        
        if ($news) {
            echo "✅ Found news article with ID: " . $news['id'] . "\n";
            echo "Current featured status: " . ($news['featured'] ? 'Yes' : 'No') . "\n";
            
            // Toggle featured status
            $newStatus = !$news['featured'];
            $newsModel->update($news['id'], ['featured' => $newStatus]);
            
            echo "✅ Updated featured status to: " . ($newStatus ? 'Yes' : 'No') . "\n";
            
            // Verify the change
            $updatedNews = $newsModel->find($news['id']);
            echo "✅ Verified featured status is now: " . ($updatedNews['featured'] ? 'Yes' : 'No') . "\n";
            
            // Revert the change
            $newsModel->update($news['id'], ['featured' => $news['featured']]);
            echo "✅ Reverted featured status back to original\n";
        } else {
            echo "❌ No news articles found in database\n";
        }
    } else {
        echo "❌ Featured column does not exist in news table\n";
        echo "Please run the SQL: ALTER TABLE news ADD COLUMN featured BOOLEAN NOT NULL DEFAULT FALSE AFTER status;\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
} 