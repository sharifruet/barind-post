<?php
// Test routes loading
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set environment to development
putenv('CI_ENVIRONMENT=development');
$_ENV['CI_ENVIRONMENT'] = 'development';

// Set up the environment
define('FCPATH', __DIR__ . '/public/');
chdir(FCPATH);

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load the paths
require_once __DIR__ . '/app/Config/Paths.php';
$paths = new \Config\Paths();

// Load the system
require_once $paths->systemDirectory . '/Boot.php';

// Try to load routes manually
try {
    require_once __DIR__ . '/app/Config/Routes.php';
    echo "Routes.php loaded successfully\n";
    
    // Check if routes are defined
    if (isset($routes)) {
        echo "Routes object exists\n";
        // Try to get routes
        $routeCollection = $routes;
        echo "Route collection type: " . get_class($routeCollection) . "\n";
    } else {
        echo "Routes object not found\n";
    }
} catch (Exception $e) {
    echo "Error loading routes: " . $e->getMessage() . "\n";
}

// Simulate web request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PATH_INFO'] = '/';

echo "Environment: " . (getenv('CI_ENVIRONMENT') ?: 'not set') . "\n";

// Bootstrap CodeIgniter
try {
    $result = \CodeIgniter\Boot::bootWeb($paths);
    echo "Web bootstrap completed successfully\n";
} catch (Exception $e) {
    echo "Web bootstrap error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 