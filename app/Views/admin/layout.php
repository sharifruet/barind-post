<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= isset($title) ? esc($title) : 'Admin - Barind Post' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSP disabled via .htaccess -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
        
        /* Mobile responsive sidebar */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: -250px;
                width: 250px;
                height: 100vh;
                z-index: 1050;
                transition: left 0.3s ease;
            }
            .sidebar.show {
                left: 0;
            }
            .main-content {
                margin-left: 0 !important;
            }
            .sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                z-index: 1040;
                display: none;
            }
            .sidebar-overlay.show {
                display: block;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/header.php'; ?>
    
    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <div class="container-fluid">
        <div class="row">
            <?php include __DIR__.'/sidebar.php'; ?>
            <main class="col-md-10 ms-sm-auto px-md-4 py-4 main-content">
                <?= $this->renderSection('content') ?>
                <?php include __DIR__.'/footer.php'; ?>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Twitter Widget Script -->
    <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
    
    <!-- Mobile sidebar toggle script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            
            if (sidebarToggle && sidebar && sidebarOverlay) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
                
                sidebarOverlay.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
        });
    </script>
</body>
</html> 