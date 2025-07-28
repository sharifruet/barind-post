#!/bin/bash

# CodeIgniter 4 News Portal - Deployment Script for cPanel
# This script prepares your project for cPanel deployment

echo "🚀 Preparing CodeIgniter 4 News Portal for cPanel deployment..."

# Create deployment directory
DEPLOY_DIR="deploy_cpanel"
if [ -d "$DEPLOY_DIR" ]; then
    rm -rf "$DEPLOY_DIR"
fi
mkdir "$DEPLOY_DIR"

echo "📁 Creating deployment package..."

# Copy necessary files and directories
cp -r app "$DEPLOY_DIR/"
cp -r system "$DEPLOY_DIR/"
cp -r vendor "$DEPLOY_DIR/"
cp -r writable "$DEPLOY_DIR/"
cp -r public "$DEPLOY_DIR/"
cp index.php "$DEPLOY_DIR/"
cp .htaccess "$DEPLOY_DIR/"
cp env.production "$DEPLOY_DIR/.env"
cp dbscript.sql "$DEPLOY_DIR/"
cp DEPLOYMENT.md "$DEPLOY_DIR/"
cp simple_test.php "$DEPLOY_DIR/"
cp debug.php "$DEPLOY_DIR/"
cp error_diagnostic.php "$DEPLOY_DIR/"

# Ensure uploads directory exists and has proper permissions
mkdir -p "$DEPLOY_DIR/public/uploads/news"
chmod 755 "$DEPLOY_DIR/public/uploads"
chmod 755 "$DEPLOY_DIR/public/uploads/news"

# Create a simple deployment checklist
cat > "$DEPLOY_DIR/DEPLOYMENT_CHECKLIST.txt" << 'EOF'
CodeIgniter 4 News Portal - cPanel Deployment Checklist

✅ Files to upload to cPanel:
   - All files in this directory to your public_html folder

✅ Database Setup:
   - Create MySQL database in cPanel
   - Import dbscript.sql via phpMyAdmin
   - Update .env file with your database credentials

✅ Configuration:
   - Update app.baseURL in .env to your domain
   - Generate a secure encryption.key (32 characters)
   - Set file permissions: writable/ (755), public/uploads/ (755)

✅ Security:
   - Ensure .env file is not accessible via web
   - Enable HTTPS in cPanel
   - Set environment to production

✅ Testing:
   - Test homepage loads correctly
   - Test admin login (/admin)
   - Test Bengali text display
   - Test image uploads

Common Issues:
- 500 Error: Check file permissions and .htaccess
- Database Error: Verify credentials in .env
- 404 Error: Ensure mod_rewrite is enabled
- Bengali Text Issues: Check database charset (utf8mb4)

Need help? Check DEPLOYMENT.md for detailed instructions.
EOF

# Create a simple test file
cat > "$DEPLOY_DIR/test.php" << 'EOF'
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
EOF

echo "📦 Creating ZIP archive..."
cd "$DEPLOY_DIR"
zip -r ../cpanel_deployment.zip . -x "*.DS_Store" "*.git*" "*.svn*"
cd ..

echo "✅ Deployment package created successfully!"
echo ""
echo "📋 Next steps:"
echo "1. Upload cpanel_deployment.zip to your cPanel file manager"
echo "2. Extract the ZIP file in your public_html directory"
echo "3. Follow the DEPLOYMENT_CHECKLIST.txt instructions"
echo "4. Test your site with test.php first"
echo ""
echo "📁 Files created:"
echo "   - $DEPLOY_DIR/ (deployment directory)"
echo "   - cpanel_deployment.zip (upload this to cPanel)"
echo "   - DEPLOYMENT.md (detailed deployment guide)"
echo ""
echo "🔧 Don't forget to:"
echo "   - Update database credentials in .env"
echo "   - Set your domain in app.baseURL"
echo "   - Generate a secure encryption key"
echo "   - Set proper file permissions" 