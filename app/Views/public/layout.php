<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <script>
    // CSRF token header for same-origin non-GET fetch()/XHR (contact form AJAX etc.).
    // Forms carry csrf_field(); the view-count beacon is excluded server-side.
    (function () {
        var t = document.querySelector('meta[name="csrf-token"]'), h = document.querySelector('meta[name="csrf-header"]');
        if (!t || !h) return;
        var token = t.content, header = h.content;
        var sameOrigin = function (u) { u = String(u || ''); return !/^[a-z][a-z0-9+.-]*:\/\//i.test(u) || u.indexOf(location.origin) === 0; };
        var mutating = function (m) { m = String(m || 'GET').toUpperCase(); return m !== 'GET' && m !== 'HEAD' && m !== 'OPTIONS'; };
        var origFetch = window.fetch;
        window.fetch = function (input, init) {
            init = init || {};
            var url = typeof input === 'string' ? input : (input && input.url) || '';
            var method = init.method || (input && input.method) || 'GET';
            if (mutating(method) && sameOrigin(url)) {
                var headers = new Headers(init.headers || (typeof Request !== 'undefined' && input instanceof Request ? input.headers : undefined));
                if (!headers.has(header)) headers.set(header, token);
                init.headers = headers;
            }
            return origFetch.call(this, input, init);
        };
        var open = XMLHttpRequest.prototype.open, send = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function (m, u) { this.__csrf = mutating(m) && sameOrigin(u); return open.apply(this, arguments); };
        XMLHttpRequest.prototype.send = function () { if (this.__csrf) { try { this.setRequestHeader(header, token); } catch (e) {} } return send.apply(this, arguments); };
    })();
    </script>

    <!-- SEO Meta Tags -->
    <title><?= isset($title) ? esc($title) : 'বারিন্দ পোস্ট - গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল' ?></title>
    <meta name="description" content="<?= isset($meta_description) ? esc($meta_description) : 'বারিন্দ পোস্ট গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল। সর্বশেষ সংবাদ, রাজনীতি, আন্তর্জাতিক, খেলাধুলা, শিক্ষা, স্বাস্থ্য ও বিজ্ঞান-প্রযুক্তি সংবাদ জানুন।' ?>">
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
    <meta property="og:title" content="<?= isset($og_title) ? esc($og_title) : (isset($title) ? esc($title) : 'বারিন্দ পোস্ট - গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল') ?>">
    <meta property="og:description" content="<?= isset($og_description) ? esc($og_description) : (isset($meta_description) ? esc($meta_description) : 'বারিন্দ পোস্ট গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল।') ?>">
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
    <meta name="twitter:title" content="<?= isset($twitter_title) ? esc($twitter_title) : (isset($title) ? esc($title) : 'বারিন্দ পোস্ট - গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল') ?>">
    <meta name="twitter:description" content="<?= isset($twitter_description) ? esc($twitter_description) : (isset($meta_description) ? esc($meta_description) : 'বারিন্দ পোস্ট গোদাগাড়ী, রাজশাহীর থেকে পরিচালিত একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল।') ?>">
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
    <link rel="icon" type="image/x-icon" href="<?= asset_url('favicon.ico') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset_url('apple-touch-icon.png') ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?= asset_url('favicon-32x32.png') ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?= asset_url('favicon-16x16.png') ?>">
    
    <!-- Google Analytics 4 -->
    <?php if (isset($_ENV['GA4_MEASUREMENT_ID']) && !empty($_ENV['GA4_MEASUREMENT_ID'])): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= $_ENV['GA4_MEASUREMENT_ID'] ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= $_ENV['GA4_MEASUREMENT_ID'] ?>', {
            'custom_map': {'custom_parameter_1': 'language'},
            'language': 'bn'
        });
    </script>
    <?php endif; ?>
    
    <!-- Google Search Console Verification -->
    <?php if (isset($_ENV['GOOGLE_SITE_VERIFICATION']) && !empty($_ENV['GOOGLE_SITE_VERIFICATION'])): ?>
    <meta name="google-site-verification" content="<?= $_ENV['GOOGLE_SITE_VERIFICATION'] ?>">
    <?php endif; ?>
    
    <!-- Google AdSense -->
    <?php if (isset($_ENV['GOOGLE_ADSENSE_ID']) && !empty($_ENV['GOOGLE_ADSENSE_ID'])): ?>
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?= $_ENV['GOOGLE_ADSENSE_ID'] ?>" crossorigin="anonymous"></script>
    <?php endif; ?>
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons (CSP compatible) -->
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Bengali Fonts: serif for headlines, sans for UI and body -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&family=Noto+Serif+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Editorial theme -->
    <link href="<?= asset_url('assets/css/theme.css') ?>" rel="stylesheet">

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
            "addressLocality": "মহিশালবাড়ী, গোদাগাড়ী, রাজশাহী",
            "addressCountry": "BD"
        },
        "contactPoint": {
            "@type": "ContactPoint",
            "contactType": "customer service",
            "email": "info@barindpost.com"
        },
        "sameAs": [
            "https://facebook.com/barindpost",
            "https://instagram.com/barindpost",
            "https://x.com/BarindPost"
        ]
    }
    </script>
    
    <style>
        /* Page-specific overrides supplied by individual views */
        <?= $customStyles ?? '' ?>
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
    
    <!-- Twitter Widget Script -->
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    
    <script>
        /*
         * Image loading is handled by the browser's native loading="lazy" on the
         * img tags themselves. A previous custom IntersectionObserver hid every
         * lazy image with opacity:0 until it fired, which meant any JS failure
         * left the page with no images at all — removed in favour of the native
         * behaviour.
         */

        /**
         * Collapse image slots whose source fails to load. Articles can carry
         * external image URLs (including ones ingested automatically), so dead
         * images are expected rather than exceptional — a card without a photo
         * reads far better than a broken-image box.
         * Uses capture, since `error` on <img> does not bubble.
         */
        document.addEventListener('error', function (e) {
            var el = e.target;
            if (!el || el.tagName !== 'IMG') return;
            var slot = el.closest('.story__media, .article__figure');
            if (slot) {
                slot.classList.add('is-broken-media');
            } else {
                el.classList.add('is-broken-media');
            }
        }, true);
    </script>
    
    <?= isset($customScripts) ? $customScripts : '' ?>
</body>
</html> 