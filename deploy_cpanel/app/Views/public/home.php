<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>বারিন্দ পোস্ট - হোম</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .category-pill {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
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
        .featured-img {
            border-top-left-radius: 1rem;
            border-top-right-radius: 1rem;
            object-fit: cover;
            height: 280px;
        }
        .hero {
            background: linear-gradient(90deg, #f8fafc 60%, #e9ecef 100%);
            border-radius: 1.5rem;
            padding: 2.5rem 2rem 2rem 2rem;
            margin-bottom: 2.5rem;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
        }
        .footer {
            background: #222;
            color: #fff;
            padding: 2rem 0 1rem 0;
            margin-top: 3rem;
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
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/header.php'; ?>
    <div class="container">
        <?php if (!empty($featuredNews)): ?>
            <h2 class="mb-4">বিশেষ সংবাদ</h2>
            <div class="row g-4 mb-5">
                <?php foreach ($featuredNews as $news): ?>
                    <div class="col-md-4">
                        <div class="card news-card h-100 border-0 shadow-sm position-relative">
                            <div class="featured-badge">বিশেষ</div>
                            <?php if (!empty($news['image_url'])): ?>
                                <img src="<?= esc($news['image_url']) ?>" class="card-img-top featured-img" alt="">
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
        <?php endif; ?>

        <?php if (!empty($latestNews)): ?>
            <?php $hero = $latestNews[0]; ?>
            <div class="hero mb-5 row align-items-center">
                <div class="col-md-7">
                    <h1 class="display-5 fw-bold mb-3">
                        <a href="/news/<?= esc($hero['slug']) ?>" class="text-decoration-none text-dark">
                            <?= esc($hero['title'], 'raw') ?>
                        </a>
                    </h1>
                    <p class="lead mb-2 text-muted"><?= esc($hero['subtitle'], 'raw') ?></p>
                    <p class="mb-3">
                        <span class="badge bg-primary me-2">সর্বশেষ</span>
                        <span class="text-muted small">
                            <?= date('M d, Y', strtotime($hero['published_at'])) ?>
                        </span>
                    </p>
                    <p class="mb-0 fs-5">
                        <?= esc($hero['lead_text'], 'raw') ?>
                    </p>
                </div>
                <div class="col-md-5 text-center">
                    <?php if (!empty($hero['image_url'])): ?>
                        <img src="<?= esc($hero['image_url']) ?>" class="img-fluid rounded shadow-sm" style="max-height: 320px; object-fit: cover;">
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="mb-4">
            <?php foreach ($categories as $cat): ?>
                <a href="/section/<?= esc($cat['slug']) ?>" class="btn btn-outline-primary category-pill rounded-pill px-3 py-1">
                    <?= esc($cat['name'], 'raw') ?>
                </a>
            <?php endforeach; ?>
        </div>
        <h2 class="mb-4">সর্বশেষ সংবাদ</h2>
        <div class="row g-4">
            <?php foreach (array_slice($latestNews, 1) as $news): ?>
                <div class="col-md-4">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <?php if (!empty($news['image_url'])): ?>
                            <img src="<?= esc($news['image_url']) ?>" class="card-img-top news-img" alt="">
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
    <?php include __DIR__.'/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 