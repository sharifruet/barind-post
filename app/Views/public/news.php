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
        <!-- Center Column - 12 columns for main news content -->
        <div class="col-md-12">
            <a href="javascript:history.back()" class="btn btn-outline-danger back-btn mb-3">&larr; Back</a>
            <article itemscope itemtype="https://schema.org/NewsArticle">
                <h1 class="mb-2 mt-3 fw-bold display-5" itemprop="headline"><?= $news['title'] ?></h1>
                <?php if (!empty($news['subtitle'])): ?>
                    <h4 class="text-muted mb-3 fw-normal" itemprop="alternativeHeadline"><?= $news['subtitle'] ?></h4>
                <?php endif; ?>
                
                <!-- Publication Date, Reporter Badge, and Social Media Buttons -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <!-- Reporter Badge -->
                        <?php if (!empty($news['reporterRole'])): ?>
                           <h6><?= esc($news['reporterRole']) ?></h6>
                        <?php endif; ?>
                                                 <time datetime="<?= $news['published_at'] ?>">
                             প্রকাশঃ <?= format_bangla_date($news['published_at'], false, true) ?>
                         </time>
                    </div>
                    
                    <div class="col-md-6">
                        <!-- Social Media Buttons -->
                        <div class="social-media-buttons">
                            <div class="social-btn-container">
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
                                <button onclick="window.print()" class="social-btn print" title="প্রিন্ট করুন">
                                    <i class="fas fa-print"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
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