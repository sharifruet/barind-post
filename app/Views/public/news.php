<?php
$title = $news['title'] . ' - বারিন্দ পোস্ট';
$meta_description = !empty($news['lead_text']) ? $news['lead_text'] : $news['title'] . ' - বারিন্দ পোস্টে প্রকাশিত সর্বশেষ সংবাদ।';
$meta_keywords = 'বারিন্দ পোস্ট, ' . $news['title'] . ', রাজশাহী সংবাদ, বাংলাদেশ সংবাদ';

// Open Graph and Twitter Card data
$og_title = $news['title'];
$og_description = $meta_description;
$og_type = 'article';
$og_image = !empty($news['image_url']) ? get_image_url($news['image_url']) : base_url('public/logo.png');
$twitter_card = 'summary_large_image';
$twitter_title = $og_title;
$twitter_description = $og_description;
$twitter_image = $og_image;

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
        <div class="row justify-content-center">
        <div class="col-md-8 news-container">
            <a href="javascript:history.back()" class="btn btn-outline-danger back-btn mb-3">&larr; Back</a>
            <article itemscope itemtype="https://schema.org/NewsArticle">
                <h1 class="mb-2 mt-3 fw-bold display-5" itemprop="headline"><?= $news['title'] ?></h1>
                <?php if (!empty($news['subtitle'])): ?>
                    <h4 class="text-muted mb-3 fw-normal" itemprop="alternativeHeadline"><?= $news['subtitle'] ?></h4>
                <?php endif; ?>
                <p class="text-muted small mb-2">
                    <time itemprop="datePublished" datetime="<?= $news['published_at'] ?>"><?= date('M d, Y', strtotime($news['published_at'])) ?></time>
                    <?php if (!empty($news['source'])): ?>
                        <span class="ms-2">| সূত্রঃ <span itemprop="sourceOrganization"><?= esc($news['source']) ?></span></span>
                    <?php endif; ?>
                </p>
                
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
                    <?= $news['content'] ?>
                </div>
            </article>
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
<?= $this->endSection() ?> 