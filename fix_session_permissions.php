<?php
/**
 * Session Directory Fix Script
 * Run this script to ensure session directory exists and has proper permissions
 */

// Define the session directory path
$sessionDir = '/home/wahidiya/barindpost.com/writable/session';

echo "Fixing session directory permissions...\n";

// Create directory if it doesn't exist
if (!is_dir($sessionDir)) {
    echo "Creating session directory: $sessionDir\n";
    if (mkdir($sessionDir, 0755, true)) {
        echo "✓ Session directory created successfully\n";
    } else {
        echo "✗ Failed to create session directory\n";
        exit(1);
    }
} else {
    echo "✓ Session directory already exists\n";
}

// Set proper permissions
if (chmod($sessionDir, 0755)) {
    echo "✓ Session directory permissions set to 755\n";
} else {
    echo "✗ Failed to set session directory permissions\n";
}

// Check if directory is writable
if (is_writable($sessionDir)) {
    echo "✓ Session directory is writable\n";
} else {
    echo "✗ Session directory is not writable\n";
    echo "Current permissions: " . substr(sprintf('%o', fileperms($sessionDir)), -4) . "\n";
}

// Create a test file to verify write permissions
$testFile = $sessionDir . '/test_write.tmp';
if (file_put_contents($testFile, 'test') !== false) {
    echo "✓ Write test successful\n";
    unlink($testFile); // Clean up test file
} else {
    echo "✗ Write test failed\n";
}

echo "\nSession directory setup complete!\n";
echo "If you still have issues, try running:\n";
echo "chmod -R 755 /home/wahidiya/barindpost.com/writable/\n";
echo "chown -R www-data:www-data /home/wahidiya/barindpost.com/writable/\n";
?> 