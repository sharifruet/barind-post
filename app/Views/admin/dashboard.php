<?php
$userRole   = session('user_role');
$isReporter = $userRole === 'reporter';
$title      = 'Dashboard';
?>

<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <div class="page-header__eyebrow">Newsroom</div>
        <h1 class="page-header__title">Welcome, <?= esc(session('user_name')) ?></h1>
        <p class="page-header__sub">
            <?= $isReporter
                ? 'You can create and edit your own articles as drafts; an editor reviews them before publication.'
                : 'Create, edit and publish articles for Barind Post.' ?>
        </p>
    </div>
    <div class="page-header__actions">
        <a href="/admin/news/create" class="btn btn-accent"><i class="fas fa-plus me-1"></i> New article</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-4 col-md-6">
        <a class="tile" href="/admin/news">
            <div class="tile__icon"><i class="fas fa-newspaper"></i></div>
            <div class="tile__title">News</div>
            <p class="tile__text"><?= $isReporter ? 'Draft and edit your stories.' : 'Manage, review and publish every story.' ?></p>
            <span class="tile__go">Open <i class="fas fa-arrow-right ms-1"></i></span>
        </a>
    </div>

    <div class="col-lg-4 col-md-6">
        <a class="tile" href="/admin/prayer-times">
            <div class="tile__icon"><i class="fas fa-mosque"></i></div>
            <div class="tile__title">Prayer Times</div>
            <p class="tile__text">Fetch and manage prayer times for every city.</p>
            <span class="tile__go">Open <i class="fas fa-arrow-right ms-1"></i></span>
        </a>
    </div>

    <?php if (! $isReporter): ?>
        <div class="col-lg-4 col-md-6">
            <a class="tile" href="/admin/users">
                <div class="tile__icon"><i class="fas fa-users"></i></div>
                <div class="tile__title">Users</div>
                <p class="tile__text">Accounts, permissions and reporter roles.</p>
                <span class="tile__go">Open <i class="fas fa-arrow-right ms-1"></i></span>
            </a>
        </div>
        <div class="col-lg-4 col-md-6">
            <a class="tile" href="/admin/categories">
                <div class="tile__icon"><i class="fas fa-folder"></i></div>
                <div class="tile__title">Categories & Tags</div>
                <p class="tile__text">Organise the sections and tags stories are filed under.</p>
                <span class="tile__go">Open <i class="fas fa-arrow-right ms-1"></i></span>
            </a>
        </div>
        <div class="col-lg-4 col-md-6">
            <a class="tile" href="/admin/contacts">
                <div class="tile__icon"><i class="fas fa-envelope"></i></div>
                <div class="tile__title">Contact Messages</div>
                <p class="tile__text">Messages sent through the public contact form.</p>
                <span class="tile__go">Open <i class="fas fa-arrow-right ms-1"></i></span>
            </a>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
