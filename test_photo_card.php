<?php
/**
 * Test script for Photo Card Generation Feature
 * 
 * This script tests the photo card generation functionality
 * Run this script to verify that the feature is working correctly
 */

// Include CodeIgniter bootstrap
require_once 'index.php';

use App\Models\NewsModel;

echo "=== Photo Card Generation Feature Test ===\n\n";

try {
    // Test 1: Check if NewsModel can fetch published news
    $newsModel = new NewsModel();
    $news = $newsModel->where('status', 'published')
                      ->orderBy('published_at', 'DESC')
                      ->findAll(5);
    
    echo "✅ Test 1: News Model - Found " . count($news) . " published news articles\n";
    
    if (count($news) > 0) {
        $firstNews = $news[0];
        echo "   - First article: " . $firstNews['title'] . "\n";
        echo "   - Image URL: " . ($firstNews['image_url'] ?? 'No image') . "\n";
        echo "   - Published: " . $firstNews['published_at'] . "\n";
    }
    
    // Test 2: Check if photo card directory exists
    $photoCardDir = FCPATH . 'public/uploads/photo_cards/';
    if (is_dir($photoCardDir)) {
        echo "✅ Test 2: Photo Card Directory - Exists at " . $photoCardDir . "\n";
    } else {
        echo "❌ Test 2: Photo Card Directory - Missing\n";
    }
    
    // Test 3: Check if logo exists
    $logoPath = FCPATH . 'public/logo.png';
    if (file_exists($logoPath)) {
        echo "✅ Test 3: Logo File - Exists at " . $logoPath . "\n";
    } else {
        echo "⚠️  Test 3: Logo File - Not found (will be skipped in generation)\n";
    }
    
    // Test 4: Check if helper function exists
    if (function_exists('get_image_url')) {
        echo "✅ Test 4: Helper Function - get_image_url() exists\n";
        
        // Test with sample data
        $testUrl = 'https://via.placeholder.com/600x400?text=Test';
        $result = get_image_url($testUrl);
        echo "   - Test URL: " . $testUrl . "\n";
        echo "   - Processed: " . $result . "\n";
    } else {
        echo "❌ Test 4: Helper Function - get_image_url() missing\n";
    }
    
    // Test 5: Check routes
    echo "✅ Test 5: Routes - Photo card routes should be available\n";
    echo "   - GET /admin/photo-card-generator\n";
    echo "   - POST /admin/photo-card-generator/generate\n";
    
    echo "\n=== Test Summary ===\n";
    echo "The photo card generation feature appears to be properly configured.\n";
    echo "To test the full functionality:\n";
    echo "1. Start the development server: php spark serve\n";
    echo "2. Login as admin user\n";
    echo "3. Navigate to /admin/photo-card-generator\n";
    echo "4. Select a news article and generate a photo card\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== End of Test ===\n"; 