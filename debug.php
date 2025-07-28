<?php
// Diagnostic script for CodeIgniter 4 deployment issues
echo "<h1>🔍 CodeIgniter 4 Deployment Diagnostics</h1>";

// 1. Basic PHP Information
echo "<h2>📋 PHP Information</h2>";
echo "<p><strong>PHP Version:</strong> " . PHP_VERSION . "</p>";
echo "<p><strong>Server Software:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] . "</p>";
echo "<p><strong>Current Directory:</strong> " . getcwd() . "</p>";

// 2. Required Extensions
echo "<h2>🔧 Required PHP Extensions</h2>";
$required_extensions = ['mysqli', 'mbstring', 'json', 'curl', 'gd', 'zip'];
foreach ($required_extensions as $ext) {
    $status = extension_loaded($ext) ? "✅" : "❌";
    echo "<p>$status $ext</p>";
}

// 3. File Structure Check
echo "<h2>📁 File Structure Check</h2>";
$required_files = [
    'index.php',
    '.htaccess',
    'app/Config/Paths.php',
    'app/Config/App.php',
    'app/Config/Database.php',
    'system/Boot.php',
    'vendor/autoload.php'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p>✅ $file</p>";
    } else {
        echo "<p>❌ $file (missing)</p>";
    }
}

// 4. Directory Permissions
echo "<h2>🔐 Directory Permissions</h2>";
$writable_dirs = ['writable', 'writable/cache', 'writable/logs', 'writable/session', 'writable/uploads'];
foreach ($writable_dirs as $dir) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? "✅" : "❌";
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        echo "<p>$writable $dir (permissions: $perms)</p>";
    } else {
        echo "<p>❌ $dir (directory not found)</p>";
    }
}

// 5. Environment File Check
echo "<h2>⚙️ Environment Configuration</h2>";
if (file_exists('.env')) {
    echo "<p>✅ .env file found</p>";
    $env_content = file_get_contents('.env');
    if (strpos($env_content, 'CI_ENVIRONMENT = production') !== false) {
        echo "<p>✅ Environment set to production</p>";
    } else {
        echo "<p>⚠️ Environment not set to production</p>";
    }
    if (strpos($env_content, 'app.baseURL') !== false) {
        echo "<p>✅ Base URL configured</p>";
    } else {
        echo "<p>❌ Base URL not configured</p>";
    }
} else {
    echo "<p>❌ .env file not found</p>";
}

// 6. Database Connection Test
echo "<h2>🗄️ Database Connection Test</h2>";
if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    if (strpos($env_content, 'database.default.database') !== false) {
        echo "<p>✅ Database configuration found in .env</p>";
        
        // Try to load CodeIgniter and test database
        try {
            // Include CodeIgniter bootstrap
            if (file_exists('vendor/autoload.php')) {
                require_once 'vendor/autoload.php';
                
                // Load environment
                $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
                $dotenv->load();
                
                // Test database connection
                $hostname = $_ENV['database.default.hostname'] ?? 'localhost';
                $database = $_ENV['database.default.database'] ?? '';
                $username = $_ENV['database.default.username'] ?? '';
                $password = $_ENV['database.default.password'] ?? '';
                
                if ($database && $username) {
                    $mysqli = new mysqli($hostname, $username, $password, $database);
                    if ($mysqli->connect_error) {
                        echo "<p>❌ Database connection failed: " . $mysqli->connect_error . "</p>";
                    } else {
                        echo "<p>✅ Database connection successful</p>";
                        $mysqli->close();
                    }
                } else {
                    echo "<p>⚠️ Database credentials not found in .env</p>";
                }
            } else {
                echo "<p>❌ Vendor autoload not found</p>";
            }
        } catch (Exception $e) {
            echo "<p>❌ Error testing database: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p>❌ Database configuration not found in .env</p>";
    }
} else {
    echo "<p>❌ Cannot test database without .env file</p>";
}

// 7. URL Rewriting Test
echo "<h2>🔗 URL Rewriting Test</h2>";
if (file_exists('.htaccess')) {
    echo "<p>✅ .htaccess file found</p>";
    $htaccess_content = file_get_contents('.htaccess');
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        echo "<p>✅ RewriteEngine enabled</p>";
    } else {
        echo "<p>❌ RewriteEngine not found</p>";
    }
} else {
    echo "<p>❌ .htaccess file not found</p>";
}

// 8. Common Issues
echo "<h2>🚨 Common Issues & Solutions</h2>";
echo "<ul>";
echo "<li><strong>500 Error:</strong> Check file permissions and .htaccess syntax</li>";
echo "<li><strong>404 Error:</strong> Ensure mod_rewrite is enabled in cPanel</li>";
echo "<li><strong>Database Error:</strong> Verify credentials in .env file</li>";
echo "<li><strong>Blank Page:</strong> Check PHP error logs in cPanel</li>";
echo "<li><strong>Path Issues:</strong> Verify app/Config/Paths.php has correct paths</li>";
echo "</ul>";

echo "<h2>📞 Next Steps</h2>";
echo "<ol>";
echo "<li>Check cPanel error logs (Error Logs section)</li>";
echo "<li>Verify PHP version is 8.1+ in PHP Selector</li>";
echo "<li>Enable mod_rewrite in cPanel if not already enabled</li>";
echo "<li>Set proper file permissions (755 for directories, 644 for files)</li>";
echo "<li>Test with a simple PHP file first</li>";
echo "</ol>";

echo "<hr>";
echo "<p><em>If you're still having issues, please share the specific error message you're seeing.</em></p>";
?> 