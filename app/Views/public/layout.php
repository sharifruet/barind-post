<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title><?= isset($title) ? esc($title) : 'বারিন্দ পোস্ট' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome Icons (CSP compatible) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bengali Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
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
            background: #222;
            color: #fff;
            padding: 2rem 0 1rem 0;
            margin-top: 3rem;
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