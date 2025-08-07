<?php
$title = 'বারিন্দ পোস্ট - গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল';
$meta_description = 'বারিন্দ পোস্ট গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল। সর্বশেষ সংবাদ, রাজনীতি, অর্থনীতি, খেলাধুলা, বিনোদন ও অন্যান্য গুরুত্বপূর্ণ খবর জানুন।';
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
    'জাতীয়' => '#dc3545',      // Red
    'রাজনীতি' => '#fd7e14',    // Orange
    'অর্থনীতি' => '#ffc107',   // Yellow
    'আন্তর্জাতিক' => '#20c997', // Teal
    'খেলাধুলা' => '#28a745',   // Green
    'বিনোদন' => '#6f42c1',     // Purple
    'default' => '#007bff'      // Blue
];

// Function to get category color
function getCategoryColor($categoryName, $categoryColors) {
    return $categoryColors[$categoryName] ?? $categoryColors['default'];
}

$customStyles = '
        .category-pill {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }
        .hero {
            background: linear-gradient(90deg, #f8fafc 60%, #e9ecef 100%);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem 2rem 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .featured-img {
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            object-fit: cover;
            height: 280px;
        }
        .featured-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #dc3545;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 10;
        }
        .category-section {
            border-top: 2px solid #f8f9fa;
            padding-top: 2rem;
        }
        .category-title {
            color: #2c3e50;
            font-weight: 700;
            border-left: 4px solid #007bff;
            padding-left: 1rem;
        }
        .view-all-btn {
            transition: all 0.3s ease;
        }
        .view-all-btn:hover {
            transform: translateX(5px);
        }
        .single-featured {
            max-width: 800px;
            margin: 0 auto;
        }
        .single-featured .card {
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
        }
        .single-featured .featured-img {
            height: 400px;
        }
        
        /* Featured news layout styles */
        .featured-hero {
            position: relative;
            height: 500px;
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
        
        /* Ensure inline styles work on overlay */
        .featured-hero-overlay[style*="background-color"] {
            z-index: 15 !important;
        }
        
        /* Alternative: Use CSS class for background colors */
        .featured-hero-overlay.bg-beige {
            background-color: beige !important;
            z-index: 15 !important;
        }
        
        .featured-hero-overlay.bg-light {
            background-color: #f8f9fa !important;
            z-index: 15 !important;
        }
        
        .featured-hero-lead {
            font-size: 1.1rem;
            line-height: 1.5;
            opacity: 0.95;
            color: #6c757d;
            text-shadow: 1px 1px 3px rgba(255,255,255,0.8);
        }
        
        /* Responsive adjustments for featured hero */
        @media (max-width: 768px) {
            .featured-hero {
                height: 300px;
                margin-bottom: 1rem;
            }
            
            .featured-hero-title {
                font-size: 1.5rem;
            }
            
            .featured-hero-title a {
                font-size: 1.5rem;
            }
            
            .featured-hero-lead {
                font-size: 0.9rem;
            }
            
            .featured-hero-overlay {
                padding: 1rem;
                justify-content: center;
            }
        }
        
        @media (max-width: 576px) {
            .featured-hero {
                height: 250px;
            }
            
            .featured-hero-title {
                font-size: 1.3rem;
            }
            
            .featured-hero-title a {
                font-size: 1.3rem;
            }
            
            .featured-hero-lead {
                font-size: 0.85rem;
            }
            
            .featured-hero-overlay {
                padding: 0.75rem;
            }
        }
        
        .featured-sidebar {
            height: 500px;
            overflow-y: auto;
        }
        
        .featured-sidebar-card {
            height: 240px;
            margin-bottom: 1rem;
            border-radius: 0.75rem;
            overflow: hidden;
            position: relative;
        }
        
        .featured-sidebar-card:last-child {
            margin-bottom: 0;
        }
        
        .featured-sidebar-card img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .featured-sidebar-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0,0,0,0.8));
            padding: 1rem;
            color: white;
        }
        
        .featured-sidebar-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }
        
        .featured-sidebar-lead {
            font-size: 0.9rem;
            line-height: 1.4;
            opacity: 0.9;
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
        }
        
        .latest-news-sidebar .list-group-item:last-child {
            border-bottom: none;
        }
        
        .latest-news-sidebar .list-group-item:hover {
            border-left-color: #007bff;
            background-color: #fff;
            transform: translateX(5px);
        }
        
        /* Column borders for news items */
        .news-card {
            border-right: 1px solid #e9ecef !important;
            border-bottom: 1px solid #e9ecef !important;
        }
        
        /* Remove right border from last column in each row */
        .col-md-4:nth-child(3n) .news-card {
            border-right: none !important;
        }
        
        /* Remove bottom border from last row */
        .row:last-child .news-card {
            border-bottom: none !important;
        }
        
        .latest-news-sidebar .badge {
            font-size: 0.7rem;
            min-width: 20px;
        }
        
        /* Add 1px border to left column news cards */
        .col-md-8 .news-card {
            border: 1px solid #dee2e6 !important;
        }
';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container">
    <!-- Top Banner Ad -->
    <?php 
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    ?>
    <?php if (!empty($featuredNews)): ?>
        <h2 class="mb-4">বিশেষ সংবাদ</h2>
        <div class="row g-4 mb-5">
            <?php 
            $firstNews = array_shift($featuredNews); // Get first news for hero section
            ?>
            
            <!-- Hero Featured News (50% width) -->
            <div class="col-12 col-md-6">
                <div class="featured-hero">
                    <?php if (!empty($firstNews['image_url'])): ?>
                        <img src="<?= esc(get_image_url($firstNews['image_url'])) ?>" style="width:100%;"  alt="<?= esc($firstNews['image_alt_text'] ?? '') ?>">
                    <?php endif; ?>
                    <div class="featured-hero-overlay">
                        <h3 class="featured-hero-title">
                            <a href="/news/<?= esc($firstNews['slug']) ?>" class="text-decoration-none text-dark"><?= esc($firstNews['title'], 'raw') ?></a>
                        </h3>
                        <div class="featured-hero-lead"><?= esc($firstNews['lead_text'], 'raw') ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Remaining Featured News (50% width, 2 columns) -->
            <div class="col-12 col-md-6">
                <div class="row g-3">
                    <?php foreach ($featuredNews as $news): ?>
                        <div class="col-6 col-md-6">
                            <div class="card news-card h-100 border-0 shadow-sm position-relative">
                                <div class="featured-badge">বিশেষ</div>
                                <?php if (!empty($news['image_url'])): ?>
                                    <img src="<?= esc(get_image_url($news['image_url'])) ?>" class="card-img-top featured-img" alt="<?= esc($news['image_alt_text'] ?? '') ?>">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none text-dark fw-semibold"><?= esc($news['title'], 'raw') ?></a>
                                    </h5>
                                    <p class="card-text small text-muted mb-1">
                                        <?= date('M d, Y', strtotime($news['published_at'])) ?>
                                    </p>
                                    <p class="card-text">
                                        <?= esc($news['lead_text'], 'raw') ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>


    <div class="mb-4">
        <?php 
        // Define important categories to show on homepage
        $importantCategories = ['জাতীয়', 'রাজনীতি', 'অর্থনীতি', 'আন্তর্জাতিক', 'খেলাধুলা', 'বিনোদন', 'শিক্ষা', 'স্বাস্থ্য', 'প্রযুক্তি', 'পরিবেশ', 'কৃষি', 'সংস্কৃতি', 'ধর্ম'];
        
        foreach ($categories as $cat): 
            if (in_array($cat['name'], $importantCategories)):
        ?>
            <a href="/section/<?= esc($cat['slug']) ?>" class="btn btn-outline-danger category-pill px-3 py-1 mt-1">
                <?= esc($cat['name'], 'raw') ?>
            </a>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>
    <h2 class="mb-4">সর্বশেষ সংবাদ</h2>
    <div class="row">
        <!-- Left Column - 8 columns for news with photos -->
        <div class="col-md-8">
            <div class="row g-4 mb-5">
                <?php 
                // Get news for left column (starting from first news since hero is removed)
                $leftColumnNews = array_slice($latestNews, 0, 6); // Get 6 news for left column
                foreach ($leftColumnNews as $news): ?>
                    <div class="col-md-4">
                        <div class="card news-card h-100 border-0 shadow-sm">
                            <?php if (!empty($news['image_url'])): ?>
                                <img src="<?= esc(get_image_url($news['image_url'])) ?>" class="card-img-top news-img" alt="<?= esc($news['image_alt_text'] ?? '') ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none text-dark fw-semibold"><?= esc($news['title'], 'raw') ?></a>
                                </h5>
                                <p class="card-text small text-muted mb-1">
                                    <?= date('M d, Y', strtotime($news['published_at'])) ?>
                                </p>
                                <p class="card-text">
                                    <?= esc($news['lead_text'], 'raw') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Column - 4 columns for news titles only -->
        <div class="col-md-4">
            <div class="latest-news-sidebar">
                <h4 class="mb-3 text-primary">আরও সর্বশেষ সংবাদ</h4>
                <div class="list-group list-group-flush">
                                                    <?php 
                // Get additional news for right sidebar (skip the ones used in left column)
                $rightColumnNews = array_slice($latestNews, 6, 15); // Get 15 news starting from index 6
                if (!empty($rightColumnNews)): ?>
                    <?php foreach ($rightColumnNews as $index => $news): ?>
                        <a href="/news/<?= esc($news['slug']) ?>" class="list-group-item list-group-item-action border-0 px-0 py-2">
                            <div class="d-flex align-items-start">
                                <span class="badge bg-secondary me-2 mt-1"><?= $index + 1 ?></span>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-semibold text-dark"><?= esc($news['title'], 'raw') ?></h6>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                                    <?php else: ?>
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
                                        <img src="<?= esc(get_image_url($news['image_url'])) ?>" class="card-img-top news-img" alt="<?= esc($news['image_alt_text'] ?? '') ?>">
                                    <?php endif; ?>
                                    <div class="card-body">
                                        <h6 class="card-title">
                                            <a href="/news/<?= esc($news['slug']) ?>" class="text-decoration-none text-dark fw-semibold"><?= esc($news['title'], 'raw') ?></a>
                                        </h6>
                                        <p class="card-text small text-muted mb-1">
                                            <?= date('M d, Y', strtotime($news['published_at'])) ?>
                                        </p>
                                        <p class="card-text small">
                                            <?= esc(mb_substr($news['lead_text'], 0, 80) . '...', 'raw') ?>
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
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    ?>
</div>
<?= $this->endSection() ?> 