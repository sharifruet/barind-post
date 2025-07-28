<?php
/**
 * Error Diagnostic Script for Barind Post
 * Upload this to your cPanel root directory to diagnose 500 errors
 */

// Prevent direct access in production
if (isset($_GET['debug']) && $_GET['debug'] === 'true') {
    // Continue with diagnostics
} else {
    echo "Add ?debug=true to URL to run diagnostics";
    exit;
}

echo "<h1>🔍 Barind Post Error Diagnostic</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .warning{color:orange;} .section{margin:20px 0;padding:15px;border:1px solid #ddd;border-radius:5px;} pre{background:#f5f5f5;padding:10px;border-radius:3px;overflow-x:auto;}</style>";

// 1. PHP Version and Extensions
echo "<div class='section'>";
echo "<h2>📋 PHP Environment</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Server Software:</strong> " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "</p>";

$required_extensions = ['mysqli', 'mbstring', 'json', 'openssl', 'curl'];
echo "<p><strong>Required Extensions:</strong></p><ul>";
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<li class='success'>✅ $ext - Loaded</li>";
    } else {
        echo "<li class='error'>❌ $ext - Missing</li>";
    }
}
echo "</ul></div>";

// 2. File Structure Check
echo "<div class='section'>";
echo "<h2>📁 File Structure</h2>";
$required_files = [
    'index.php',
    '.htaccess',
    '.env',
    'app/Config/App.php',
    'app/Config/Database.php',
    'app/Config/Paths.php',
    'system/bootstrap.php',
    'vendor/autoload.php'
];

echo "<p><strong>Required Files:</strong></p><ul>";
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<li class='success'>✅ $file - Exists</li>";
    } else {
        echo "<li class='error'>❌ $file - Missing</li>";
    }
}
echo "</ul></div>";

// 3. File Permissions
echo "<div class='section'>";
echo "<h2>🔐 File Permissions</h2>";
$permission_checks = [
    'index.php' => '644',
    '.htaccess' => '644',
    '.env' => '644',
    'writable/' => '755',
    'writable/logs/' => '755',
    'writable/cache/' => '755',
    'writable/uploads/' => '755'
];

echo "<p><strong>File Permissions:</strong></p><ul>";
foreach ($permission_checks as $file => $expected) {
    if (file_exists($file)) {
        $perms = substr(sprintf('%o', fileperms($file)), -3);
        if ($perms === $expected) {
            echo "<li class='success'>✅ $file - $perms (correct)</li>";
        } else {
            echo "<li class='warning'>⚠️ $file - $perms (expected: $expected)</li>";
        }
    } else {
        echo "<li class='error'>❌ $file - File not found</li>";
    }
}
echo "</ul></div>";

// 4. .env Configuration
echo "<div class='section'>";
echo "<h2>⚙️ Environment Configuration</h2>";
if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    $env_lines = explode("\n", $env_content);
    
    $required_env_vars = [
        'app.baseURL',
        'app.indexPage',
        'database.default.hostname',
        'database.default.database',
        'database.default.username',
        'database.default.password',
        'encryption.key'
    ];
    
    echo "<p><strong>Required Environment Variables:</strong></p><ul>";
    foreach ($required_env_vars as $var) {
        $found = false;
        foreach ($env_lines as $line) {
            if (strpos($line, $var) === 0) {
                $found = true;
                $value = trim(substr($line, strpos($line, '=') + 1));
                if (!empty($value) && $value !== 'your_value_here') {
                    echo "<li class='success'>✅ $var - Set</li>";
                } else {
                    echo "<li class='warning'>⚠️ $var - Not configured</li>";
                }
                break;
            }
        }
        if (!$found) {
            echo "<li class='error'>❌ $var - Missing</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p class='error'>❌ .env file not found</p>";
}
echo "</div>";

// 5. Database Connection Test
echo "<div class='section'>";
echo "<h2>🗄️ Database Connection</h2>";
if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    $env_lines = explode("\n", $env_content);
    
    $db_config = [];
    foreach ($env_lines as $line) {
        if (strpos($line, 'database.default.') === 0) {
            $parts = explode('=', $line, 2);
            if (count($parts) == 2) {
                $key = trim($parts[0]);
                $value = trim($parts[1]);
                $db_config[$key] = $value;
            }
        }
    }
    
    if (isset($db_config['database.default.hostname']) && 
        isset($db_config['database.default.database']) && 
        isset($db_config['database.default.username']) && 
        isset($db_config['database.default.password'])) {
        
        try {
            $mysqli = new mysqli(
                $db_config['database.default.hostname'],
                $db_config['database.default.username'],
                $db_config['database.default.password'],
                $db_config['database.default.database']
            );
            
            if ($mysqli->connect_error) {
                echo "<p class='error'>❌ Database connection failed: " . $mysqli->connect_error . "</p>";
            } else {
                echo "<p class='success'>✅ Database connection successful</p>";
                echo "<p><strong>Server Info:</strong> " . $mysqli->server_info . "</p>";
                
                // Check if tables exist
                $tables = ['users', 'roles', 'news', 'categories', 'tags'];
                echo "<p><strong>Required Tables:</strong></p><ul>";
                foreach ($tables as $table) {
                    $result = $mysqli->query("SHOW TABLES LIKE '$table'");
                    if ($result && $result->num_rows > 0) {
                        echo "<li class='success'>✅ $table - Exists</li>";
                    } else {
                        echo "<li class='error'>❌ $table - Missing</li>";
                    }
                }
                echo "</ul>";
                
                $mysqli->close();
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Database connection error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p class='error'>❌ Database configuration incomplete</p>";
    }
} else {
    echo "<p class='error'>❌ Cannot test database - .env file not found</p>";
}
echo "</div>";

// 6. Error Log Check
echo "<div class='section'>";
echo "<h2>📝 Error Logs</h2>";
$log_files = [
    'writable/logs/log-*.php',
    'error_log',
    'php_errors.log'
];

foreach ($log_files as $pattern) {
    $files = glob($pattern);
    if (!empty($files)) {
        echo "<p><strong>Found log files:</strong></p>";
        foreach ($files as $file) {
            echo "<p><strong>$file:</strong></p>";
            $content = file_get_contents($file);
            if (strlen($content) > 1000) {
                $content = substr($content, -1000); // Last 1000 chars
            }
            echo "<pre>" . htmlspecialchars($content) . "</pre>";
        }
    }
}
echo "</div>";

// 7. CodeIgniter Bootstrap Test
echo "<div class='section'>";
echo "<h2>🚀 CodeIgniter Bootstrap Test</h2>";
try {
    // Test if we can load CodeIgniter core
    if (file_exists('system/bootstrap.php')) {
        echo "<p class='success'>✅ system/bootstrap.php exists</p>";
        
        // Test autoloader
        if (file_exists('vendor/autoload.php')) {
            echo "<p class='success'>✅ vendor/autoload.php exists</p>";
        } else {
            echo "<p class='error'>❌ vendor/autoload.php missing - Run composer install</p>";
        }
        
        // Test if we can include the bootstrap
        ob_start();
        include 'system/bootstrap.php';
        $output = ob_get_clean();
        
        if (empty($output)) {
            echo "<p class='success'>✅ Bootstrap loaded without errors</p>";
        } else {
            echo "<p class='warning'>⚠️ Bootstrap output: " . htmlspecialchars($output) . "</p>";
        }
    } else {
        echo "<p class='error'>❌ system/bootstrap.php missing</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Bootstrap error: " . $e->getMessage() . "</p>";
}
echo "</div>";

// 8. .htaccess Test
echo "<div class='section'>";
echo "<h2>🔧 .htaccess Configuration</h2>";
if (file_exists('.htaccess')) {
    $htaccess_content = file_get_contents('.htaccess');
    echo "<p><strong>.htaccess content:</strong></p>";
    echo "<pre>" . htmlspecialchars($htaccess_content) . "</pre>";
    
    // Check for common issues
    if (strpos($htaccess_content, 'RewriteEngine On') !== false) {
        echo "<p class='success'>✅ RewriteEngine enabled</p>";
    } else {
        echo "<p class='error'>❌ RewriteEngine not found</p>";
    }
    
    if (strpos($htaccess_content, 'RewriteRule') !== false) {
        echo "<p class='success'>✅ RewriteRule found</p>";
    } else {
        echo "<p class='error'>❌ RewriteRule not found</p>";
    }
} else {
    echo "<p class='error'>❌ .htaccess file not found</p>";
}
echo "</div>";

// 9. Quick Fix Suggestions
echo "<div class='section'>";
echo "<h2>🔧 Quick Fix Suggestions</h2>";
echo "<ol>";
echo "<li><strong>If .env is missing:</strong> Copy env.production to .env and configure it</li>";
echo "<li><strong>If vendor/autoload.php is missing:</strong> Run 'composer install'</li>";
echo "<li><strong>If database connection fails:</strong> Check database credentials in .env</li>";
echo "<li><strong>If permissions are wrong:</strong> Set writable/ to 755 and .env to 644</li>";
echo "<li><strong>If .htaccess is missing:</strong> Copy the provided .htaccess file</li>";
echo "<li><strong>If tables are missing:</strong> Import dbscript.sql to your database</li>";
echo "</ol>";
echo "</div>";

echo "<div class='section'>";
echo "<h2>📞 Next Steps</h2>";
echo "<p>If you're still getting 500 errors after fixing the issues above:</p>";
echo "<ol>";
echo "<li>Check your cPanel error logs</li>";
echo "<li>Contact your hosting provider</li>";
echo "<li>Try uploading the files again</li>";
echo "<li>Make sure all files are in the correct directory structure</li>";
echo "</ol>";
echo "</div>";

echo "<p><em>Diagnostic completed at: " . date('Y-m-d H:i:s') . "</em></p>";
?> 