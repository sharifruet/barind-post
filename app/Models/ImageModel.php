<?php

namespace App\Models;

use CodeIgniter\Model;

class ImageModel extends Model
{
    protected $table = 'images';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'image_name', 
        'image_path', 
        'original_filename', 
        'file_size', 
        'mime_type', 
        'width', 
        'height',
        'caption',
        'alt_text',
        'uploaded_by'
    ];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    /**
     * Get all images with usage count
     */
    public function getAllImagesWithUsage()
    {
        $builder = $this->db->table('images i');
        $builder->select('i.*, COUNT(ni.id) as usage_count');
        $builder->join('news_images ni', 'ni.image_id = i.id', 'left');
        $builder->groupBy('i.id');
        $builder->orderBy('i.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Get unused images
     */
    public function getUnusedImages()
    {
        $builder = $this->db->table('images i');
        $builder->select('i.*');
        $builder->join('news_images ni', 'ni.image_id = i.id', 'left');
        $builder->where('ni.id IS NULL');
        $builder->orderBy('i.created_at', 'DESC');
        
        return $builder->get()->getResultArray();
    }

    /**
     * Check if image is used by any news article
     */
    public function isImageUsed($imageId)
    {
        $builder = $this->db->table('news_images');
        $builder->where('image_id', $imageId);
        
        return $builder->countAllResults() > 0;
    }

    /**
     * Delete image if not used
     */
    public function deleteImageIfUnused($imageId)
    {
        if (!$this->isImageUsed($imageId)) {
            // Get image info before deletion
            $image = $this->find($imageId);
            if ($image) {
                // Delete physical file
                $filePath = FCPATH . $image['image_path'];
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
                
                // Delete database record
                return $this->delete($imageId);
            }
        }
        
        return false;
    }
} 