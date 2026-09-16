<?php
// Controller for Bengali overlay functionality

class BengaliOverlayController {
    
    public function index() {
        // Serve the HTML page
        $htmlFile = __DIR__ . '/bengali_overlay.html';
        if (file_exists($htmlFile)) {
            header('Content-Type: text/html; charset=utf-8');
            readfile($htmlFile);
        } else {
            echo "HTML file not found";
        }
    }
    
    public function generateOverlay() {
        // Handle AJAX requests for overlay generation
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $text = $_POST['text'] ?? 'বারিন্দ পোস্ট';
            $fontSize = $_POST['fontSize'] ?? 24;
            $position = $_POST['position'] ?? 'bottom-left';
            $textColor = $_POST['textColor'] ?? '#ffffff';
            $shadowColor = $_POST['shadowColor'] ?? '#000000';
            
            // Return JSON response
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'text' => $text,
                'fontSize' => $fontSize,
                'position' => $position,
                'textColor' => $textColor,
                'shadowColor' => $shadowColor
            ]);
        }
    }
    
    public function downloadImage() {
        // Handle image download requests
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $imageData = $_POST['imageData'] ?? '';
            
            if (!empty($imageData)) {
                // Remove data URL prefix
                $imageData = str_replace('data:image/png;base64,', '', $imageData);
                $imageData = str_replace(' ', '+', $imageData);
                
                // Decode base64
                $imageData = base64_decode($imageData);
                
                // Generate filename
                $filename = 'bengali_overlay_' . date('Y-m-d_H-i-s') . '.png';
                $filepath = __DIR__ . '/public/' . $filename;
                
                // Save image
                if (file_put_contents($filepath, $imageData)) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'filename' => $filename,
                        'filepath' => $filepath
                    ]);
                } else {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => 'Failed to save image'
                    ]);
                }
            }
        }
    }
}

// Handle requests
$controller = new BengaliOverlayController();

$action = $_GET['action'] ?? 'index';

switch ($action) {
    case 'generate':
        $controller->generateOverlay();
        break;
    case 'download':
        $controller->downloadImage();
        break;
    default:
        $controller->index();
        break;
}
?> 