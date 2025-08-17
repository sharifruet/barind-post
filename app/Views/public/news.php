<?php
// Meta tags are now set in the controller

// Structured data for news article
$structured_data = [
    "@context" => "https://schema.org",
    "@type" => "NewsArticle",
    "headline" => $news['title'],
    "description" => $meta_description,
    "image" => $og_image,
    "author" => [
        "@type" => "Organization",
        "name" => "বারিন্দ পোস্ট"
    ],
    "publisher" => [
        "@type" => "Organization",
        "name" => "বারিন্দ পোস্ট",
        "logo" => [
            "@type" => "ImageObject",
            "url" => base_url('public/logo.png')
        ]
    ],
    "datePublished" => $news['published_at'],
    "dateModified" => $news['updated_at'] ?? $news['published_at'],
    "mainEntityOfPage" => [
        "@type" => "WebPage",
        "@id" => current_url()
    ]
];

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
        }
        .social-media-buttons {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 1rem;
            margin-bottom: 2rem;
        }
        .social-btn {
            display: block;
            width: 100%;
            padding: 1.5rem;
            margin: 1rem 0;
            border-radius: 0.75rem;
            text-decoration: none;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 2.5rem;
            line-height: 1;
        }
        .social-btn:hover {
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
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
            border: 5px solid #dc3545;
            box-shadow: 0 0 10px rgba(220, 53, 69, 0.3);
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
            content: "Loading tweet...";
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

$customScripts = '
<script type="application/ld+json">
' . json_encode($structured_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '
</script>
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
    
    <div class="row">
        <!-- Left Column - 2 columns for date, reporter info and social media -->
        <div class="col-md-2">
            <!-- Publication Date in Bangla -->
            <div class="text-center mb-3">
                <p class="text-muted small">
                    <i class="fas fa-calendar-alt"></i><br>
                    <time datetime="<?= $news['published_at'] ?>">
                        <?php 
                        $date = new DateTime($news['published_at']);
                        $banglaMonths = [
                            'January' => 'জানুয়ারি',
                            'February' => 'ফেব্রুয়ারি',
                            'March' => 'মার্চ',
                            'April' => 'এপ্রিল',
                            'May' => 'মে',
                            'June' => 'জুন',
                            'July' => 'জুলাই',
                            'August' => 'আগস্ট',
                            'September' => 'সেপ্টেম্বর',
                            'October' => 'অক্টোবর',
                            'November' => 'নভেম্বর',
                            'December' => 'ডিসেম্বর'
                        ];
                        $englishMonth = $date->format('F');
                        $banglaMonth = $banglaMonths[$englishMonth];
                        echo $date->format('d') . ' ' . $banglaMonth . ', ' . $date->format('Y');
                        ?>
                    </time>
                </p>
            </div>
            
            <!-- Reporter Badge -->
            <?php if (!empty($news['reporter'])): ?>
                <div class="reporter-badge mb-3">
                    <?= esc($news['reporter']) ?>
                </div>
            <?php endif; ?>
            
            <!-- Social Media Buttons -->
            <div class="social-media-buttons">
                <h6 class="mb-3 text-center">শেয়ার করুন</h6>
                <div>
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode(current_url()) ?>" 
                       target="_blank" class="social-btn facebook" title="ফেসবুকে শেয়ার করুন">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($news['title']) ?>" 
                       target="_blank" class="social-btn twitter" title="টুইটারে শেয়ার করুন">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="https://wa.me/?text=<?= urlencode($news['title'] . ' ' . current_url()) ?>" 
                       target="_blank" class="social-btn whatsapp" title="হোয়াটসঅ্যাপে শেয়ার করুন">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://t.me/share/url?url=<?= urlencode(current_url()) ?>&text=<?= urlencode($news['title']) ?>" 
                       target="_blank" class="social-btn telegram" title="টেলিগ্রামে শেয়ার করুন">
                        <i class="fab fa-telegram"></i>
                    </a>
                    <button onclick="copyToClipboard()" class="social-btn copy" title="লিংক কপি করুন">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Center Column - 8 columns for main news content -->
        <div class="col-md-8">
            <a href="javascript:history.back()" class="btn btn-outline-danger back-btn mb-3">&larr; Back</a>
            <article itemscope itemtype="https://schema.org/NewsArticle">
                <h1 class="mb-2 mt-3 fw-bold display-5" itemprop="headline"><?= $news['title'] ?></h1>
                <?php if (!empty($news['subtitle'])): ?>
                    <h4 class="text-muted mb-3 fw-normal" itemprop="alternativeHeadline"><?= $news['subtitle'] ?></h4>
                <?php endif; ?>
                
                <?php if (!empty($news['image_url'])): ?>
                    <div class="news-image-container">
                        <figure class="mb-3">
                            <img src="<?= esc(get_image_url($news['image_url'])) ?>" 
                                 alt="<?= esc($news['image_alt_text'] ?? $news['title']) ?>" 
                                 style="width:100%"
                                 itemprop="image">
                            <?php if (!empty($news['image_caption'])): ?>
                                <figcaption class="text-center text-muted mt-2 small">ছবিঃ <?= esc($news['image_caption']) ?></figcaption>
                            <?php endif; ?>
                        </figure>
                    </div>
                <?php endif; ?>
                
                <div class="mb-3 fs-5" itemprop="description">
                    <strong><?= $news['lead_text'] ?></strong>
                </div>
                
                <div class="mb-4 fs-5 lh-lg" itemprop="articleBody">
                    <?php 
                    // Split content into paragraphs
                    $paragraphs = explode('</p>', $news['content']);
                    $firstParagraph = $paragraphs[0] . '</p>';
                    echo $firstParagraph;
                    
                    // Show read more section after first paragraph
                    if (count($paragraphs) > 1):
                    ?>
                        <div class="read-more-section">
                            <h4 class="mb-3">আরও পড়ুন</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <?php 
                                    // Get latest news (excluding current news)
                                    $latestNews = isset($latestNews) ? $latestNews : [];
                                    $displayedCount = 0;
                                    $halfCount = ceil(count($latestNews) / 2);
                                    foreach ($latestNews as $latest):
                                        if ($latest['id'] != $news['id'] && $displayedCount < 8):
                                            $displayedCount++;
                                            if ($displayedCount <= $halfCount):
                                    ?>
                                        <div class="latest-news-title">
                                            <a href="/news/<?= esc($latest['slug']) ?>">
                                                <?= esc($latest['title']) ?>
                                            </a>
                                        </div>
                                    <?php 
                                            endif;
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                                <div class="col-md-6">
                                    <?php 
                                    $displayedCount = 0;
                                    foreach ($latestNews as $latest):
                                        if ($latest['id'] != $news['id'] && $displayedCount < 8):
                                            $displayedCount++;
                                            if ($displayedCount > $halfCount):
                                    ?>
                                        <div class="latest-news-title">
                                            <a href="/news/<?= esc($latest['slug']) ?>">
                                                <?= esc($latest['title']) ?>
                                            </a>
                                        </div>
                                    <?php 
                                            endif;
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php 
                    // Display remaining paragraphs
                    for ($i = 1; $i < count($paragraphs); $i++) {
                        if (trim($paragraphs[$i]) !== '') {
                            echo $paragraphs[$i] . '</p>';
                        }
                    }
                    ?>
                </div>
            </article>
        </div>
        
        <!-- Right Column - 2 columns for additional info -->
        <div class="col-md-2">
            <!-- Additional info can be added here -->
        </div>
    </div>
    
    <!-- Bottom Banner Ad -->
    <?php 
    $adType = 'banner';
    $adSize = 'small';
    $adText = 'বিজ্ঞাপন দিন';
    include __DIR__.'/ad_placeholder.php'; 
    ?>
</div>

<script>
function copyToClipboard() {
    navigator.clipboard.writeText(window.location.href).then(function() {
        alert('লিংক কপি হয়েছে!');
    }, function(err) {
        console.error('Could not copy text: ', err);
    });
}
</script>

<?= $this->endSection() ?> 