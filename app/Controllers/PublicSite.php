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
                                ->findAll(25);
        
        // Get major categories and their news
        $majorCategories = ['national', 'politics', 'economy', 'international'];
        $categoryNews = [];
        
        foreach ($majorCategories as $slug) {
            $category = $categoryModel->where('slug', $slug)->first();
            if ($category) {
                $news = $newsModel->where('category_id', $category['id'])
                                ->where('status', 'published')
                                ->orderBy('published_at', 'DESC')
                                ->findAll(4); // Get 4 latest news from each category
                
                $categoryNews[] = [
                    'category' => $category,
                    'news' => $news
                ];
            }
        }
        
        $categories = $categoryModel->findAll();
        
        return view('public/home', [
            'featuredNews' => $featuredNews,
            'latestNews' => $latestNews,
            'categoryNews' => $categoryNews,
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
        
        // Set meta tags for the section
        $data = [
            'category' => $category,
            'news' => $news,
            'categories' => $categories,
            'title' => $category['name'] . ' - বারিন্দ পোস্ট',
            'meta_description' => $category['name'] . ' বিভাগের সর্বশেষ সংবাদ। বারিন্দ পোস্টে প্রকাশিত ' . $category['name'] . ' সম্পর্কিত সব খবর জানুন।',
            'meta_keywords' => 'বারিন্দ পোস্ট, ' . $category['name'] . ', রাজশাহী সংবাদ, বাংলাদেশ সংবাদ',
            'og_title' => $category['name'] . ' - বারিন্দ পোস্ট',
            'og_description' => $category['name'] . ' বিভাগের সর্বশেষ সংবাদ। বারিন্দ পোস্টে প্রকাশিত ' . $category['name'] . ' সম্পর্কিত সব খবর জানুন।',
            'og_type' => 'website',
            'twitter_title' => $category['name'] . ' - বারিন্দ পোস্ট',
            'twitter_description' => $category['name'] . ' বিভাগের সর্বশেষ সংবাদ। বারিন্দ পোস্টে প্রকাশিত ' . $category['name'] . ' সম্পর্কিত সব খবর জানুন।'
        ];
        
        return view('public/section', $data);
    }

    public function news($slug)
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        
        // Try to find the news article
        $news = $newsModel->where('slug', $slug)->where('status', 'published')->first();
        
        // If not found, try with URL decoded slug
        if (!$news) {
            $decodedSlug = urldecode($slug);
            $news = $newsModel->where('slug', $decodedSlug)->where('status', 'published')->first();
        }
        
        // If still not found, try with raw URL decoded slug
        if (!$news) {
            $rawSlug = rawurldecode($slug);
            $news = $newsModel->where('slug', $rawSlug)->where('status', 'published')->first();
        }
        
        // If still not found, try to find by title
        if (!$news) {
            $news = $newsModel->where('title', $slug)->where('status', 'published')->first();
        }
        
        // If still not found, try with URL decoded title
        if (!$news) {
            $decodedTitle = urldecode($slug);
            $news = $newsModel->where('title', $decodedTitle)->where('status', 'published')->first();
        }
        
        if (!$news) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Track the view
        $this->trackNewsView($news['id']);
        
        // Set meta tags for the news article
        $data = [
            'news' => $news,
            'categories' => $categories,
            'title' => $news['title'] . ' - বারিন্দ পোস্ট',
            'meta_description' => !empty($news['lead_text']) ? $news['lead_text'] : $news['title'] . ' - বারিন্দ পোস্টে প্রকাশিত সর্বশেষ সংবাদ।',
            'meta_keywords' => 'বারিন্দ পোস্ট, ' . $news['title'] . ', রাজশাহী সংবাদ, বাংলাদেশ সংবাদ',
            'og_title' => $news['title'],
            'og_description' => !empty($news['lead_text']) ? $news['lead_text'] : $news['title'] . ' - বারিন্দ পোস্টে প্রকাশিত সর্বশেষ সংবাদ।',
            'og_type' => 'article',
            'og_image' => !empty($news['image_url']) ? get_image_url($news['image_url']) : base_url('public/logo.png'),
            'twitter_card' => 'summary_large_image',
            'twitter_title' => $news['title'],
            'twitter_description' => !empty($news['lead_text']) ? $news['lead_text'] : $news['title'] . ' - বারিন্দ পোস্টে প্রকাশিত সর্বশেষ সংবাদ।',
            'twitter_image' => !empty($news['image_url']) ? get_image_url($news['image_url']) : base_url('public/logo.png')
        ];
        
        return view('public/news', $data);
    }

    public function newsByTitle($title)
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        
        // URL decode the title
        $decodedTitle = urldecode($title);
        
        // Find news by title
        $news = $newsModel->where('title', $decodedTitle)->where('status', 'published')->first();
        
        if (!$news) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Track the view
        $this->trackNewsView($news['id']);
        
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

    public function privacy()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        
        return view('public/privacy', [
            'categories' => $categories
        ]);
    }

    public function terms()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        
        return view('public/terms', [
            'categories' => $categories
        ]);
    }

    public function contact()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        
        return view('public/contact', [
            'categories' => $categories
        ]);
    }

    public function submitContact()
    {
        // Check if it's a POST request
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method']);
        }

        // Get form data
        $name = $this->request->getPost('name');
        $email = $this->request->getPost('email');
        $phone = $this->request->getPost('phone');
        $subject = $this->request->getPost('subject');
        $message = $this->request->getPost('message');
        $newsletter = $this->request->getPost('newsletter') ? 1 : 0;

        // Validate required fields
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            return $this->response->setJSON(['success' => false, 'message' => 'সব প্রয়োজনীয় তথ্য পূরণ করুন']);
        }

        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'সঠিক ইমেইল ঠিকানা দিন']);
        }

        try {
            // Save to database (you'll need to create a contacts table)
            $db = \Config\Database::connect();
            
            $data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message,
                'newsletter_subscribed' => $newsletter,
                'created_at' => date('Y-m-d H:i:s'),
                'ip_address' => $this->request->getIPAddress()
            ];

            $db->table('contacts')->insert($data);

            // Send email notification (optional)
            // $this->sendContactEmail($data);

            return $this->response->setJSON([
                'success' => true, 
                'message' => 'আপনার বার্তা সফলভাবে পাঠানো হয়েছে। আমরা শীঘ্রই আপনার সাথে যোগাযোগ করব।'
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false, 
                'message' => 'দুঃখিত, একটি সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।'
            ]);
        }
    }

    public function ads()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        
        return view('public/ads', [
            'categories' => $categories
        ]);
    }

    public function about()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        return view('public/about', ['categories' => $categories]);
    }

    public function sitemap()
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        
        // Get all published news
        $news = $newsModel->where('status', 'published')
                         ->orderBy('published_at', 'DESC')
                         ->findAll();
        
        // Get all categories
        $categories = $categoryModel->findAll();
        
        $this->response->setContentType('application/xml');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        
        // Homepage
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . base_url() . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>daily</changefreq>' . "\n";
        $xml .= '    <priority>1.0</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // Static pages
        $staticPages = ['about', 'privacy', 'terms', 'contact', 'ads'];
        foreach ($staticPages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . base_url($page) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.5</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        // Category pages
        foreach ($categories as $category) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . base_url('section/' . $category['slug']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>daily</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        // News articles
        foreach ($news as $article) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . base_url('news/' . $article['slug']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d', strtotime($article['updated_at'] ?? $article['published_at'])) . '</lastmod>' . "\n";
            $xml .= '    <changefreq>weekly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        $xml .= '</urlset>';
        
        return $this->response->setBody($xml);
    }
    
    /**
     * Track news view in the database
     */
    private function trackNewsView($newsId)
    {
        $db = \Config\Database::connect();
        
        // Get visitor's IP address
        $ipAddress = $this->request->getIPAddress();
        
        // Insert view record
        $db->table('news_views')->insert([
            'news_id' => $newsId,
            'viewed_at' => date('Y-m-d H:i:s'),
            'viewer_ip' => $ipAddress
        ]);
    }
} 