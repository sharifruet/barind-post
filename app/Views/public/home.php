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
        
        /* Prayer Times Box Styles */
        .prayer-times-box {
            background: linear-gradient(135deg, #5aa17a 0%, #2d6a4f 100%);
            color: white;
            border-radius: 14px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 6px 18px rgba(0,0,0,0.12);
            position: relative;
            overflow: hidden;
        }
        /* Decorative crescent and pattern */
        .prayer-times-box:before {
            content: "";
            position: absolute;
            top: -30px;
            right: -30px;
            width: 140px;
            height: 140px;
            background: radial-gradient(circle at 40% 40%, rgba(255,255,255,0.25) 0 35%, transparent 36% 100%);
            border-radius: 50%;
            filter: blur(1px);
            pointer-events: none;
        }
        .prayer-times-box:after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 8px;
            background-image: linear-gradient(to right, rgba(255,255,255,0.25) 20%, rgba(255,255,255,0.05) 20% 40%, rgba(255,255,255,0.25) 40% 60%, rgba(255,255,255,0.05) 60% 80%, rgba(255,255,255,0.25) 80%);
            opacity: 0.5;
        }
        .prayer-times-header {
            margin-bottom: 1rem;
        }
        .prayer-times-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0.5px;
        }
        .prayer-times-footer {
            margin-top: 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.5rem;
        }
        .prayer-area-text {
            font-size: 0.95rem;
            opacity: 0.95;
            font-weight: 500;
        }
        .settings-button {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 8px;
            padding: 0.35rem 0.6rem;
            cursor: pointer;
            line-height: 1;
        }
        .settings-button:hover {
            background: rgba(255,255,255,0.25);
        }
        .city-selector {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            padding: 0.5rem;
            border-radius: 6px;
            font-size: 0.9rem;
        }
        .city-selector option {
            background: #2d6a4f;
            color: white;
        }
        .city-selector.hidden {
            display: none;
        }
        .prayer-times-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .prayer-time-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid rgba(255,255,255,0.22);
        }
        .prayer-time-item:last-child {
            border-bottom: none;
        }
        .prayer-name {
            font-weight: 600;
            font-size: 0.95rem;
        }
        .prayer-time {
            font-weight: 700;
            font-size: 1rem;
        }
        .loading {
            text-align: center;
            padding: 1rem;
            color: rgba(255,255,255,0.9);
        }
        .error {
            text-align: center;
            padding: 1rem;
            color: #ffd1d1;
            background: rgba(255,255,255,0.12);
            border-radius: 8px;
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
    <?php if (!empty($featuredNews)): ?>
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
                        <div class="featured-hero-lead"><?= esc(limitTo15Words($firstNews['lead_text']), 'raw') ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Remaining Featured News (50% width, 2 columns) -->
            <div class="col-12 col-md-6">
                <div class="row g-3">
                    <?php foreach ($featuredNews as $news): ?>
                        <div class="col-6 col-md-6">
                            <div class="card news-card h-100 border-0 shadow-sm position-relative">
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
                                        <?= esc(limitTo15Words($news['lead_text']), 'raw') ?>
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
        foreach ($categories as $cat): 
        ?>
            <a href="/section/<?= esc($cat['slug']) ?>" class="btn btn-outline-danger category-pill px-3 py-1 mt-1">
                <?= esc($cat['name'], 'raw') ?>
            </a>
        <?php 
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
                                    <?= esc(limitTo15Words($news['lead_text']), 'raw') ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Right Column - 4 columns for news with photos -->
        <div class="col-md-4">
            <!-- Prayer Times Box -->
            <div class="prayer-times-box">
                <div class="prayer-times-header">
                    <h3 class="prayer-times-title">নামাজের সময়</h3>
                </div>
                <div id="prayerTimesContent">
                    <div class="loading">Loading prayer times...</div>
                </div>
                <div class="prayer-times-footer">
                    <span id="prayerAreaText" class="prayer-area-text">ঢাকা ও পার্শবর্তী এলাকার জন্য</span>
                    <button id="settingsButton" type="button" class="settings-button" aria-label="Change city">⚙️</button>
                </div>
                <div style="margin-top: 0.5rem;">
                    <select id="citySelector" class="city-selector hidden" aria-label="Select city">
                        <option value="">Loading cities...</option>
                    </select>
                </div>
            </div>
            
            <div class="latest-news-sidebar">
                <h4 class="mb-3 text-primary">আরও সর্বশেষ সংবাদ</h4>
                <div class="list-group list-group-flush">
                    <?php 
                    // Get additional news for right sidebar (skip the ones used in left column)
                    $rightColumnNews = array_slice($latestNews, 6, 10); // Get 15 news starting from index 6
                    
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

<script>
    // Prayer Times functionality
    let currentCityId = 1; // Default to Dhaka
    let currentCityName = 'ঢাকা';

    function updateAreaText() {
        const areaText = document.getElementById('prayerAreaText');
        if (areaText) {
            areaText.textContent = `${currentCityName} ও পার্শবর্তী এলাকার জন্য`;
        }
    }

    // Load cities for dropdown
    async function loadCities() {
        try {
            const response = await fetch('/prayer-time/cities');
            const data = await response.json();
            
            if (data.success) {
                const citySelector = document.getElementById('citySelector');
                citySelector.innerHTML = '';
                
                // Find Dhaka city and set it as default
                let dhakaCity = null;
                data.cities.forEach(city => {
                    const lower = (city.name || '').toLowerCase();
                    if (lower.includes('ঢাকা') || lower.includes('dhaka')) {
                        dhakaCity = city;
                    }
                });
                
                // If Dhaka found, set it as current city
                if (dhakaCity) {
                    currentCityId = dhakaCity.id;
                    currentCityName = dhakaCity.name;
                }

                // Build options
                data.cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.id;
                    option.textContent = city.name;
                    if (city.id == currentCityId) {
                        option.selected = true;
                    }
                    citySelector.appendChild(option);
                });

                updateAreaText();
            }
        } catch (error) {
            console.error('Error loading cities:', error);
        }
    }

    // Load prayer times for selected city
    async function loadPrayerTimes(cityId = null) {
        const content = document.getElementById('prayerTimesContent');
        content.innerHTML = '<div class="loading">Loading prayer times...</div>';
        
        try {
            const url = cityId ? `/prayer-time/today/${cityId}` : '/prayer-time/today';
            const response = await fetch(url);
            const data = await response.json();
            
            if (data.success) {
                const prayerTimes = data.prayer_times;
                // If backend returns city name, sync it
                if (data.city) {
                    currentCityName = data.city;
                    updateAreaText();
                }
                content.innerHTML = `
                    <ul class="prayer-times-list">
                        <li class="prayer-time-item">
                            <span class="prayer-name">ফজর</span>
                            <span class="prayer-time">${prayerTimes.fajr}</span>
                        </li>
                        <li class="prayer-time-item">
                            <span class="prayer-name">সূর্যোদয়</span>
                            <span class="prayer-time">${prayerTimes.sunrise}</span>
                        </li>
                        <li class="prayer-time-item">
                            <span class="prayer-name">যোহর</span>
                            <span class="prayer-time">${prayerTimes.dhuhr}</span>
                        </li>
                        <li class="prayer-time-item">
                            <span class="prayer-name">আসর</span>
                            <span class="prayer-time">${prayerTimes.asr}</span>
                        </li>
                        <li class="prayer-time-item">
                            <span class="prayer-name">মাগরিব</span>
                            <span class="prayer-time">${prayerTimes.maghrib}</span>
                        </li>
                        <li class="prayer-time-item">
                            <span class="prayer-name">এশা</span>
                            <span class="prayer-time">${prayerTimes.isha}</span>
                        </li>
                    </ul>
                `;
            } else {
                content.innerHTML = `<div class="error">${data.error || 'Prayer times not available'}</div>`;
            }
        } catch (error) {
            console.error('Error loading prayer times:', error);
            content.innerHTML = '<div class="error">Error loading prayer times</div>';
        }
    }

    // Settings toggle to show/hide selector
    document.addEventListener('click', function(e) {
        const btn = document.getElementById('settingsButton');
        const selector = document.getElementById('citySelector');
        if (!btn || !selector) return;
        if (e.target === btn) {
            selector.classList.toggle('hidden');
        }
    });

    // Handle city selection change
    document.getElementById('citySelector').addEventListener('change', function() {
        const selectedCityId = this.value;
        const selectedText = this.options[this.selectedIndex]?.text || currentCityName;
        if (selectedCityId) {
            currentCityId = selectedCityId;
            currentCityName = selectedText;
            updateAreaText();
            loadPrayerTimes(selectedCityId);
            // Collapse selector after choosing
            this.classList.add('hidden');
        }
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', async function() {
        await loadCities();
        updateAreaText();
        loadPrayerTimes(currentCityId);
    });
</script>

<?= $this->endSection() ?> 