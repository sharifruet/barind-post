<?php

namespace App\Libraries;

/**
 * Headline card renderer (GD): 1200×630-style social image with the site logo,
 * the article photo when there is one, the headline and the date.
 *
 * Moved verbatim out of the admin Photo Card generator so the same renderer can
 * produce the Open Graph image for articles that have no photo
 * (PublicSite::ogImage → /og/{id}.png). Templates: 'default', 'header_footer'.
 */
class PhotoCard
{
    /**
     * @return string PNG bytes
     */
    public function render(array $news, string $template = 'default'): string
    {
        return $this->createPhotoCard($news, $template);
    }

    private function createPhotoCard($news, $template = 'default')
    {
        // Check if GD library is available
        if (!extension_loaded('gd')) {
            throw new Exception('GD library is not available');
        }
        
        // Set up image dimensions
        $width = 1200;
        $height = 630;
        
        // Calculate height for header_footer template
        if ($template === 'header_footer') {
            $imageHeight = $height; // Image takes full height
            $titleHeight = max(200, $height * 0.2); // Minimum 200px or 20% of height
            $footerHeight = max(80, $height * 0.1); // Minimum 80px or 10% of height
            
            // Adjust canvas height to accommodate full image + title + footer
            $totalHeight = $imageHeight + $titleHeight + $footerHeight;
            $image = imagecreatetruecolor($width, $totalHeight);
        } else {
            $image = imagecreatetruecolor($width, $height);
        }
        
        if ($image === false) {
            throw new Exception('Failed to create image resource');
        }
        
        // Set colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image, 220, 53, 69);
        $darkGray = imagecolorallocate($image, 52, 58, 64);
        $lightGray = imagecolorallocate($image, 108, 117, 125);
        
        // Fill background
        imagefill($image, 0, 0, $white);
        
        // Load and add logo
        $logoPath = FCPATH . 'public/logo.png';
        if (file_exists($logoPath)) {
            $logo = imagecreatefrompng($logoPath);
            if ($logo === false) {
                // Skip logo if it can't be loaded
                $logo = null;
            } else {
                $logoWidth = imagesx($logo);
                $logoHeight = imagesy($logo);
                
                // Scale logo based on template
                if ($template === 'header_footer') {
                    // For header_footer template, logo goes in title section
                    $imageHeight = $height * 0.6; // 60% of height for image
                    $titleHeight = $height * 0.3; // 30% for title (3x footer height)
                    $footerHeight = $height * 0.1; // 10% for footer
                    
                    $logoNewWidth = 80; // Fixed logo size
                    $logoNewHeight = ($logoHeight / $logoWidth) * $logoNewWidth;
                    
                    $scaledLogo = imagecreatetruecolor($logoNewWidth, $logoNewHeight);
                    imagealphablending($scaledLogo, false);
                    imagesavealpha($scaledLogo, true);
                    imagecopyresampled($scaledLogo, $logo, 0, 0, 0, 0, $logoNewWidth, $logoNewHeight, $logoWidth, $logoHeight);
                    
                    // Position logo in title section, at the top
                    $logoX = ($width - $logoNewWidth) / 2; // Center horizontally
                    $logoY = $imageHeight + 20; // Just after image, at top of title section
                    imagecopy($image, $scaledLogo, $logoX, $logoY, 0, 0, $logoNewWidth, $logoNewHeight);
                } else {
                    // For other templates, use original positioning
                    $logoNewWidth = 100;
                    $logoNewHeight = ($logoHeight / $logoWidth) * $logoNewWidth;
                    
                    $scaledLogo = imagecreatetruecolor($logoNewWidth, $logoNewHeight);
                    imagealphablending($scaledLogo, false);
                    imagesavealpha($scaledLogo, true);
                    imagecopyresampled($scaledLogo, $logo, 0, 0, 0, 0, $logoNewWidth, $logoNewHeight, $logoWidth, $logoHeight);
                    
                    // Position logo at top-right corner
                    imagecopy($image, $scaledLogo, $width - $logoNewWidth - 50, 30, 0, 0, $logoNewWidth, $logoNewHeight);
                }
                
                imagedestroy($logo);
                imagedestroy($scaledLogo);
            }
        }
        
        // Load and add news image if exists (positioned at top with larger size)
        if (!empty($news['image_url'])) {
            $imageUrl = $news['image_url'];
            
            // Debug: Log the image URL
            error_log("Photo Card Debug: Image URL = " . $imageUrl);
            
            // Handle both local files and external URLs
            if (strpos($imageUrl, 'http') === 0) {
                // External URL - try to download
                $imageData = @file_get_contents($imageUrl);
                if ($imageData === false) {
                    // Skip if external image can't be loaded
                    error_log("Photo Card Debug: Failed to load external image: " . $imageUrl);
                    $imageData = null;
                } else {
                    error_log("Photo Card Debug: External image loaded successfully");
                }
            } else {
                // Local file - try multiple possible paths
                $possiblePaths = [
                    FCPATH . 'public/uploads/news/' . basename($imageUrl),
                    FCPATH . 'public/writable/uploads/news/' . basename($imageUrl),
                    FCPATH . 'writable/uploads/news/' . basename($imageUrl)
                ];
                
                $imageData = null;
                foreach ($possiblePaths as $newsImagePath) {
                    error_log("Photo Card Debug: Trying path = " . $newsImagePath);
                    if (file_exists($newsImagePath)) {
                        $imageData = file_get_contents($newsImagePath);
                        error_log("Photo Card Debug: Local image loaded successfully from: " . $newsImagePath);
                        break;
                    }
                }
                
                if ($imageData === null) {
                    error_log("Photo Card Debug: Local image file not found in any path");
                }
            }
            
            if ($imageData !== null && $imageData !== false) {
                error_log("Photo Card Debug: Image data loaded, attempting to create image resource");
                $newsImage = imagecreatefromstring($imageData);
                if ($newsImage === false) {
                    // Skip if image can't be created
                    error_log("Photo Card Debug: Failed to create image resource from string");
                } else {
                    $newsImageWidth = imagesx($newsImage);
                    $newsImageHeight = imagesy($newsImage);
                    error_log("Photo Card Debug: Image created successfully - Width: $newsImageWidth, Height: $newsImageHeight");
                    
                    // Scale news image based on template
                    if ($template === 'header_footer') {
                        // For header_footer template, use exact image dimensions
                        // Use the exact image dimensions - full height
                        $newsImageNewWidth = $newsImageWidth;
                        $newsImageNewHeight = $newsImageHeight;
                        $newsImageX = ($width - $newsImageNewWidth) / 2; // Center horizontally
                        $newsImageY = 0; // Start from top
                        
                        $scaledNewsImage = imagecreatetruecolor($newsImageNewWidth, $newsImageNewHeight);
                        imagecopyresampled($scaledNewsImage, $newsImage, 0, 0, 0, 0, $newsImageNewWidth, $newsImageNewHeight, $newsImageWidth, $newsImageHeight);
                        
                        // Position news image
                        error_log("Photo Card Debug: Drawing image at X: $newsImageX, Y: $newsImageY, Width: $newsImageNewWidth, Height: $newsImageNewHeight");
                        imagecopy($image, $scaledNewsImage, $newsImageX, $newsImageY, 0, 0, $newsImageNewWidth, $newsImageNewHeight);
                    } else {
                        // For other templates, use original logic
                        $newsImageNewWidth = $width - 100; // Full width minus margins
                        $newsImageNewHeight = ($newsImageHeight / $newsImageWidth) * $newsImageNewWidth;
                        
                        // Limit height to 60% of total height to leave space for text
                        $maxImageHeight = $height * 0.6;
                        if ($newsImageNewHeight > $maxImageHeight) {
                            $newsImageNewHeight = $maxImageHeight;
                            $newsImageNewWidth = ($newsImageWidth / $newsImageHeight) * $newsImageNewHeight;
                        }
                        
                        $scaledNewsImage = imagecreatetruecolor($newsImageNewWidth, $newsImageNewHeight);
                        imagecopyresampled($scaledNewsImage, $newsImage, 0, 0, 0, 0, $newsImageNewWidth, $newsImageNewHeight, $newsImageWidth, $newsImageHeight);
                        
                        // Position news image at top center
                        $newsImageX = ($width - $newsImageNewWidth) / 2;
                        $newsImageY = 50; // Top margin
                        imagecopy($image, $scaledNewsImage, $newsImageX, $newsImageY, 0, 0, $newsImageNewWidth, $newsImageNewHeight);
                    }
                    
                    imagedestroy($newsImage);
                    imagedestroy($scaledNewsImage);
                }
            }
        }
        
        // Add text overlay - Use fonts that support Bengali text rendering
        $fontPath = '/usr/share/fonts/truetype/noto/NotoSansBengali-Bold.ttf'; // Use Bold version which might work better
        if (!file_exists($fontPath)) {
            // Try alternative Bengali fonts
            $alternativeFonts = [
                '/usr/share/fonts/truetype/noto/NotoSansBengali-Regular.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengali-Bold.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengali-SemiBold.ttf',
                '/usr/share/fonts/truetype/noto/NotoSerifBengali-Regular.ttf',
                '/usr/share/fonts/truetype/noto/NotoSerifBengali-Bold.ttf',
                '/usr/share/fonts/truetype/noto/HindSiliguri-Regular.ttf',
                '/usr/share/fonts/truetype/noto/HindSiliguri-Bold.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengaliUI-Regular.ttf',
                '/usr/share/fonts/truetype/noto/NotoSansBengaliUI-Bold.ttf',
                '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
                '/usr/share/fonts/truetype/liberation/LiberationSans-Regular.ttf',
                '/System/Library/Fonts/KohinoorBangla.ttc',
                '/System/Library/Fonts/Arial.ttf'
            ];
            
            $fontPath = null;
            foreach ($alternativeFonts as $altFont) {
                if (file_exists($altFont)) {
                    $fontPath = $altFont;
                    break;
                }
            }
            
            if (!$fontPath) {
                throw new Exception('No suitable font found for Bengali text rendering');
            }
        }
        
        // Add title text based on template
        $title = $news['title'];
        
        // Debug: Log the title being processed
        error_log("Photo card title: " . $title);
        error_log("Title length: " . strlen($title));
        error_log("Title mb_strlen: " . mb_strlen($title, 'UTF-8'));
        
        // Ensure proper UTF-8 encoding for Bengali text
        $title = mb_convert_encoding($title, 'UTF-8', 'UTF-8');
        
        if ($template === 'header_footer') {
            // For header_footer template, position title in title section
            $imageHeight = $height; // Image takes full height
            $titleHeight = max(200, $height * 0.2); // Minimum 200px or 20% of height
            $footerHeight = max(80, $height * 0.1); // Minimum 80px or 10% of height
            
            // Adjust canvas height to accommodate full image + title + footer
            $totalHeight = $imageHeight + $titleHeight + $footerHeight;
            $image = imagecreatetruecolor($width, $totalHeight);
            
            $titleFontSize = 36;
            $titleColor = $black;
            $titleX = $width / 2; // Center horizontally
            $titleY = $imageHeight + 120; // Position after logo in title section
            $maxTitleWidth = $width - 100;
            
            // For Bengali text, use simpler approach without complex wrapping
            $lines = [$title]; // Just use the title as one line for now
            $lineHeight = 45;
            
            // Draw red background for entire title section
            $titleBackgroundY = $imageHeight; // Start from end of image
            $titleBgColor = imagecolorallocate($image, 150, 3, 26); // #96031a red
            imagefill($image, 0, $titleBackgroundY, $titleBgColor);
            
            // Simple direct rendering approach with white text
            $titleColor = $white; // White text on red background
            foreach ($lines as $index => $line) {
                $y = $titleY + ($index * $lineHeight);
                if ($y >= $imageHeight && $y < $totalHeight - $footerHeight) {
                    // Ensure proper UTF-8 encoding
                    $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
                    imagettftext($image, $titleFontSize, 0, $titleX, $y, $titleColor, $fontPath, $line);
                }
            }
            
            // Add footer
            $footerY = $totalHeight - $footerHeight;
            
            // Draw footer background
            $footerBgColor = imagecolorallocate($image, 0, 0, 0); // Black background
            imagefill($image, 0, $footerY, $footerBgColor);
            
            // Add footer text
            $footerFontSize = 16;
            $footerColor = $white;
            
            // Left side: "Barind Post | Category"
            $categoryName = $news['category_name'] ?? 'সংবাদ';
            $leftText = 'বরিন্দ পোস্ট | ' . $categoryName;
            imagettftext($image, $footerFontSize, 0, 20, $footerY + ($footerHeight / 2) + 5, $footerColor, $fontPath, $leftText);
            
            // Right side: Date
            $dateText = date('d M, Y', strtotime($news['published_at']));
            $dateWidth = imagettfbbox($footerFontSize, 0, $fontPath, $dateText);
            $dateX = $width - 20 - ($dateWidth[2] - $dateWidth[0]);
            imagettftext($image, $footerFontSize, 0, $dateX, $footerY + ($footerHeight / 2) + 5, $footerColor, $fontPath, $dateText);
            
        } else {
            // For other templates, use original logic
            $titleFontSize = 36;
            $titleColor = $template === 'default' ? $black : $white;
            
            // Calculate text position at bottom
            $titleX = 50;
            $titleY = $height - 200; // Position at bottom
            $maxTitleWidth = $width - 100; // Full width minus margins
            
            // For Bengali text, use simpler approach without complex wrapping
            $lines = [$title]; // Just use the title as one line for now
            $lineHeight = 45;
            
            // Simple direct rendering approach (like the working example)
            foreach ($lines as $index => $line) {
                $y = $titleY + ($index * $lineHeight);
                if ($y < $height - 100) { // Don't go below bottom
                    // Ensure proper UTF-8 encoding
                    $line = mb_convert_encoding($line, 'UTF-8', 'UTF-8');
                    
                    // Try with a test title first to see if the issue is with the data
                    $testTitle = 'আমরা বিশ্বাস করি';
                    imagettftext($image, $titleFontSize, 0, $titleX, $y, $titleColor, $fontPath, $testTitle);
                    
                    // Then try with the actual title
                    imagettftext($image, $titleFontSize, 0, $titleX, $y + 50, $titleColor, $fontPath, $line);
                }
            }
            
            // Add subtitle/date (positioned at bottom)
            $dateText = date('d M, Y', strtotime($news['published_at']));
            $dateFontSize = 18;
            $dateColor = $template === 'default' ? $lightGray : $white;
            $dateY = $titleY + (count($lines) * $lineHeight) + 20;
            
            // Simple direct rendering for date (like the working example)
            imagettftext($image, $dateFontSize, 0, $titleX, $dateY, $dateColor, $fontPath, $dateText);
            
            // Add website URL
            $urlText = 'barindpost.com';
            $urlFontSize = 16;
            $urlColor = $template === 'default' ? $red : $white;
            $urlY = $height - 50;
            
            // Simple direct rendering for URL (like the working example)
            imagettftext($image, $urlFontSize, 0, $titleX, $urlY, $urlColor, $fontPath, $urlText);
        }
        
        // Save the image
        // Capture image data to string
        ob_start();
        imagepng($image);
        $imageData = ob_get_contents();
        ob_end_clean();
        
        imagedestroy($image);
        
        return $imageData;
    }

    private function wrapText($text, $fontPath, $fontSize, $maxWidth)
    {
        // For Bengali text, we need to handle it differently
        // Bengali text doesn't use spaces the same way as English
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
        
        foreach ($words as $index => $word) {
            $testLine = $currentLine . ' ' . $word;
            
            // Ensure proper UTF-8 encoding
            $testLine = mb_convert_encoding($testLine, 'UTF-8', 'UTF-8');
            
            $bbox = imagettfbbox($fontSize, 0, $fontPath, $testLine);
            if ($bbox === false) {
                // If bbox fails, try with a simpler approach
                $lineWidth = strlen($testLine) * ($fontSize * 0.6); // Approximate width
            } else {
                $lineWidth = $bbox[2] - $bbox[0];
            }
            
            if ($lineWidth > $maxWidth && $currentLine !== '') {
                // If we already have 1 line, try to fit remaining words on second line
                if (count($lines) === 0) {
                    $lines[] = trim($currentLine);
                    $currentLine = $word;
                } else {
                    // We already have 1 line, try to fit remaining words on second line
                    $remainingWords = array_slice($words, $index);
                    $secondLine = implode(' ', $remainingWords);
                    
                    // Check if second line fits
                    $bbox2 = imagettfbbox($fontSize, 0, $fontPath, $secondLine);
                    if ($bbox2 === false) {
                        $secondLineWidth = strlen($secondLine) * ($fontSize * 0.6);
                    } else {
                        $secondLineWidth = $bbox2[2] - $bbox2[0];
                    }
                    
                    if ($secondLineWidth <= $maxWidth) {
                        // All remaining words fit on second line
                        $lines[] = trim($currentLine);
                        $lines[] = $secondLine;
                        break;
                    } else {
                        // Second line is too long, truncate with ellipsis
                        $truncatedLine = '';
                        foreach ($remainingWords as $remainingWord) {
                            $testTruncated = $truncatedLine . ($truncatedLine ? ' ' : '') . $remainingWord . '...';
                            $bbox3 = imagettfbbox($fontSize, 0, $fontPath, $testTruncated);
                            if ($bbox3 === false) {
                                $truncatedWidth = strlen($testTruncated) * ($fontSize * 0.6);
                            } else {
                                $truncatedWidth = $bbox3[2] - $bbox3[0];
                            }
                            
                            if ($truncatedWidth <= $maxWidth) {
                                $truncatedLine = $testTruncated;
                            } else {
                                break;
                            }
                        }
                        $lines[] = trim($currentLine);
                        $lines[] = $truncatedLine ?: '...';
                        break;
                    }
                }
            } else {
                $currentLine = $testLine;
            }
        }
        
        // Add the last line if we haven't reached 2 lines yet
        if (empty($lines)) {
            $lines[] = trim($currentLine);
        } elseif (count($lines) === 1 && trim($currentLine) !== $lines[0]) {
            $lines[] = trim($currentLine);
        }
        
        // Ensure we don't exceed 2 lines
        if (count($lines) > 2) {
            $lines = array_slice($lines, 0, 2);
        }
        
        return implode("\n", $lines);
    }
}
