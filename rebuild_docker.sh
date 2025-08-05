#!/bin/bash

echo "=== Rebuilding Docker Container with GD Library Support ==="

echo "1. Stopping existing containers..."
docker-compose down

echo "2. Removing old images..."
docker-compose rm -f

echo "3. Building new image with GD library..."
docker-compose build --no-cache

echo "4. Starting containers..."
docker-compose up -d

echo "5. Waiting for containers to be ready..."
sleep 10

echo "6. Testing GD library installation..."
docker-compose exec app php -m | grep gd

echo "7. Testing photo card functionality..."
docker-compose exec app php -r "
if (extension_loaded('gd')) {
    echo '✅ GD library is installed and working\n';
    
    // Test basic image creation
    \$image = imagecreatetruecolor(100, 100);
    if (\$image) {
        echo '✅ Can create image resources\n';
        
        \$white = imagecolorallocate(\$image, 255, 255, 255);
        if (\$white !== false) {
            echo '✅ Can allocate colors\n';
        }
        
        imagedestroy(\$image);
        echo '✅ Can destroy image resources\n';
    }
} else {
    echo '❌ GD library is not installed\n';
}
"

echo "8. Testing font installation..."
docker-compose exec app php test_fonts_docker.php

echo "=== Rebuild Complete ==="
echo "You can now test the photo card generation feature."
echo "Visit: http://localhost/admin/photo-card-generator" 