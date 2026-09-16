<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container-fluid">
        <!-- Mobile menu button -->
        <button class="btn btn-outline-secondary d-md-none me-3" type="button" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <span class="navbar-brand fw-bold">Barind Post Admin</span>
        <div class="d-flex align-items-center">
            <span class="me-3 d-none d-md-inline"><i class="fas fa-user-circle"></i> <?php echo session('user_name'); ?> (<?php echo session('user_role'); ?>)</span>
            <a href="/logout" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav> 