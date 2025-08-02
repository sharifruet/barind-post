<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold">Barind Post Admin</span>
        <div class="d-flex align-items-center">
            <span class="me-3"><i class="fas fa-user-circle"></i> <?php echo session('user_name'); ?> (<?php echo session('user_role'); ?>)</span>
            <a href="/logout" class="btn btn-outline-danger btn-sm">Logout</a>
        </div>
    </div>
</nav> 