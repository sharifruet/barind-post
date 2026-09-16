<?php
$title = 'বারিন্দ পোস্ট - গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল';
$meta_description = 'বারিন্দ পোস্ট গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল। সর্বশেষ সংবাদ, রাজনীতি, আন্তর্জাতিক, খেলাধুলা, শিক্ষা, স্বাস্থ্য ও বিজ্ঞান-প্রযুক্তি সংবাদ জানুন।';
$meta_keywords = 'বারিন্দ পোস্ট, রাজশাহী সংবাদ, বাংলাদেশ সংবাদ, অনলাইন নিউজ, বাংলা সংবাদ, সর্বশেষ খবর';

// Open Graph and Twitter Card data
$og_title = $title;
$og_description = $meta_description;
$og_type = 'website';
$og_image = base_url('public/logo.png');
$twitter_card = 'summary_large_image';
$twitter_title = $og_title;
$twitter_description = $og_description;
$twitter_image = $og_image;

// Structured data for homepage
$structured_data = [
    "@context" => "https://schema.org",
    "@type" => "WebPage",
    "name" => "বারিন্দ পোস্ট",
    "description" => $meta_description,
    "url" => base_url(),
    "mainEntity" => [
        "@type" => "NewsMediaOrganization",
        "name" => "বারিন্দ পোস্ট",
        "url" => base_url(),
        "logo" => base_url('public/logo.png'),
        "description" => "রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল"
    ]
];

$customScripts = '
<script type="application/ld+json">
' . json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '
</script>
';

// Define default colors for categories
$categoryColors = [
    'সারাদেশ' => '#dc3545',           // Red
    'রাজনীতি' => '#fd7e14',          // Orange
    'অর্থনীতি' => '#ffc107',         // Yellow
    'খেলাধুলা' => '#28a745',         // Green
    'বিনোদন' => '#6f42c1',           // Purple
    'শিক্ষা' => '#17a2b8',           // Info
    'স্বাস্থ্য' => '#e83e8c',         // Pink
    'বিজ্ঞান ও প্রযুক্তি' => '#20c997', // Teal
    'আন্তর্জাতিক' => '#6c757d',       // Secondary
    'সম্পাদকীয়' => '#343a40',        // Dark
    'লেটার টু এডিটর' => '#495057',    // Gray
    'বিশেষ প্রতিবেদন' => '#dc3545',   // Red
    'কৃষি' => '#28a745',             // Green
    'পরিবেশ' => '#20c997',           // Teal
    'নারী' => '#e83e8c',             // Pink
    'ইসলাম' => '#6f42c1',            // Purple
    'সংস্কৃতি' => '#fd7e14',          // Orange
    'ভ্রমণ' => '#17a2b8',            // Info
    'লাইফস্টাইল' => '#ffc107',        // Warning
    'ক্যাম্পাস' => '#28a745',         // Green
    'প্রবাস' => '#6c757d',            // Secondary
    'default' => '#007bff'            // Blue
];

// Function to get category color
function getCategoryColor($categoryName, $categoryColors) {
    return $categoryColors[$categoryName] ?? $categoryColors['default'];
}

// Function to limit text to first 15 words
function limitTo15Words($text) {
    if (empty($text)) return '';
    
    $words = preg_split('/\s+/', trim($text));
    if (count($words) <= 15) {
        return $text;
    }
    
    $limitedWords = array_slice($words, 0, 15);
    return implode(' ', $limitedWords) . '...';
}

$customStyles = '
        /* Featured news layout styles */
        .featured-hero {
            position: relative;
            aspect-ratio: 16/9;
            overflow: hidden;
            border-radius: 1rem;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .featured-hero:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.2);
        }
        
        .featured-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .featured-hero-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            padding: 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            z-index: 10;
        }
        
        .featured-hero-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.2;
        }
        
        .featured-hero-title a {
            color: #212529 !important;
            text-decoration: none;
            text-shadow: 2px 2px 4px rgba(255,255,255,0.8);
        }
        
        .featured-hero-title a:hover {
            color: #0d6efd !important;
            text-decoration: underline;
        }
        
        .featured-hero-lead {
            font-size: 1.1rem;
            line-height: 1.5;
            opacity: 0.95;
            color: #6c757d;
            text-shadow: 1px 1px 3px rgba(255,255,255,0.8);
        }
        
        /* Kicker styling */
        .kicker {
            font-size: 0.9em !important;
            font-weight: bold !important;
            margin-bottom: 0.5rem !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            line-height: 1.2 !important;
        }
        
        .featured-hero .kicker {
            font-size: 1rem !important;
            margin-bottom: 0.75rem !important;
        }
        
        .news-card .kicker {
            font-size: 0.8em !important;
            margin-bottom: 0.4rem !important;
        }
        
        /* Sidebar news styles */
        .latest-news-sidebar {
            background: #f8f9fa;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .latest-news-sidebar .list-group-item {
            border-left: 3px solid transparent;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
            padding: 0.5rem 0.25rem;
            font-size: 0.85em;
        }
        
        .latest-news-sidebar .list-group-item:last-child {
            border-bottom: none;
        }
        
        .latest-news-sidebar .list-group-item:hover {
            border-left-color: #007bff;
            background-color: #fff;
            transform: translateX(5px);
        }
        
        .latest-news-sidebar h4 {
            font-size: 1rem;
        }
        
        /* Most Read News Sidebar */
        .most-read-sidebar {
            background: #f8f9fa;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .most-read-sidebar .list-group-item {
            border-left: 3px solid transparent;
            border-bottom: 1px solid #e9ecef;
            transition: all 0.3s ease;
        }
        
        .most-read-sidebar .list-group-item:last-child {
            border-bottom: none;
        }
        
        .most-read-sidebar .list-group-item:hover {
            border-left-color: #dc3545;
            background-color: #fff;
            transform: translateX(5px);
        }
        
        .most-read-sidebar h6 {
            font-size: 0.9rem;
            line-height: 1.3;
            margin-bottom: 0.25rem;
        }
        
        .most-read-sidebar h6 a {
            color: #212529;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .most-read-sidebar h6 a:hover {
            color: #dc3545;
        }
        
        .most-read-sidebar small {
            font-size: 0.75rem;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .featured-hero {
                margin-bottom: 1rem;
            }
            
            .featured-hero-title {
                font-size: 1.5rem;
            }
            
            .featured-hero-lead {
                font-size: 0.9rem;
            }
            
            .featured-hero-overlay {
                padding: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            /* Featured hero maintains 16:9 aspect ratio */
            
            .featured-hero-title {
                font-size: 1.3rem;
            }
            
            .featured-hero-lead {
                font-size: 0.85rem;
            }
            
            .featured-hero-overlay {
                padding: 0.75rem;
            }
        }
';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <!-- Top Banner Ad -->
    <?php 
    /*
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    */
    ?>
    <!-- Main Content Layout: 10 columns left + 2 columns right -->
    <div class="row">
        <!-- Left Column - 10 columns for featured and latest news -->
        <div class="col-md-9">
            <!-- Featured News Section -->
            <?php if (!empty($featuredNews)): ?>
                <div class="mb-5">
                    <div class="row g-5">
                        <?php 
                        // Limit to maximum 7 featured news
                        $featuredNews = array_slice($featuredNews, 0, 7);
                        $firstNews = array_shift($featuredNews); // Get first news for hero section
                        $rightColumnNews = array_slice($featuredNews, 0, 2); // Get next 2 news for right column
                        $secondRowNews = array_slice($featuredNews, 2, 4); // Get next 4 news for second row
                        ?>
                        
                        <!-- First Row: Hero News (8 cols) + Right Column (4 cols) -->
                        <div class="row g-4 mb-4">
                            <!-- Hero Featured News (9 columns) -->
                            <div class="col-md-9">
                                <?= view('public/widgets/news_card_widget', ['news' => $firstNews, 'size' => 'hero','showKicker' => true, 'showDate' => false, 'showLead' => true ]) ?>
                            </div>
                            
                            <!-- Right Column News (3 columns) -->
                            <div class="col-md-3">
                                <div class="row g-3">
                                    <?php foreach ($rightColumnNews as $news): ?>
                                        <div class="col-12">
                                            <?= view('public/widgets/news_card_widget', [ 'news' => $news,  'size' => 'large',  'showKicker' => true,  'showDate' => false,  'showLead' => true  ]) ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Second Row: 4 News (3 columns each) -->
                        <?php if (!empty($secondRowNews)): ?>
                            <div class="row g-4">
                                <?php foreach ($secondRowNews as $news): ?>
                                    <div class="col-md-3">
                                        <?= view('public/widgets/news_card_widget', [  'news' => $news,  'size' => 'medium',  'showKicker' => true,  'showDate' => false,  'showLead' => true
                                        ]) ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Category Pills -->
            <div class="mb-4">
                <?php 
                foreach ($categories as $cat): 
                ?>
                    <a href="/section/<?= esc($cat['slug']) ?>" class="btn btn-outline-danger category-pill px-3 py-1 mt-1">
                        <?= esc($cat['name'], 'raw') ?>
                    </a>
                <?php 
                endforeach; 
                ?>
            </div>

        </div>
        
        <!-- Right Column - 2 columns for prayer times and more news -->
        <div class="col-md-3">
            <!-- Prayer Times Widget - Top Right -->
            <div class="mb-4">
                <?= view('public/widgets/prayer_times_widget') ?>
            </div>
            
            <!-- Most Read News Section -->
            <?php if (!empty($mostReadNews)): ?>
                <div class="most-read-sidebar mb-4">
                    <h4 class="mb-3 text-danger">সর্বাধিক পঠিত</h4>
                    <div class="list-group list-group-flush">
                        <?php foreach ($mostReadNews as $index => $news): ?>
                            <div class="list-group-item d-flex align-items-start p-2 border-0">
                                <div class="flex-shrink-0 me-2">
                                    <span class="badge bg-danger rounded-circle" style="width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem;">
                                        <?= $index + 1 ?>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">
                                        <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none text-dark fw-semibold">
                                            <?= esc($news['title'], 'raw') ?>
                                        </a>
                                    </h6>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
           
        </div>
    </div>

     <!-- Latest News Section -->
     <div class="row">
        <div class="col-md-12"> <h2 class="mb-4">সর্বশেষ সংবাদ</h2> </div>
        <div class="col-md-9 row g-4 mb-5">
            <?php 
            // Get news for left column
            $leftColumnNews = array_slice($latestNews, 0, 9); // Get 9 news for left column (3x3 grid)
            foreach ($leftColumnNews as $news): ?>
                <div class="col-md-4">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <?php if (!empty($news['image_url'])): ?>
                            <img src="<?= esc(get_image_url($news['image_url'])) ?>" class="card-img-top news-img" style="aspect-ratio: 16/9; object-fit: cover;" alt="<?= esc($news['image_alt_text'] ?? '') ?>">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none text-dark fw-semibold"><?= esc($news['title'], 'raw') ?></a>
                            </h5>
                            <p class="card-text small text-muted mb-1">
                                <?= date('M d, Y', strtotime($news['published_at'])) ?>
                            </p>
                            <p class="card-text">
                                <?= esc(limitTo15Words($news['lead_text']), 'raw') ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="col-md-3">
            <?php if (!empty($sportsWidgets)): ?>
                <?php foreach ($sportsWidgets as $sw): ?>
                    <?= view('public/widgets/sports_event_widget', ['event' => $sw['event'], 'matches' => $sw['matches']]) ?>
                <?php endforeach; ?>
            <?php endif; ?>
            <!-- More News Sidebar -->
            <div class="latest-news-sidebar">
                <h4 class="mb-3 text-primary">আরও পড়ুন</h4>
                <div class="list-group list-group-flush">
                    <?php 
                    // Get additional news for right sidebar (skip the ones used in left column)
                    $rightColumnNews = array_slice($latestNews, 9, 10); // Get 10 news starting from index 9
                    
                    if (!empty($rightColumnNews)): 
                        foreach ($rightColumnNews as $news): 
                            // Use the single news widget for each news item
                            echo view('public/widgets/single_news_widget', ['news' => $news]);
                        endforeach;
                    else: ?>
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-newspaper"></i>
                            <p class="mb-0">আরও সংবাদ নেই</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (!empty($categoryNews)): ?>
        <?php foreach ($categoryNews as $categorySection): ?>
            <?php if (!empty($categorySection['news'])): ?>
                <div class="mb-5 category-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 category-title">
                            <span class="category-indicator" style="background-color: <?= getCategoryColor($categorySection['category']['name'], $categoryColors) ?>;"></span>
                            <?= esc($categorySection['category']['name'], 'raw') ?>
                        </h2>
                        <a href="/section/<?= esc($categorySection['category']['slug']) ?>" class="btn btn-outline-danger btn-sm view-all-btn">
                            সব দেখুন <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="row g-4">
                        <?php foreach ($categorySection['news'] as $news): ?>
                            <div class="col-md-3">
                                <div class="card news-card h-100 border-0 shadow-sm">
                                    <?php if (!empty($news['image_url'])): ?>
                                        <img src="<?= esc(get_image_url($news['image_url'])) ?>" class="card-img-top news-img" style="aspect-ratio: 16/9; object-fit: cover;" alt="<?= esc($news['image_alt_text'] ?? '') ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none text-dark fw-semibold"><?= esc($news['title'], 'raw') ?></a>
                                        </h6>
                                        <p class="card-text small text-muted mb-1">
                                            <?= date('M d, Y', strtotime($news['published_at'])) ?>
                                        </p>
                                        <p class="card-text small">
                                            <?= esc(limitTo15Words($news['lead_text']), 'raw') ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Bottom Banner Ad -->
    <?php 
    /*
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    */
    ?>
</div>


<?= $this->endSection() ?> 