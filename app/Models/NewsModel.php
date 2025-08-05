<?php
namespace App\Models;
use CodeIgniter\Model;

class NewsModel extends Model
{
    protected $table = 'news';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title', 'subtitle', 'lead_text', 'content', 'author_id', 'category_id', 'status',
        'featured', 'created_at', 'updated_at', 'published_at', 'image_url', 'image_caption', 
        'image_alt_text', 'slug', 'source', 'dateline', 'word_count', 'language'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    // Optionally, add methods for tags, category, author relationships
} 