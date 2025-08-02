<?php
// Debug file to test CodeIgniter bootstrap
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting debug...\n";

// Test if we can load the Paths configuration
try {
    require_once __DIR__ . '/app/Config/Paths.php';
    $paths = new \Config\Paths();
    echo "Paths loaded successfully\n";
    echo "System directory: " . $paths->systemDirectory . "\n";
    echo "App directory: " . $paths->appDirectory . "\n";
} catch (Exception $e) {
    echo "Error loading Paths: " . $e->getMessage() . "\n";
    exit;
}

// Test if system directory exists
if (!file_exists($paths->systemDirectory)) {
    echo "ERROR: System directory does not exist: " . $paths->systemDirectory . "\n";
    exit;
}

// Test if Boot.php exists
$bootFile = $paths->systemDirectory . '/Boot.php';
if (!file_exists($bootFile)) {
    echo "ERROR: Boot.php does not exist: " . $bootFile . "\n";
    exit;
}

echo "Boot.php exists\n";

// Try to load Boot.php
try {
    require_once $bootFile;
    echo "Boot.php loaded successfully\n";
} catch (Exception $e) {
    echo "Error loading Boot.php: " . $e->getMessage() . "\n";
    exit;
}

// Try to bootstrap
try {
    $result = \CodeIgniter\Boot::bootWeb($paths);
    echo "Bootstrap completed\n";
} catch (Exception $e) {
    echo "Bootstrap error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 