<?php
$userRole   = session('user_role');
$isReporter = $userRole === 'reporter';
$isAdmin    = $userRole === 'admin';

$active = static fn (string $pattern): string => url_is($pattern) ? ' is-active' : '';
?>
<aside class="admin-sidebar" id="sidebar">
    <a class="admin-sidebar__brand" href="/admin">
        <img src="<?= base_url('public/logo.png') ?>" alt="">
        <span>
            <span class="admin-sidebar__brand-name">বারিন্দ পোস্ট</span>
            <span class="admin-sidebar__brand-sub">Newsroom</span>
        </span>
    </a>

    <div class="admin-sidebar__group">Content</div>
    <ul class="admin-sidebar__nav">
        <li><a class="admin-sidebar__link<?= url_is('admin') ? ' is-active' : '' ?>" href="/admin"><i class="fas fa-gauge-high"></i>Dashboard</a></li>
        <li><a class="admin-sidebar__link<?= $active('admin/news*') ?>" href="/admin/news"><i class="fas fa-newspaper"></i>News</a></li>
        <li><a class="admin-sidebar__link<?= $active('admin/categories*') ?>" href="/admin/categories"><i class="fas fa-folder"></i>Categories</a></li>
        <li><a class="admin-sidebar__link<?= $active('admin/tags*') ?>" href="/admin/tags"><i class="fas fa-tags"></i>Tags</a></li>
        <li><a class="admin-sidebar__link<?= $active('admin/kickers*') ?>" href="/admin/kickers"><i class="fas fa-hashtag"></i>Kickers</a></li>
        <?php if (! $isReporter): ?>
            <li><a class="admin-sidebar__link<?= $active('admin/sports-events*') ?>" href="/admin/sports-events"><i class="fas fa-trophy"></i>Sports Events</a></li>
        <?php endif; ?>
        <li><a class="admin-sidebar__link<?= $active('admin/prayer-times*') ?>" href="/admin/prayer-times"><i class="fas fa-mosque"></i>Prayer Times</a></li>
        <?php if ($isAdmin): ?>
            <li><a class="admin-sidebar__link<?= $active('admin/photo-card-generator*') ?>" href="/admin/photo-card-generator"><i class="fas fa-image"></i>Photo Cards</a></li>
        <?php endif; ?>
    </ul>

    <div class="admin-sidebar__group">Inbox</div>
    <ul class="admin-sidebar__nav">
        <?php if (! $isReporter): ?>
            <?php $incomingCount = (new \App\Models\NewsModel())->where('source_url IS NOT NULL')->where('status', 'draft')->countAllResults(); ?>
            <li><a class="admin-sidebar__link<?= $active('admin/incoming*') ?>" href="/admin/incoming"><i class="fas fa-inbox"></i>Incoming<?php if ($incomingCount): ?><span class="admin-sidebar__badge"><?= $incomingCount ?></span><?php endif; ?></a></li>
        <?php endif; ?>
        <li><a class="admin-sidebar__link<?= $active('admin/contacts*') ?>" href="/admin/contacts"><i class="fas fa-envelope"></i>Contact Messages</a></li>
    </ul>

    <?php if (! $isReporter): ?>
        <div class="admin-sidebar__group">People</div>
        <ul class="admin-sidebar__nav">
            <li><a class="admin-sidebar__link<?= $active('admin/users*') ?>" href="/admin/users"><i class="fas fa-users"></i>Users</a></li>
            <li><a class="admin-sidebar__link<?= $active('admin/roles*') ?>" href="/admin/roles"><i class="fas fa-shield-halved"></i>Roles</a></li>
            <li><a class="admin-sidebar__link<?= $active('admin/reporter-roles*') ?>" href="/admin/reporter-roles"><i class="fas fa-user-tie"></i>Reporter Roles</a></li>
        </ul>
    <?php endif; ?>

    <div class="admin-sidebar__group">System</div>
    <ul class="admin-sidebar__nav">
        <li><a class="admin-sidebar__link<?= $active('admin/logs*') ?>" href="/admin/logs"><i class="fas fa-file-lines"></i>Logs</a></li>
        <li><a class="admin-sidebar__link" href="/" target="_blank" rel="noopener"><i class="fas fa-arrow-up-right-from-square"></i>View Site</a></li>
    </ul>

    <div class="admin-sidebar__foot">
        <a class="admin-sidebar__link" href="/logout"><i class="fas fa-right-from-bracket"></i>Log out</a>
    </div>
</aside>
