<?php
// Simple test file to verify PHP and server configuration
echo "<h1>PHP Test</h1>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Server: " . $_SERVER['SERVER_SOFTWARE'] . "</p>";

// Check required extensions
$required_extensions = ['mysqli', 'mbstring', 'json', 'curl', 'gd'];
echo "<h2>Required Extensions:</h2>";
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "<p>$status $ext</p>";
}

// Test database connection (if .env exists)
if (file_exists('.env')) {
    echo "<h2>Environment File:</h2>";
    echo "<p>✅ .env file found</p>";
} else {
    echo "<h2>Environment File:</h2>";
    echo "<p>❌ .env file not found - please create it</p>";
}

echo "<h2>Directory Permissions:</h2>";
$writable_dirs = ['writable', 'writable/cache', 'writable/logs', 'writable/session', 'writable/uploads'];
foreach ($writable_dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? "✅" : "❌";
        echo "<p>$writable $dir</p>";
    } else {
        echo "<p>❌ $dir (directory not found)</p>";
    }
}
?>
