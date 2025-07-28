<?php
// Test script to verify featured functionality
require_once 'vendor/autoload.php';

// Initialize CodeIgniter
$pathsPath = realpath(FCPATH . '../app/Config/Paths.php');
require_once $pathsPath;

$paths = new Config\Paths();
require_once $paths->systemDirectory . '/Boot.php';

// Boot the application
$app = new CodeIgniter\CodeIgniter(new Config\Paths());
$app->initialize();

echo "=== Featured News Functionality Test ===\n\n";

try {
    $db = \Config\Database::connect();
    
    // 1. Check if featured column exists
    echo "1. Checking database structure...\n";
    $result = $db->query("SHOW COLUMNS FROM news LIKE 'featured'");
    if ($result->getNumRows() > 0) {
        echo "   ✅ Featured column exists in news table\n";
    } else {
        echo "   ❌ Featured column missing from news table\n";
        exit(1);
    }
    
    // 2. Check current featured news count
    echo "\n2. Checking current featured news...\n";
    $featuredCount = $db->table('news')->where('featured', 1)->countAllResults();
    $totalCount = $db->table('news')->countAllResults();
    echo "   Total news articles: $totalCount\n";
    echo "   Featured news articles: $featuredCount\n";
    
    // 3. Test the NewsModel
    echo "\n3. Testing NewsModel...\n";
    $newsModel = new \App\Models\NewsModel();
    $news = $newsModel->findAll(5); // Get first 5 news articles
    
    if (!empty($news)) {
        echo "   Found " . count($news) . " news articles\n";
        foreach ($news as $item) {
            $featuredStatus = $item['featured'] ? 'Featured' : 'Not Featured';
            echo "   - ID {$item['id']}: {$item['title']} ({$featuredStatus})\n";
        }
    } else {
        echo "   No news articles found\n";
    }
    
    // 4. Test toggle functionality (simulate)
    echo "\n4. Testing toggle functionality...\n";
    if (!empty($news)) {
        $testNews = $news[0];
        $currentFeatured = $testNews['featured'];
        $newFeatured = !$currentFeatured;
        
        echo "   Testing with news ID: {$testNews['id']}\n";
        echo "   Current featured status: " . ($currentFeatured ? 'Yes' : 'No') . "\n";
        echo "   New featured status: " . ($newFeatured ? 'Yes' : 'No') . "\n";
        
        // Update the featured status
        $newsModel->update($testNews['id'], ['featured' => $newFeatured]);
        
        // Verify the update
        $updatedNews = $newsModel->find($testNews['id']);
        if ($updatedNews['featured'] == $newFeatured) {
            echo "   ✅ Toggle functionality works correctly\n";
        } else {
            echo "   ❌ Toggle functionality failed\n";
        }
        
        // Restore original status
        $newsModel->update($testNews['id'], ['featured' => $currentFeatured]);
        echo "   Restored original featured status\n";
    }
    
    echo "\n=== Test completed successfully ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 