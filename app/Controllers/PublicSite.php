<?php
namespace App\Controllers;

use App\Models\NewsModel;
use App\Models\CategoryModel;
use App\Models\TagModel;
use CodeIgniter\Controller;

class PublicSite extends Controller
{
    public function __construct()
    {
        // Set proper UTF-8 headers for Bengali text
        header('Content-Type: text/html; charset=UTF-8');
    }

    public function home()
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        
        // Get featured news
        $featuredNews = $newsModel->where('featured', 1)
                                ->where('status', 'published')
                                ->orderBy('published_at', 'DESC')
                                ->findAll(6);
        
        // Get latest news (excluding featured)
        $latestNews = $newsModel->where('featured', 0)
                                ->where('status', 'published')
                                ->orderBy('published_at', 'DESC')
                                ->findAll(10);
        
        $categories = $categoryModel->findAll();
        
        return view('public/home', [
            'featuredNews' => $featuredNews,
            'latestNews' => $latestNews,
            'categories' => $categories
        ]);
    }

    public function section($slug)
    {
        $categoryModel = new CategoryModel();
        $newsModel = new NewsModel();
        $categories = $categoryModel->findAll();
        $category = $categoryModel->where('slug', $slug)->first();
        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $news = $newsModel->where('category_id', $category['id'])->where('status', 'published')->orderBy('published_at', 'DESC')->findAll(20);
        return view('public/section', [
            'category' => $category,
            'news' => $news,
            'categories' => $categories
        ]);
    }

    public function news($slug)
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        $news = $newsModel->where('slug', $slug)->where('status', 'published')->first();
        if (!$news) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        return view('public/news', ['news' => $news, 'categories' => $categories]);
    }

    public function tag($slug)
    {
        $tagModel = new TagModel();
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        $tag = $tagModel->where('name', $slug)->first();
        if (!$tag) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        $db = \Config\Database::connect();
        $newsIds = $db->table('news_tags')->select('news_id')->where('tag_id', $tag['id'])->get()->getResultArray();
        $ids = array_column($newsIds, 'news_id');
        $news = [];
        if ($ids) {
            $news = $newsModel->whereIn('id', $ids)->where('status', 'published')->orderBy('published_at', 'DESC')->findAll(20);
        }
        return view('public/tag', [
            'tag' => $tag,
            'news' => $news,
            'categories' => $categories
        ]);
    }

    public function search()
    {
        $query = $this->request->getGet('q');
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        
        $news = [];
        $categories = $categoryModel->findAll();
        
        if ($query) {
            $news = $newsModel->like('title', $query)
                            ->orLike('subtitle', $query)
                            ->orLike('lead_text', $query)
                            ->orLike('content', $query)
                            ->where('status', 'published')
                            ->orderBy('published_at', 'DESC')
                            ->findAll(20);
        }
        
        return view('public/search', [
            'query' => $query,
            'news' => $news,
            'categories' => $categories
        ]);
    }
} 