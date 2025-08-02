<?php
// Simulate a web request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['SERVER_NAME'] = 'localhost';
$_SERVER['SERVER_PORT'] = '80';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PATH_INFO'] = '/';

// Set up the environment
define('FCPATH', __DIR__ . '/public/');
chdir(FCPATH);

// Load autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Load the paths
require_once __DIR__ . '/app/Config/Paths.php';
$paths = new \Config\Paths();

// Bootstrap CodeIgniter
try {
    $result = \CodeIgniter\Boot::bootWeb($paths);
    echo "Web bootstrap completed successfully\n";
} catch (Exception $e) {
    echo "Web bootstrap error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 