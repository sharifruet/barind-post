<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ImageModel;

class ImageUpload extends BaseController
{
    /**
     * Handle AJAX image upload
     */
    public function upload()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        // Check if this is a CKEditor upload (different format)
        $isCKEditor = $this->request->getPost('upload') !== null;
        
        if ($isCKEditor) {
            return $this->handleCKEditorUpload();
        }

        // Regular upload (for news form)
        $file = $this->request->getFile('image');
        $caption = $this->request->getPost('caption');
        $altText = $this->request->getPost('alt_text');

        if (!$file) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'No file uploaded'
            ]);
        }

        if (!$file->isValid()) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'File upload error: ' . $file->getErrorString()
            ]);
        }

        // Check if file has already been moved
        if ($file->hasMoved()) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'File has already been moved'
            ]);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();
        
        if (!in_array($mimeType, $allowedTypes)) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Invalid file type: ' . $mimeType . '. Only JPG, PNG, GIF, and WebP are allowed'
            ]);
        }

        // Validate file size (max 5MB)
        $fileSize = $file->getSize();
        if ($fileSize > 5 * 1024 * 1024) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'File size too large. Maximum 5MB allowed'
            ]);
        }

        try {
            // Generate unique filename
            $timestamp = time();
            $randomString = bin2hex(random_bytes(8));
            $extension = $file->getExtension();
            $filename = $timestamp . '_' . $randomString . '.' . $extension;

            // Create upload directory if it doesn't exist
            $uploadPath = FCPATH . 'public/uploads/news/';
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    throw new \Exception('Failed to create upload directory: ' . $uploadPath);
                }
            }

            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                throw new \Exception('Upload directory is not writable: ' . $uploadPath);
            }

            // Move uploaded file
            if (!$file->move($uploadPath, $filename)) {
                throw new \Exception('Failed to move uploaded file. Error: ' . $file->getErrorString());
            }

            $relativePath = 'public/uploads/news/' . $filename;

            // Verify file was actually moved
            if (!file_exists($uploadPath . $filename)) {
                throw new \Exception('File was not moved successfully');
            }

            // Get image dimensions
            $imageInfo = getimagesize($uploadPath . $filename);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            // Save to images table
            $imageModel = new ImageModel();
            $imageData = [
                'image_name' => $caption ?: $file->getClientName(),
                'image_path' => $relativePath,
                'original_filename' => $file->getClientName(),
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'width' => $width,
                'height' => $height,
                'caption' => $caption,
                'alt_text' => $altText,
                'uploaded_by' => session('user_id') ?: 1 // Default to user ID 1 if not logged in
            ];

            $imageId = $imageModel->insert($imageData);

            if (!$imageId) {
                throw new \Exception('Failed to save image to database');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Image uploaded successfully',
                'image' => [
                    'id' => $imageId,
                    'image_path' => $relativePath,
                    'caption' => $caption,
                    'alt_text' => $altText,
                    'preview_url' => base_url($relativePath)
                ]
            ]);

        } catch (\Exception $e) {
            // Clean up any partially uploaded file
            if (isset($uploadPath, $filename) && file_exists($uploadPath . $filename)) {
                @unlink($uploadPath . $filename);
            }
            
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'Upload failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Handle CKEditor image upload
     */
    private function handleCKEditorUpload()
    {
        $file = $this->request->getFile('upload');

        if (!$file) {
            return $this->response->setJSON([
                'error' => [
                    'message' => 'No file uploaded'
                ]
            ]);
        }

        if (!$file->isValid()) {
            return $this->response->setJSON([
                'error' => [
                    'message' => 'File upload error: ' . $file->getErrorString()
                ]
            ]);
        }

        // Check if file has already been moved
        if ($file->hasMoved()) {
            return $this->response->setJSON([
                'error' => [
                    'message' => 'File has already been moved'
                ]
            ]);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType = $file->getMimeType();
        
        if (!in_array($mimeType, $allowedTypes)) {
            return $this->response->setJSON([
                'error' => [
                    'message' => 'Invalid file type: ' . $mimeType . '. Only JPG, PNG, GIF, and WebP are allowed'
                ]
            ]);
        }

        // Validate file size (max 5MB)
        $fileSize = $file->getSize();
        if ($fileSize > 5 * 1024 * 1024) {
            return $this->response->setJSON([
                'error' => [
                    'message' => 'File size too large. Maximum 5MB allowed'
                ]
            ]);
        }

        try {
            // Generate unique filename
            $timestamp = time();
            $randomString = bin2hex(random_bytes(8));
            $extension = $file->getExtension();
            $filename = $timestamp . '_' . $randomString . '.' . $extension;

            // Create upload directory if it doesn't exist
            $uploadPath = FCPATH . 'public/uploads/news/';
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0755, true)) {
                    throw new \Exception('Failed to create upload directory: ' . $uploadPath);
                }
            }

            // Check if directory is writable
            if (!is_writable($uploadPath)) {
                throw new \Exception('Upload directory is not writable: ' . $uploadPath);
            }

            // Move uploaded file
            if (!$file->move($uploadPath, $filename)) {
                throw new \Exception('Failed to move uploaded file. Error: ' . $file->getErrorString());
            }

            $relativePath = 'public/uploads/news/' . $filename;

            // Verify file was actually moved
            if (!file_exists($uploadPath . $filename)) {
                throw new \Exception('File was not moved successfully');
            }

            // Get image dimensions
            $imageInfo = getimagesize($uploadPath . $filename);
            $width = $imageInfo[0] ?? null;
            $height = $imageInfo[1] ?? null;

            // Save to images table
            $imageModel = new ImageModel();
            $imageData = [
                'image_name' => $file->getClientName(),
                'image_path' => $relativePath,
                'original_filename' => $file->getClientName(),
                'file_size' => $fileSize,
                'mime_type' => $mimeType,
                'width' => $width,
                'height' => $height,
                'caption' => null,
                'alt_text' => null,
                'uploaded_by' => session('user_id') ?: 1
            ];

            $imageId = $imageModel->insert($imageData);

            if (!$imageId) {
                throw new \Exception('Failed to save image to database');
            }

            // Return CKEditor format response - simpleUpload expects just the URL
            return $this->response->setJSON([
                'url' => base_url($relativePath)
            ]);

        } catch (\Exception $e) {
            // Clean up any partially uploaded file
            if (isset($uploadPath, $filename) && file_exists($uploadPath . $filename)) {
                @unlink($uploadPath . $filename);
            }
            
            return $this->response->setJSON([
                'error' => [
                    'message' => 'Upload failed: ' . $e->getMessage()
                ]
            ]);
        }
    }

    /**
     * Get existing images for selection
     */
    public function getExistingImages()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $imageModel = new ImageModel();
            $images = $imageModel->getAllImagesWithUsage();
            
            $formattedImages = [];
            foreach ($images as $image) {
                $formattedImages[] = [
                    'id' => $image['id'],
                    'image_path' => $image['image_path'],
                    'caption' => $image['caption'],
                    'alt_text' => $image['alt_text'],
                    'preview_url' => base_url($image['image_path']),
                    'usage_count' => $image['usage_count'],
                    'uploaded_at' => $image['created_at']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'images' => $formattedImages
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to fetch images: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get all images (alias for getExistingImages)
     */
    public function getAllImages()
    {
        return $this->getExistingImages();
    }

    /**
     * Update image caption
     */
    public function updateCaption()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $imageId = $this->request->getPost('image_id');
        $caption = $this->request->getPost('caption');
        $altText = $this->request->getPost('alt_text');

        if (!$imageId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Image ID is required']);
        }

        try {
            $imageModel = new ImageModel();
            $imageModel->update($imageId, [
                'caption' => $caption,
                'alt_text' => $altText
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Image updated successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update image: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete image
     */
    public function delete()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $imageId = $this->request->getPost('image_id');

        if (!$imageId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Image ID is required']);
        }

        try {
            $imageModel = new ImageModel();
            $deleted = $imageModel->deleteImageIfUnused($imageId);

            if ($deleted) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Image deleted successfully'
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Image cannot be deleted as it is being used'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to delete image: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Set image as featured
     */
    public function setFeatured()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $imageId = $this->request->getPost('image_id');
        $featured = $this->request->getPost('featured');

        if (!$imageId) {
            return $this->response->setJSON(['success' => false, 'message' => 'Image ID is required']);
        }

        try {
            $imageModel = new ImageModel();
            $imageModel->update($imageId, ['featured' => $featured]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Image featured status updated'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to update featured status: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get images for a specific news article
     */
    public function getImages($newsId)
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('news');
            $news = $builder->where('id', $newsId)->get()->getRowArray();

            if (!$news) {
                return $this->response->setJSON(['success' => false, 'message' => 'News article not found']);
            }

            $images = [];
            if ($news['image_url']) {
                $images[] = [
                    'id' => 0, // No specific ID for news images
                    'image_path' => $news['image_url'],
                    'caption' => $news['image_caption'],
                    'alt_text' => $news['image_alt_text'],
                    'preview_url' => base_url($news['image_url'])
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'images' => $images
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to fetch images: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Link image to news article
     */
    public function linkImage()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $newsId = $this->request->getPost('news_id');
        $imagePath = $this->request->getPost('image_path');
        $caption = $this->request->getPost('caption');
        $altText = $this->request->getPost('alt_text');

        if (!$newsId || !$imagePath) {
            return $this->response->setJSON(['success' => false, 'message' => 'News ID and image path are required']);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('news');
            $builder->where('id', $newsId)->update([
                'image_url' => $imagePath,
                'image_caption' => $caption,
                'image_alt_text' => $altText
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Image linked successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to link image: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Remove image from news article
     */
    public function removeFromNews()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request']);
        }

        $newsId = $this->request->getPost('news_id');

        if (!$newsId) {
            return $this->response->setJSON(['success' => false, 'message' => 'News ID is required']);
        }

        try {
            $db = \Config\Database::connect();
            $builder = $db->table('news');
            $builder->where('id', $newsId)->update([
                'image_url' => null,
                'image_caption' => null,
                'image_alt_text' => null
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Image removed successfully'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Failed to remove image: ' . $e->getMessage()
            ]);
        }
    }
} 