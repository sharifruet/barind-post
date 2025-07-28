<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($category['name']) ?> - Barind Post</title>
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
        .category-pill.active, .category-pill:focus, .category-pill:active {
            background: #0d6efd;
            color: #fff !important;
            border-color: #0d6efd;
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
            background: #222;
            color: #fff;
            padding: 2rem 0 1rem 0;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/header.php'; ?>
    <div class="container">
        <div class="mb-4">
            <a href="/section/<?= esc($category['slug']) ?>" class="btn btn-primary category-pill rounded-pill px-3 py-1 active">
                <?= esc($category['name']) ?>
            </a>
        </div>
        <h2 class="mb-4">Section: <?= esc($category['name']) ?></h2>
        <div class="row g-4">
            <?php foreach ($news as $item): ?>
                <div class="col-md-4">
                    <div class="card news-card h-100 border-0 shadow-sm">
                        <?php if (!empty($item['image_url'])): ?>
                            <img src="<?= esc($item['image_url']) ?>" class="card-img-top news-img" alt="">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title">
                                <a href="/news/<?= esc($item['slug']) ?>" class="text-decoration-none text-dark fw-semibold"><?= esc($item['title']) ?></a>
                            </h5>
                            <p class="card-text small text-muted mb-1">
                                <?= date('M d, Y', strtotime($item['published_at'])) ?>
                            </p>
                            <p class="card-text">
                                <?= esc($item['lead_text']) ?>
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