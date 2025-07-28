<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Results - Barind Post</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
            background: #222;
            color: #fff;
            padding: 2rem 0 1rem 0;
            margin-top: 3rem;
        }
        .search-highlight {
            background: #fff3cd;
            padding: 0.1rem 0.2rem;
            border-radius: 0.2rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/header.php'; ?>
    <div class="container">
        <div class="mb-4">
            <h2>Search Results</h2>
            <?php if ($query): ?>
                <p class="text-muted">Searching for: "<strong><?= esc($query) ?></strong>"</p>
                <p class="text-muted">Found <?= count($news) ?> result<?= count($news) != 1 ? 's' : '' ?></p>
            <?php else: ?>
                <p class="text-muted">Enter a search term to find news articles</p>
            <?php endif; ?>
        </div>

        <?php if ($query && !empty($news)): ?>
            <div class="row g-4">
                <?php foreach ($news as $item): ?>
                    <div class="col-md-4">
                        <div class="card news-card h-100 border-0 shadow-sm">
                            <?php if (!empty($item['image_url'])): ?>
                                <img src="<?= esc($item['image_url']) ?>" class="card-img-top news-img" alt="">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title">
                                    <a href="/news/<?= esc($item['slug']) ?>" class="text-decoration-none text-dark fw-semibold">
                                        <?= esc($item['title']) ?>
                                    </a>
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
        <?php elseif ($query && empty($news)): ?>
            <div class="text-center py-5">
                <h4 class="text-muted">No results found</h4>
                <p class="text-muted">Try different keywords or browse our categories</p>
                <div class="mt-3">
                    <?php foreach ($categories as $cat): ?>
                        <a href="/section/<?= esc($cat['slug']) ?>" class="btn btn-outline-primary me-2 mb-2">
                            <?= esc($cat['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php include __DIR__.'/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 