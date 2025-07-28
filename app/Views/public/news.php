<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($news['title']) ?> - Barind Post</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .news-img {
            border-radius: 1rem;
            object-fit: cover;
            max-height: 400px;
            width: 100%;
        }
        .footer {
            background: #222;
            color: #fff;
            padding: 2rem 0 1rem 0;
            margin-top: 3rem;
        }
        .back-btn {
            border-radius: 2rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/header.php'; ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <a href="javascript:history.back()" class="btn btn-outline-primary back-btn mb-3">&larr; Back</a>
                <h1 class="mb-2 mt-3 fw-bold display-5"><?= esc($news['title']) ?></h1>
                <?php if (!empty($news['subtitle'])): ?>
                    <h4 class="text-muted mb-3 fw-normal"><?= esc($news['subtitle']) ?></h4>
                <?php endif; ?>
                <p class="text-muted small mb-2">
                    <?= date('M d, Y', strtotime($news['published_at'])) ?>
                </p>
                <?php if (!empty($news['image_url'])): ?>
                    <img src="<?= esc($news['image_url']) ?>" class="news-img mb-3 shadow-sm" alt="">
                <?php endif; ?>
                <div class="mb-3 fs-5">
                    <strong><?= esc($news['lead_text']) ?></strong>
                </div>
                <div class="mb-4 fs-5 lh-lg">
                    <?= $news['content'] ?>
                </div>
            </div>
        </div>
    </div>
    <?php include __DIR__.'/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 