<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= isset($title) ? esc($title) : 'বারিন্দ পোস্ট - রাজশাহীর শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল' ?></title>
    <meta name="description" content="<?= isset($meta_description) ? esc($meta_description) : 'বারিন্দ পোস্ট রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল। সর্বশেষ সংবাদ, রাজনীতি, অর্থনীতি, খেলাধুলা, বিনোদন ও অন্যান্য গুরুত্বপূর্ণ খবর জানুন।' ?>">
    <meta name="keywords" content="<?= isset($meta_keywords) ? esc($meta_keywords) : 'বারিন্দ পোস্ট, রাজশাহী সংবাদ, বাংলাদেশ সংবাদ, অনলাইন নিউজ, বাংলা সংবাদ' ?>">
    <meta name="author" content="বারিন্দ পোস্ট">
    <meta name="robots" content="index, follow">
    <meta name="language" content="bn">
    <meta name="revisit-after" content="1 days">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="<?= current_url() ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?= isset($og_title) ? esc($og_title) : (isset($title) ? esc($title) : 'বারিন্দ পোস্ট') ?>">
    <meta property="og:description" content="<?= isset($og_description) ? esc($og_description) : (isset($meta_description) ? esc($meta_description) : 'বারিন্দ পোস্ট রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল।') ?>">
    <meta property="og:type" content="<?= isset($og_type) ? esc($og_type) : 'website' ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:site_name" content="বারিন্দ পোস্ট">
    <meta property="og:locale" content="bn_BD">
    <?php if (isset($og_image)): ?>
    <meta property="og:image" content="<?= esc($og_image) ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="<?= isset($twitter_card) ? esc($twitter_card) : 'summary_large_image' ?>">
    <meta name="twitter:title" content="<?= isset($twitter_title) ? esc($twitter_title) : (isset($title) ? esc($title) : 'বারিন্দ পোস্ট') ?>">
    <meta name="twitter:description" content="<?= isset($twitter_description) ? esc($twitter_description) : (isset($meta_description) ? esc($meta_description) : 'বারিন্দ পোস্ট রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল।') ?>">
    <?php if (isset($og_image)): ?>
    <meta name="twitter:image" content="<?= esc($og_image) ?>">
    <?php endif; ?>
    
    <!-- Additional SEO Meta Tags -->
    <meta name="theme-color" content="#dc3545">
    <meta name="msapplication-TileColor" content="#dc3545">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="বারিন্দ পোস্ট">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons (CSP compatible) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bengali Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "NewsMediaOrganization",
        "name": "বারিন্দ পোস্ট",
        "url": "<?= base_url() ?>",
        "logo": "<?= base_url('public/logo.png') ?>",
        "description": "রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল",
        "foundingDate": "2024",
        "address": {
            "@type": "PostalAddress",
            "addressLocality": "রাজশাহী",
            "addressCountry": "BD"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "email": "info@barindpost.com"
        },
        "sameAs": [
            "https://www.facebook.com/barindpost"
        ]
    }
    </script>
    
    <style>
        /* Bengali font support */
        body {
            font-family: 'Noto Sans Bengali', 'Noto Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        
        .bengali-text {
            font-family: 'Noto Sans Bengali', 'Noto Sans', sans-serif;
        }
        
        /* Common styles */
        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        
        .news-card {
            transition: box-shadow 0.2s, transform 0.2s;
            border-radius: 1rem;
        }
        
        .news-card:hover {
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            transform: translateY(-4px) scale(1.01);
        }
        
        .news-img {
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            object-fit: cover;
            height: 220px;
        }
        
        .footer {
            background: #f8f9fa;
            color: #333;
            padding: 2rem 0 1rem 0;
            margin-top: 3rem;
            border-top: 1px solid #dee2e6;
        }
        
        .footer-link {
            color: #333 !important;
            transition: color 0.3s ease;
        }
        
        .footer-link:hover {
            color: #dc3545 !important;
            text-decoration: underline !important;
        }
        
        /* Ad placeholder styles */
        .ad-placeholder {
            background: linear-gradient(45deg, #f8f9fa 25%, transparent 25%), 
                        linear-gradient(-45deg, #f8f9fa 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #f8f9fa 75%), 
                        linear-gradient(-45deg, transparent 75%, #f8f9fa 75%);
            background-size: 20px 20px;
            background-position: 0 0, 0 10px, 10px -10px, -10px 0px;
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .ad-placeholder:hover {
            border-color: #007bff;
            color: #007bff;
            background: linear-gradient(45deg, #e3f2fd 25%, transparent 25%), 
                        linear-gradient(-45deg, #e3f2fd 25%, transparent 25%), 
                        linear-gradient(45deg, transparent 75%, #e3f2fd 75%), 
                        linear-gradient(-45deg, transparent 75%, #e3f2fd 75%);
        }
        
        .ad-banner {
            height: 90px;
            margin: 1rem 0;
        }
        
        .ad-sidebar {
            height: 250px;
            margin-bottom: 1rem;
        }
        
        .ad-inline {
            height: 120px;
            margin: 2rem 0;
        }
        
        .ad-sticky {
            position: sticky;
            top: 100px;
        }
        
        /* Category color indicators */
        .category-indicator {
            display: inline-block;
            width: 40px;
            height: 1.2em;
            border-radius: 2px;
            margin-right: 8px;
            vertical-align: middle;
        }
        
        .category-pill .category-indicator {
            width: 40px;
            height: 1.2em;
            margin-right: 6px;
        }
        
        /* Additional custom styles can be added here */
        <?= isset($customStyles) ? $customStyles : '' ?>
    </style>
</head>
<body>
    <?php include __DIR__.'/header.php'; ?>
    
    <main>
        <?= $this->renderSection('content') ?>
    </main>
    
    <?php include __DIR__.'/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?= isset($customScripts) ? $customScripts : '' ?>
</body>
</html> 