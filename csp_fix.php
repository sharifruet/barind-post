<?php
// Temporary CSP Disable Script
// Run this to completely disable CSP for testing

echo "<h1>🔧 CSP Configuration Fix</h1>";

// Check if .env file exists
if (file_exists('.env')) {
    $env_content = file_get_contents('.env');
    
    // Replace CSP settings
    $env_content = preg_replace('/app\.CSPEnabled\s*=\s*true/', 'app.CSPEnabled = false', $env_content);
    
    // If CSPEnabled line doesn't exist, add it
    if (strpos($env_content, 'app.CSPEnabled') === false) {
        $env_content .= "\napp.CSPEnabled = false\n";
    }
    
    // Write back to .env
    if (file_put_contents('.env', $env_content)) {
        echo "<p>✅ CSP disabled in .env file</p>";
    } else {
        echo "<p>❌ Could not write to .env file</p>";
    }
} else {
    echo "<p>❌ .env file not found</p>";
}

// Create a simple .htaccess override
$htaccess_content = "RewriteEngine On\n";
$htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
$htaccess_content .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
$htaccess_content .= "RewriteRule ^(.*)$ index.php/\$1 [L]\n\n";
$htaccess_content .= "# Disable CSP headers temporarily\n";
$htaccess_content .= "<IfModule mod_headers.c>\n";
$htaccess_content .= "    Header unset Content-Security-Policy\n";
$htaccess_content .= "    Header unset X-Content-Security-Policy\n";
$htaccess_content .= "</IfModule>\n";

if (file_put_contents('.htaccess', $htaccess_content)) {
    echo "<p>✅ .htaccess updated to disable CSP headers</p>";
} else {
    echo "<p>❌ Could not write to .htaccess file</p>";
}

echo "<hr>";
echo "<p><strong>Next steps:</strong></p>";
echo "<ol>";
echo "<li>Clear your browser cache</li>";
echo "<li>Test your site again</li>";
echo "<li>If it works, you can re-enable CSP with more permissive settings later</li>";
echo "</ol>";

echo "<p><a href='../'>← Back to your site</a></p>";
?> 