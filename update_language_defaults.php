<?php
/**
 * Script to update existing news articles to have Bengali as default language
 * Run this script once to update existing articles that don't have a language set
 */

// Load CodeIgniter
require_once 'app/Config/Paths.php';
$paths = new Config\Paths();
require_once $paths->systemDirectory . '/Boot.php';

// Initialize the application
$app = Config\Services::codeigniter();
$app->initialize();

// Connect to database
$db = \Config\Database::connect();

echo "Updating news articles to set Bangla as default language...\n";

// Update news articles that don't have a language set or have 'en' as language
$result = $db->table('news')
    ->where('language IS NULL OR language = "en"')
    ->update(['language' => 'bn']);

echo "Updated {$result} news articles to Bengali language.\n";

// Show current language distribution
$languages = $db->table('news')
    ->select('language, COUNT(*) as count')
    ->groupBy('language')
    ->get()
    ->getResultArray();

echo "\nCurrent language distribution:\n";
foreach ($languages as $lang) {
    $langName = $lang['language'] === 'bn' ? 'Bangla' : 'English';
    echo "- {$langName}: {$lang['count']} articles\n";
}

echo "\nLanguage update completed successfully!\n"; 