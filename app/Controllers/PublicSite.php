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
        
        // Get latest news for the read more section
        $latestNews = $newsModel->where('status', 'published')
                                ->where('id !=', $news['id'])
                                ->orderBy('published_at', 'DESC')
                                ->findAll(8);
        
        // Custom styles for news page
        $customStyles = '
        .news-img {
            border-radius: 1rem;
            object-fit: cover;
            width: 100%;
            height: auto;
            max-height: none;
        }
        .back-btn {
            border-radius: 2rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        .news-container {
            max-width: 100%;
        }
        .news-image-container {
            margin: 0 0 1.5rem 0;
        }
        .news-image-container img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 0;
        }
        .news-image-container figcaption {
            padding: 0;
        }
        .reporter-badge {
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
            text-align: center;
        }
        .social-media-buttons {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            width: 100%;
            box-sizing: border-box;
        }
        .social-btn {
            display: inline-block;
            width: auto;
            max-width: 100%;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 1rem;
            line-height: 1;
            min-height: 35px;
            box-sizing: border-box;
            overflow: hidden;
            word-wrap: break-word;
        }
        .social-btn:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .social-btn-container{
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            flex-wrap: wrap;
        }
        .social-btn.facebook { background: #1877f2; }
        .social-btn.twitter { background: #1da1f2; }
        .social-btn.whatsapp { background: #25d366; }
        .social-btn.telegram { background: #0088cc; }
        .social-btn.copy { background: #6c757d; }
        .social-btn.print { background: #28a745; }
        .read-more-section {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 1rem;
            margin: 0.5rem 0;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .latest-news-title {
            font-size: 0.8rem;
            margin-bottom: 0.2rem;
            padding: 0.2rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .latest-news-title:last-child {
            border-bottom: none;
        }
        .latest-news-title a {
            color: #495057;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .latest-news-title a:hover {
            color: #dc3545;
            text-decoration: underline;
        }
        /* Tweet embed styles for public view */
        .tweet-embed-container {
            margin: 20px 0;
            text-align: center;
            max-width: 100%;
            overflow: hidden;
        }
        .tweet-embed-placeholder {
            display: inline-block;
            max-width: 100%;
            min-height: 200px;
            background: #f8f9fa;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
        }
        .tweet-embed-placeholder:empty::before {
            content: \"Loading tweet...\";
            color: #657786;
            font-style: italic;
        }
        /* Twitter widget responsive styles */
        .twitter-tweet {
            margin: 0 auto !important;
            max-width: 100% !important;
        }
        /* Ensure tweets are responsive */
        .twitter-tweet-rendered {
            max-width: 100% !important;
            width: auto !important;
        }
        
        /* Print styles */
        @media print {
            .social-media-buttons,
            .read-more-section,
            .navbar,
            .footer,
            .sidebar {
                display: none !important;
            }
            .container {
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            body {
                font-size: 12pt !important;
                line-height: 1.4 !important;
                color: #000 !important;
                background: #fff !important;
            }
            h1, h2, h3, h4, h5, h6 {
                color: #000 !important;
                page-break-after: avoid;
            }
            img {
                max-width: 100% !important;
                height: auto !important;
            }
            a {
                color: #000 !important;
                text-decoration: none !important;
            }
            .news-image-container {
                text-align: center;
                margin: 20px 0;
            }
        }
        ';

        // Set meta tags for the news article
        $data = [
            'news' => $news,
            'categories' => $categories,
            'latestNews' => $latestNews,
            'customStyles' => $customStyles,
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
        
        // Custom styles for news page (same as main news method)
        $customStyles = '
        .news-img {
            border-radius: 1rem;
            object-fit: cover;
            width: 100%;
            height: auto;
            max-height: none;
        }
        .back-btn {
            border-radius: 2rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
        .news-container {
            max-width: 100%;
        }
        .news-image-container {
            margin: 0 0 1.5rem 0;
        }
        .news-image-container img {
            width: 100%;
            height: auto;
            object-fit: cover;
            border-radius: 0;
        }
        .news-image-container figcaption {
            padding: 0;
        }
        .reporter-badge {
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 1rem;
            text-align: center;
        }
        .social-media-buttons {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            width: 100%;
            box-sizing: border-box;
        }
        .social-btn {
            display: inline-block;
            width: auto;
            max-width: 100%;
            padding: 0.5rem 1rem;
            margin: 0.25rem;
            border-radius: 0.5rem;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 1rem;
            line-height: 1;
            min-height: 35px;
            box-sizing: border-box;
            overflow: hidden;
            word-wrap: break-word;
        }
        .social-btn:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        .social-btn-container{
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            width: 100%;
            max-width: 100%;
            box-sizing: border-box;
            flex-wrap: wrap;
        }
        .social-btn.facebook { background: #1877f2; }
        .social-btn.twitter { background: #1da1f2; }
        .social-btn.whatsapp { background: #25d366; }
        .social-btn.telegram { background: #0088cc; }
        .social-btn.copy { background: #6c757d; }
        .read-more-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 1rem;
            margin: 2rem 0;
            border: none;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .read-more-section h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }
        .latest-news-title {
            font-size: 0.6rem;
            margin-bottom: 0.2rem;
            padding: 0.2rem 0;
            border-bottom: 1px solid #e9ecef;
        }
        .latest-news-title:last-child {
            border-bottom: none;
        }
        .latest-news-title a {
            color: #495057;
            text-decoration: none;
            transition: color 0.2s ease;
        }
        .latest-news-title a:hover {
            color: #dc3545;
            text-decoration: underline;
        }
        /* Tweet embed styles for public view */
        .tweet-embed-container {
            margin: 20px 0;
            text-align: center;
            max-width: 100%;
            overflow: hidden;
        }
        .tweet-embed-placeholder {
            display: inline-block;
            max-width: 100%;
            min-height: 200px;
            background: #f8f9fa;
            border: 1px solid #e1e8ed;
            border-radius: 8px;
            padding: 20px;
            margin: 10px 0;
        }
        .tweet-embed-placeholder:empty::before {
            content: \"Loading tweet...\";
            color: #657786;
            font-style: italic;
        }
        /* Twitter widget responsive styles */
        .twitter-tweet {
            margin: 0 auto !important;
            max-width: 100% !important;
        }
        /* Ensure tweets are responsive */
        .twitter-tweet-rendered {
            max-width: 100% !important;
            width: auto !important;
        }
        ';
        
        // Set meta tags for the news article
        $data = [
            'news' => $news, 
            'categories' => $categories,
            'customStyles' => $customStyles,
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
        $staticPages = ['about', 'privacy', 'terms', 'contact', 'ads', 'rss-info'];
        foreach ($staticPages as $page) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . base_url($page) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>monthly</changefreq>' . "\n";
            $xml .= '    <priority>0.5</priority>' . "\n";
            $xml .= '  </url>' . "\n";
        }
        
        // RSS feeds
        $xml .= '  <url>' . "\n";
        $xml .= '    <loc>' . base_url('rss') . '</loc>' . "\n";
        $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
        $xml .= '    <changefreq>hourly</changefreq>' . "\n";
        $xml .= '    <priority>0.8</priority>' . "\n";
        $xml .= '  </url>' . "\n";
        
        // Category pages
        foreach ($categories as $category) {
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . base_url('section/' . $category['slug']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>daily</changefreq>' . "\n";
            $xml .= '    <priority>0.8</priority>' . "\n";
            $xml .= '  </url>' . "\n";
            
            // Category RSS feeds
            $xml .= '  <url>' . "\n";
            $xml .= '    <loc>' . base_url('rss/category/' . $category['slug']) . '</loc>' . "\n";
            $xml .= '    <lastmod>' . date('Y-m-d') . '</lastmod>' . "\n";
            $xml .= '    <changefreq>hourly</changefreq>' . "\n";
            $xml .= '    <priority>0.7</priority>' . "\n";
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

    /**
     * Generate RSS feed for all news
     */
    public function rss()
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        
        // Get latest published news (last 50 articles)
        $news = $newsModel->where('status', 'published')
                         ->orderBy('published_at', 'DESC')
                         ->findAll(50);
        
        // Set proper headers for RSS
        $this->response->setContentType('application/rss+xml; charset=UTF-8');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>বারিন্দ পোস্ট - সর্বশেষ সংবাদ</title>' . "\n";
        $xml .= '    <link>' . base_url() . '</link>' . "\n";
        $xml .= '    <description>রাজশাহী ও বাংলাদেশের সর্বশেষ সংবাদ। বারিন্দ পোস্টে প্রকাশিত সব খবর জানুন।</description>' . "\n";
        $xml .= '    <language>bn</language>' . "\n";
        $xml .= '    <lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        $xml .= '    <atom:link href="' . base_url('rss') . '" rel="self" type="application/rss+xml" />' . "\n";
        
        foreach ($news as $article) {
            // Get category name
            $category = $categoryModel->find($article['category_id']);
            $categoryName = $category ? $category['name'] : '';
            
            // Clean content for RSS (remove HTML tags, limit length)
            $content = strip_tags($article['content']);
            $content = mb_substr($content, 0, 500) . (mb_strlen($content) > 500 ? '...' : '');
            
            // Format date
            $pubDate = date('r', strtotime($article['published_at']));
            
            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . htmlspecialchars($article['title'], ENT_XML1, 'UTF-8') . '</title>' . "\n";
            $xml .= '      <link>' . base_url('news/' . $article['slug']) . '</link>' . "\n";
            $xml .= '      <guid>' . base_url('news/' . $article['slug']) . '</guid>' . "\n";
            $xml .= '      <pubDate>' . $pubDate . '</pubDate>' . "\n";
            $xml .= '      <category>' . htmlspecialchars($categoryName, ENT_XML1, 'UTF-8') . '</category>' . "\n";
            $xml .= '      <description>' . htmlspecialchars($content, ENT_XML1, 'UTF-8') . '</description>' . "\n";
            
            // Add image if available
            if (!empty($article['image_url'])) {
                $xml .= '      <enclosure url="' . base_url($article['image_url']) . '" type="image/jpeg" />' . "\n";
            }
            
            $xml .= '    </item>' . "\n";
        }
        
        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';
        
        return $this->response->setBody($xml);
    }

    /**
     * Show RSS feed information page
     */
    public function rssInfo()
    {
        $categoryModel = new CategoryModel();
        $categories = $categoryModel->findAll();
        return view('public/rss_info', ['categories' => $categories]);
    }

    /**
     * Generate RSS feed for specific category
     */
    public function rssCategory($slug)
    {
        $newsModel = new NewsModel();
        $categoryModel = new CategoryModel();
        
        // Get category
        $category = $categoryModel->where('slug', $slug)->first();
        if (!$category) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }
        
        // Get latest published news from this category (last 30 articles)
        $news = $newsModel->where('category_id', $category['id'])
                         ->where('status', 'published')
                         ->orderBy('published_at', 'DESC')
                         ->findAll(30);
        
        // Set proper headers for RSS
        $this->response->setContentType('application/rss+xml; charset=UTF-8');
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . htmlspecialchars($category['name'], ENT_XML1, 'UTF-8') . ' - বারিন্দ পোস্ট</title>' . "\n";
        $xml .= '    <link>' . base_url('section/' . $category['slug']) . '</link>' . "\n";
        $xml .= '    <description>' . htmlspecialchars($category['name'], ENT_XML1, 'UTF-8') . ' বিভাগের সর্বশেষ সংবাদ। বারিন্দ পোস্টে প্রকাশিত ' . htmlspecialchars($category['name'], ENT_XML1, 'UTF-8') . ' সম্পর্কিত সব খবর জানুন।</description>' . "\n";
        $xml .= '    <language>bn</language>' . "\n";
        $xml .= '    <lastBuildDate>' . date('r') . '</lastBuildDate>' . "\n";
        $xml .= '    <atom:link href="' . base_url('rss/category/' . $category['slug']) . '" rel="self" type="application/rss+xml" />' . "\n";
        
        foreach ($news as $article) {
            // Clean content for RSS (remove HTML tags, limit length)
            $content = strip_tags($article['content']);
            $content = mb_substr($content, 0, 500) . (mb_strlen($content) > 500 ? '...' : '');
            
            // Format date
            $pubDate = date('r', strtotime($article['published_at']));
            
            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . htmlspecialchars($article['title'], ENT_XML1, 'UTF-8') . '</title>' . "\n";
            $xml .= '      <link>' . base_url('news/' . $article['slug']) . '</link>' . "\n";
            $xml .= '      <guid>' . base_url('news/' . $article['slug']) . '</guid>' . "\n";
            $xml .= '      <pubDate>' . $pubDate . '</pubDate>' . "\n";
            $xml .= '      <category>' . htmlspecialchars($category['name'], ENT_XML1, 'UTF-8') . '</category>' . "\n";
            $xml .= '      <description>' . htmlspecialchars($content, ENT_XML1, 'UTF-8') . '</description>' . "\n";
            
            // Add image if available
            if (!empty($article['image_url'])) {
                $xml .= '      <enclosure url="' . base_url($article['image_url']) . '" type="image/jpeg" />' . "\n";
            }
            
            $xml .= '    </item>' . "\n";
        }
        
        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';
        
        return $this->response->setBody($xml);
    }
} 