<?php
// Simple test to verify PHP is working
echo "<h1>✅ PHP is Working!</h1>";
echo "<p>If you can see this, PHP is functioning correctly.</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";

// Test if we can read files
if (file_exists('index.php')) {
    echo "<p>✅ index.php exists</p>";
} else {
    echo "<p>❌ index.php not found</p>";
}

if (file_exists('.env')) {
    echo "<p>✅ .env file exists</p>";
} else {
    echo "<p>❌ .env file not found</p>";
}

echo "<hr>";
echo "<p><a href='debug.php'>🔍 Run Full Diagnostics</a></p>";
?> 