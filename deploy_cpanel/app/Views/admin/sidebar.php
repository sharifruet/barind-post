<nav class="col-md-2 d-none d-md-block sidebar py-4">
    <div class="position-sticky">
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link<?= (url_is('admin')) ? ' active' : '' ?>" href="/admin"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link<?= (url_is('admin/news*')) ? ' active' : '' ?>" href="/admin/news"><i class="bi bi-newspaper me-2"></i>Manage News</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link<?= (url_is('admin/categories*')) ? ' active' : '' ?>" href="/admin/categories"><i class="bi bi-folder me-2"></i>Manage Categories</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link<?= (url_is('admin/tags*')) ? ' active' : '' ?>" href="/admin/tags"><i class="bi bi-tags me-2"></i>Manage Tags</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link<?= (url_is('admin/users*')) ? ' active' : '' ?>" href="/admin/users"><i class="bi bi-people me-2"></i>Manage Users</a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link<?= (url_is('admin/roles*')) ? ' active' : '' ?>" href="/admin/roles"><i class="bi bi-shield-lock me-2"></i>Manage Roles</a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="/logout"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
            </li>
        </ul>
    </div>
</nav> 