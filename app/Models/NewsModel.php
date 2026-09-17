<?php
namespace App\Models;
use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'subtitle', 'lead_text', 'reporterRole', 'content', 'author_id', 'category_id', 'status',
        'featured', 'kicker_id', 'event_id', 'match_id', 'created_at', 'updated_at', 'published_at',
        'image_url', 'image_caption', 'image_alt_text', 'slug', 'source', 'dateline', 'word_count', 'language',
        'source_url', 'content_hash', 'suggested_image_url'
    ];
    protected $returnType = 'array';

    // Every write through this model invalidates the public page cache
    // (see app/Helpers/cache_helper.php). Query-builder writes must purge themselves.
    protected $afterInsert = ['purgePublicCache'];
    protected $afterUpdate = ['purgePublicCache'];
    protected $afterDelete = ['purgePublicCache'];

    protected function purgePublicCache(array $eventData): array
    {
        purge_public_cache();

        return $eventData;
    }
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Set default values
    protected $defaultValues = [
        'language' => 'bn', // Bangla
        'status' => 'draft',
        'featured' => false
    ];

    // Optionally, add methods for tags, category, author relationships
    
    /**
     * Get most read news from a specific time period
     */
    public function getMostReadNews($days = 3, $limit = 10)
    {
        return $this->getMostReadWithKicker($days, $limit);
    }
    
    /**
     * Get view count for a specific news article
     */
    public function getViewCount($newsId)
    {
        $db = \Config\Database::connect();
        return $db->table('news_views')
                  ->where('news_id', $newsId)
                  ->countAllResults();
    }
    
    /**
     * Get view counts for multiple news articles
     */
    public function getViewCounts($newsIds)
    {
        $db = \Config\Database::connect();
        $viewCounts = [];
        
        foreach ($newsIds as $newsId) {
            $count = $db->table('news_views')
                       ->where('news_id', $newsId)
                       ->countAllResults();
            $viewCounts[$newsId] = $count;
        }
        
        return $viewCounts;
    }
    
    /**
     * Track a view for a news article
     */
    public function trackView($newsId, $ipAddress)
    {
        $db = \Config\Database::connect();
        
        // Check if this IP has already viewed this news in the last 30 minutes
        $thirtyMinutesAgo = date('Y-m-d H:i:s', strtotime('-30 minutes'));
        $existingView = $db->table('news_views')
                          ->where('news_id', $newsId)
                          ->where('viewer_ip', $ipAddress)
                          ->where('viewed_at >=', $thirtyMinutesAgo)
                          ->countAllResults();
        
        // Only insert if no recent view from this IP
        if ($existingView == 0) {
            $db->table('news_views')->insert([
                'news_id' => $newsId,
                'viewed_at' => date('Y-m-d H:i:s'),
                'viewer_ip' => $ipAddress
            ]);
            
            // Occasionally clean up old view records (keep only last 6 months)
            if (rand(1, 100) == 1) { // 1% chance to run cleanup
                $this->cleanupOldViews();
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Get latest news excluding a specific article
     */
    public function getLatestNewsExcluding($excludeId, $limit = 8)
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->where('n.status', 'published')
                 ->where('n.id !=', $excludeId)
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get featured news
     */
    public function getFeaturedNews($limit = 6)
    {
        return $this->getFeaturedWithKicker($limit);
    }
    
    /**
     * Get latest news (excluding featured)
     */
    public function getLatestNews($limit = 25)
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->where('n.featured', 0)
                 ->where('n.status', 'published')
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get news by category
     */
    public function getNewsByCategory($categoryId, $limit = 20)
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->where('n.category_id', $categoryId)
                 ->where('n.status', 'published')
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get news by category slug
     */
    public function getNewsByCategorySlug($categorySlug, $limit = 20)
    {
        $categoryModel = new \App\Models\CategoryModel();
        $category = $categoryModel->where('slug', $categorySlug)->first();
        
        if (!$category) {
            return [];
        }
        
        return $this->getNewsByCategory($category['id'], $limit);
    }
    
    /**
     * Get news by slug
     */
    public function getNewsBySlug($slug)
    {
        return $this->findWithKicker($slug, 'slug');
    }
    
    /**
     * Get news by title
     */
    public function getNewsByTitle($title)
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->where('n.title', $title)
                 ->where('n.status', 'published')
                 ->get()
                 ->getRowArray();
    }
    
    /**
     * Search news by query
     */
    public function searchNews($query, $limit = 20)
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->groupStart()
                     ->like('n.title', $query)
                     ->orLike('n.content', $query)
                 ->groupEnd()
                 ->where('n.status', 'published')
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get news by tag
     */
    public function getNewsByTag($tagSlug, $limit = 20)
    {
        $tagModel = new \App\Models\TagModel();
        $tag = $tagModel->where('name', $tagSlug)->first();
        
        if (!$tag) {
            return [];
        }
        
        $db = \Config\Database::connect();
        $newsIds = $db->table('news_tags')
                     ->select('news_id')
                     ->where('tag_id', $tag['id'])
                     ->get()
                     ->getResultArray();
        
        $ids = array_column($newsIds, 'news_id');
        
        if (empty($ids)) {
            return [];
        }
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->whereIn('n.id', $ids)
                 ->where('n.status', 'published')
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }
    
    /**
     * Get all published news for RSS
     */
    public function getAllPublishedNews($limit = null)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('news n')
                   ->select('n.*, k.text as kicker, k.color as kicker_color')
                   ->join('kickers k', 'n.kicker_id = k.id', 'left')
                   ->where('n.status', 'published')
                   ->orderBy('n.published_at', 'DESC');
        
        if ($limit) {
            return $query->limit($limit)->get()->getResultArray();
        }
        
        return $query->get()->getResultArray();
    }
    
    /**
     * Clean up old view records to keep database optimized
     */
    private function cleanupOldViews()
    {
        $db = \Config\Database::connect();
        $sixMonthsAgo = date('Y-m-d H:i:s', strtotime('-6 months'));
        
        $db->table('news_views')
           ->where('viewed_at <', $sixMonthsAgo)
           ->delete();
    }

    /**
     * Get news with kicker information
     */
    public function findWithKicker($value, $field = 'id')
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->where('n.' . $field, $value)
                 ->get()
                 ->getRowArray();
    }

    /**
     * Get all news with kicker information
     */
    public function findAllWithKicker($limit = null, $offset = null)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('news n')
                   ->select('n.*, k.text as kicker, k.color as kicker_color')
                   ->join('kickers k', 'n.kicker_id = k.id', 'left')
                   ->orderBy('n.created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get()->getResultArray();
    }

    /**
     * Get published news with kicker information
     */
    public function getPublishedWithKicker($limit = null, $offset = null)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('news n')
                   ->select('n.*, k.text as kicker, k.color as kicker_color')
                   ->join('kickers k', 'n.kicker_id = k.id', 'left')
                   ->where('n.status', 'published')
                   ->orderBy('n.published_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get()->getResultArray();
    }

    /**
     * Get featured news with kicker information
     */
    public function getFeaturedWithKicker($limit = 7)
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->where('n.status', 'published')
                 ->where('n.featured', true)
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }

    /**
     * Update kicker usage count when news is created/updated
     */
    public function updateKickerUsage($newsId, $oldKickerId = null, $newKickerId = null)
    {
        $kickerModel = new KickerModel();
        
        // Decrement old kicker usage
        if ($oldKickerId) {
            $oldKicker = $kickerModel->find($oldKickerId);
            if ($oldKicker) {
                $kickerModel->update($oldKickerId, [
                    'usage_count' => max(0, $oldKicker['usage_count'] - 1)
                ]);
            }
        }
        
        // Increment new kicker usage
        if ($newKickerId) {
            $newKicker = $kickerModel->find($newKickerId);
            if ($newKicker) {
                $kickerModel->update($newKickerId, [
                    'usage_count' => $newKicker['usage_count'] + 1
                ]);
            }
        }
    }

    /**
     * Get news list with kicker info for admin based on user role
     */
    public function getNewsListForAdmin($userRole, $userId = null)
    {
        $db = \Config\Database::connect();
        
        if ($userRole === 'reporter') {
            // Reporters can only see their own news
            return $db->table('news n')
                     ->select('n.*, k.text as kicker, k.color as kicker_color')
                     ->join('kickers k', 'n.kicker_id = k.id', 'left')
                     ->where('n.author_id', $userId)
                     ->orderBy('n.created_at', 'DESC')
                     ->get()
                     ->getResultArray();
        } else {
            // Editors, sub-editors, and admins can see all news
            return $this->findAllWithKicker();
        }
    }

    /**
     * Get news with kicker info for a specific author
     */
    public function getNewsByAuthorWithKicker($authorId, $limit = null, $offset = null)
    {
        $db = \Config\Database::connect();
        
        $query = $db->table('news n')
                   ->select('n.*, k.text as kicker, k.color as kicker_color')
                   ->join('kickers k', 'n.kicker_id = k.id', 'left')
                   ->where('n.author_id', $authorId)
                   ->orderBy('n.created_at', 'DESC');
        
        if ($limit) {
            $query->limit($limit, $offset);
        }
        
        return $query->get()->getResultArray();
    }

    /**
     * Get latest news with kicker info
     */
    public function getLatestWithKicker($limit = 20, $offset = 0)
    {
        $db = \Config\Database::connect();
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->where('n.status', 'published')
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit, $offset)
                 ->get()
                 ->getResultArray();
    }

    /**
     * Get most read news with kicker info
     */
    public function getMostReadWithKicker($days = 3, $limit = 10)
    {
        $db = \Config\Database::connect();
        $timeAgo = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        return $db->table('news n')
                 ->select('n.*, k.text as kicker, k.color as kicker_color, COUNT(nv.id) as view_count')
                 ->join('kickers k', 'n.kicker_id = k.id', 'left')
                 ->join('news_views nv', 'n.id = nv.news_id', 'left')
                 ->where('n.status', 'published')
                 ->where('n.published_at >=', $timeAgo)
                 ->groupBy('n.id')
                 ->orderBy('view_count', 'DESC')
                 ->orderBy('n.published_at', 'DESC')
                 ->limit($limit)
                 ->get()
                 ->getResultArray();
    }
} 