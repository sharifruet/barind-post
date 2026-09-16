# CodeIgniter 4 News Portal - cPanel Deployment Guide

## Prerequisites

1. **cPanel Access**: You need access to your cPanel hosting account
2. **MySQL Database**: A MySQL database (version 5.7+ or 8.0+ recommended)
3. **PHP Version**: PHP 8.1 or higher
4. **Required PHP Extensions**:
   - mysqli
   - mbstring
   - json
   - curl
   - gd (for image processing)
   - zip (for file uploads)

## Step 1: Prepare Your Files

### Option A: Upload via FTP/SFTP
1. Create a ZIP file of your project (excluding unnecessary files)
2. Upload to your cPanel file manager or via FTP

### Option B: Git Deployment (Recommended)
1. Clone your repository to your local machine
2. Upload via Git or use cPanel's Git Version Control

## Step 2: File Structure for cPanel

Your files should be organized as follows in your cPanel:

```
public_html/ (or your domain folder)
├── index.php (CodeIgniter's front controller)
├── .htaccess (URL rewriting)
├── logo.png
├── robots.txt
├── favicon.ico
└── app/ (CodeIgniter application folder)
    ├── Config/
    ├── Controllers/
    ├── Models/
    ├── Views/
    └── ...
├── system/ (CodeIgniter system files)
├── writable/ (writable directory)
└── vendor/ (Composer dependencies)
```

## Step 3: Database Setup

1. **Create Database in cPanel**:
   - Go to cPanel → MySQL Databases
   - Create a new database
   - Create a database user
   - Assign user to database with all privileges

2. **Import Database Schema**:
   - Go to phpMyAdmin in cPanel
   - Select your database
   - Import the `dbscript.sql` file

## Step 4: Environment Configuration

1. **Create .env file** in your root directory:

```env
#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------
CI_ENVIRONMENT = production

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------
app.baseURL = 'https://yourdomain.com'
app.forceGlobalSecureRequests = true
app.CSPEnabled = true

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------
database.default.hostname = localhost
database.default.database = your_database_name
database.default.username = your_database_user
database.default.password = your_database_password
database.default.DBDriver = MySQLi
database.default.DBPrefix =
database.default.port = 3306
database.default.charset = utf8mb4
database.default.DBCollat = utf8mb4_unicode_ci

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------
encryption.key = your_32_character_random_string_here

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------
session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
session.savePath = null

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------
logger.threshold = 1
```

## Step 5: File Permissions

Set the following permissions:
- `writable/` directory: 755
- `writable/cache/`: 755
- `writable/logs/`: 755
- `writable/session/`: 755
- `writable/uploads/`: 755

## Step 6: .htaccess Configuration

Create/update `.htaccess` in your root directory:

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Prevent access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>
```

## Step 7: Update Paths Configuration

Edit `app/Config/Paths.php` to ensure correct paths:

```php
<?php

namespace Config;

class Paths
{
    public $systemDirectory = __DIR__ . '/../../system';
    public $appDirectory = __DIR__ . '/..';
    public $writableDirectory = __DIR__ . '/../../writable';
    public $testsDirectory = __DIR__ . '/../../tests';
    public $viewDirectory = __DIR__ . '/../Views';
}
```

## Step 8: Composer Dependencies

If your hosting supports Composer:
1. SSH into your hosting (if available)
2. Navigate to your project directory
3. Run: `composer install --no-dev --optimize-autoloader`

If Composer is not available:
1. Upload the `vendor/` folder from your local development
2. Ensure all dependencies are included

## Step 9: Final Configuration

1. **Update Base URL**: Ensure `app.baseURL` in `.env` matches your domain
2. **Generate Encryption Key**: Use a secure random string for `encryption.key`
3. **Test the Application**: Visit your domain to ensure everything works

## Step 10: Security Checklist

- [ ] Environment is set to `production`
- [ ] `.env` file is not accessible via web
- [ ] Database credentials are secure
- [ ] File permissions are correct
- [ ] HTTPS is enabled
- [ ] Error reporting is disabled in production
- [ ] Logging is configured appropriately

## Troubleshooting

### Common Issues:

1. **500 Internal Server Error**:
   - Check file permissions
   - Verify .htaccess syntax
   - Check error logs in cPanel

2. **Database Connection Error**:
   - Verify database credentials
   - Ensure database exists
   - Check if database user has proper privileges

3. **Page Not Found (404)**:
   - Verify .htaccess is working
   - Check if mod_rewrite is enabled
   - Ensure routes are configured correctly

4. **Bengali Text Issues**:
   - Ensure database charset is utf8mb4
   - Check PHP mbstring extension is enabled
   - Verify Content-Type headers are set correctly

### cPanel Error Logs:
- Check error logs in cPanel → Error Logs
- Check PHP error logs in cPanel → PHP Selector → Error Logs

## Performance Optimization

1. **Enable Caching**:
   - Configure file caching in `app/Config/Cache.php`
   - Enable database query caching

2. **Optimize Images**:
   - Compress uploaded images
   - Use appropriate image formats

3. **CDN Integration**:
   - Consider using a CDN for static assets
   - Configure asset caching headers

## Backup Strategy

1. **Regular Database Backups**:
   - Use cPanel's backup feature
   - Schedule automated backups

2. **File Backups**:
   - Backup your application files regularly
   - Keep multiple versions

## Support

If you encounter issues:
1. Check cPanel error logs
2. Verify all requirements are met
3. Test with a simple PHP file first
4. Contact your hosting provider for server-specific issues 