<?php
namespace App\Controllers;

use App\Models\RoleModel;
use App\Models\UserModel;
use App\Models\NewsModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use App\Models\PrayerTimesModel;
use App\Models\CityModel;
use App\Models\KickerModel;
use Exception;

/**
 * Social photo-card generator (GD). Route-level `role:admin`.
 */
class AdminPhotoCards extends BaseAdminController
{
    public function photoCardGenerator()
    {
        // Check if user is admin
        // Access control: `role:` filter on this route (app/Config/Routes.php).
        
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        
        // Get news with category information
        $db = \Config\Database::connect();
        $news = $db->table('news')
                   ->select('news.*, categories.name as category_name')
                   ->join('categories', 'categories.id = news.category_id', 'left')
                   ->where('news.status', 'published')
                   ->orderBy('news.published_at', 'DESC')
                   ->limit(50)
                   ->get()
                   ->getResultArray();
        
        return view('admin/photo_card_generator', [
            'news' => $news
        ]);
    }

    public function generatePhotoCard()
    {
        // Check if user is logged in and has admin role
        if (!session('user_id') || session('user_role') !== 'admin') {
            return $this->response->setJSON(['error' => 'Access denied. Admin role required.']);
        }
        
        $newsId = $this->request->getPost('news_id');
        $template = $this->request->getPost('template') ?? 'default';
        
        if (!$newsId) {
            return $this->response->setJSON(['error' => 'News ID is required.']);
        }
        
        // Get news with category information
        $db = \Config\Database::connect();
        $news = $db->table('news')
                   ->select('news.*, categories.name as category_name')
                   ->join('categories', 'categories.id = news.category_id', 'left')
                   ->where('news.id', $newsId)
                   ->get()
                   ->getRowArray();
        
        if (!$news) {
            return $this->response->setJSON(['error' => 'News article not found.']);
        }
        
        try {
            // Generate the photo card
            $imageData = (new \App\Libraries\PhotoCard())->render($news, $template);
            
            // Return the image data as base64
            return $this->response->setJSON([
                'success' => true,
                'image' => base64_encode($imageData)
            ]);
        } catch (Exception $e) {
            return $this->response->setJSON(['error' => 'Failed to generate photo card: ' . $e->getMessage()]);
        }
    }

}
