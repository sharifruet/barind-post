<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? esc($title) : 'Admin - Barind Post' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSP disabled via .htaccess -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { background: #f8fafc; }
        .sidebar { min-height: 100vh; background: #343a40; color: #fff; }
        .sidebar .nav-link { color: #adb5bd; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { color: #fff; background: #495057; }
        .navbar { background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
        .dashboard-cards .card { transition: box-shadow 0.2s; }
        .dashboard-cards .card:hover { box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        
        /* Bengali font support */
        .bengali-text {
            font-family: 'Noto Sans Bengali', 'Noto Sans', sans-serif;
        }
        
        /* Apply Bengali font to form inputs when Bengali is selected */
        .bengali-input {
            font-family: 'Noto Sans Bengali', 'Noto Sans', sans-serif;
            font-size: 16px; /* Better for Bengali text */
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/header.php'; ?>
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__.'/sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <?= $this->renderSection('content') ?>
                <?php include __DIR__.'/footer.php'; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 