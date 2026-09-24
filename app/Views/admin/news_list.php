<?php
$userRole   = session('user_role');
$isReporter = $userRole === 'reporter';
$isAdmin    = $userRole === 'admin';
$title      = 'News';

$categoryNames = [];
foreach ($categories as $c) {
    $categoryNames[$c['id']] = $c['name'];
}

// Row-action forms carry the page so their redirect lands back here (AdminNews::redirectToList).
$pageField = '<input type="hidden" name="page" value="' . (int) $page . '">';
?>

<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <div class="page-header__eyebrow">Content</div>
        <h1 class="page-header__title">News</h1>
        <p class="page-header__sub">
            <?= $isReporter
                ? 'Your articles. Anything you save is a draft until an editor publishes it.'
                : 'Every story, across all reporters.' ?>
        </p>
    </div>
    <div class="page-header__actions">
        <a href="/admin/news/create" class="btn btn-accent"><i class="fas fa-plus me-1"></i> New article</a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3"><div class="stat"><div class="stat__label">All</div><div class="stat__value"><?= $counts['all'] ?></div></div></div>
    <div class="col-6 col-md-3"><div class="stat"><div class="stat__label">Published</div><div class="stat__value"><?= $counts['published'] ?></div></div></div>
    <div class="col-6 col-md-3"><div class="stat"><div class="stat__label">Drafts</div><div class="stat__value"><?= $counts['draft'] ?></div></div></div>
    <div class="col-6 col-md-3"><div class="stat"><div class="stat__label">Archived</div><div class="stat__value"><?= $counts['archived'] ?></div></div></div>
</div>

<div class="panel">
    <div class="panel__head">
        <div class="panel__title">Articles</div>
        <input type="search" class="form-control form-control-sm" id="newsFilter" placeholder="Filter this page by title, kicker or category…" style="max-width: 320px;" autocomplete="off">
    </div>

    <?php if (empty($news)): ?>
        <div class="empty">
            <i class="fas fa-newspaper"></i>
            No articles yet. <a href="/admin/news/create">Write the first one.</a>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table class="table table-hover" id="newsTable">
                <thead>
                    <tr>
                        <th>Story</th>
                        <th>Category</th>
                        <th>Status</th>
                        <?php if (! $isReporter): ?><th>Author</th><?php endif; ?>
                        <th class="text-end">Views</th>
                        <th>Published</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($news as $item): ?>
                    <?php
                    $isBreaking = ! empty($item['kicker']) && $item['kicker'] === 'ব্রেকিং';
                    $catName    = $categoryNames[$item['category_id']] ?? '—';
                    $searchText = mb_strtolower(($item['title'] ?? '') . ' ' . ($item['kicker'] ?? '') . ' ' . $catName);
                    ?>
                    <tr data-search="<?= esc($searchText, 'attr') ?>">
                        <td>
                            <div class="story-cell">
                                <?php if (! empty($item['image_url'])): ?>
                                    <img class="story-cell__thumb" src="<?= esc(get_image_url($item['image_url']), 'attr') ?>" alt="" loading="lazy"
                                         onerror="this.classList.add('story-cell__thumb--empty');this.outerHTML='<div class=&quot;story-cell__thumb story-cell__thumb--empty&quot;><i class=&quot;fas fa-image&quot;></i></div>'">
                                <?php else: ?>
                                    <div class="story-cell__thumb story-cell__thumb--empty"><i class="fas fa-image"></i></div>
                                <?php endif; ?>
                                <div>
                                    <?php if (! empty($item['kicker'])): ?>
                                        <div class="story-cell__kicker" style="color: <?= esc($item['kicker_color'] ?: '#c8102e', 'attr') ?>;"><?= esc($item['kicker']) ?></div>
                                    <?php endif; ?>
                                    <a class="story-cell__title bengali-text" href="/admin/news/edit/<?= esc($item['id']) ?>"><?= esc($item['title']) ?></a>
                                    <div class="story-cell__meta">
                                        #<?= esc($item['id']) ?>
                                        <?php if ($item['featured']): ?> &middot; <span class="flag"><i class="fas fa-star"></i> Featured</span><?php endif; ?>
                                        <?php if ($isBreaking): ?> &middot; <span class="flag"><i class="fas fa-bolt"></i> Breaking</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="bengali-text"><?= esc($catName) ?></td>
                        <td><span class="status status--<?= esc($item['status'], 'attr') ?>"><?= esc(ucfirst($item['status'])) ?></span></td>
                        <?php if (! $isReporter): ?>
                            <td><?= esc($userMap[$item['author_id']] ?? 'Unknown') ?></td>
                        <?php endif; ?>
                        <td class="text-end num"><?= (int) ($viewCounts[$item['id']] ?? 0) ?></td>
                        <td class="num">
                            <?= ! empty($item['published_at']) ? esc(date('d M Y, H:i', strtotime($item['published_at']))) : '<span class="text-muted">—</span>' ?>
                        </td>
                        <td class="text-end">
                            <div class="row-actions justify-content-end">
                                <?php if (! empty($item['slug']) && $item['status'] === 'published'): ?>
                                    <a class="btn-icon" href="/news/<?= esc(rawurlencode($item['slug']), 'attr') ?>" target="_blank" rel="noopener" title="View on site"><i class="fas fa-arrow-up-right-from-square"></i></a>
                                <?php endif; ?>
                                <a class="btn-icon" href="/admin/news/edit/<?= esc($item['id']) ?>" title="Edit"><i class="fas fa-pen"></i></a>
                                <?php if ($isAdmin): ?>
                                    <form method="post" action="/admin/news/toggle-featured/<?= esc($item['id']) ?>"><?= csrf_field() ?><?= $pageField ?>
                                        <button type="submit" class="btn-icon" title="<?= $item['featured'] ? 'Remove from featured' : 'Mark as featured' ?>"
                                                onclick="return confirm('<?= $item['featured'] ? 'Remove this article from featured?' : 'Feature this article?' ?>')">
                                            <i class="<?= $item['featured'] ? 'fas' : 'far' ?> fa-star"></i>
                                        </button>
                                    </form>
                                    <form method="post" action="/admin/news/toggle-breaking/<?= esc($item['id']) ?>"><?= csrf_field() ?><?= $pageField ?>
                                        <button type="submit" class="btn-icon" title="<?= $isBreaking ? 'Remove breaking kicker' : 'Mark as breaking' ?>"
                                                onclick="return confirm('<?= $isBreaking ? 'Remove the breaking kicker?' : 'Add the breaking kicker?' ?>')">
                                            <i class="fas fa-bolt<?= $isBreaking ? ' text-danger' : '' ?>"></i>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <form method="post" action="/admin/news/delete/<?= esc($item['id']) ?>"><?= csrf_field() ?><?= $pageField ?>
                                    <button type="submit" class="btn-icon btn-icon--danger" title="Delete" onclick="return confirm('Delete this article? This cannot be undone.')"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
            <?php
            $from = ($page - 1) * $perPage + 1;
            $to   = $from + count($news) - 1;
            // First, last and a window around the current page; anything skipped becomes "…"
            $links = array_filter(range(1, $totalPages), fn ($p) => $p === 1 || $p === $totalPages || abs($p - $page) <= 2);
            $pageUrl = fn ($p) => '/admin/news' . ($p > 1 ? '?page=' . $p : '');
            ?>
            <nav class="pager" aria-label="Article pages">
                <div class="pager__summary">Showing <?= number_format($from) ?>–<?= number_format($to) ?> of <?= number_format($counts['all']) ?></div>
                <ul class="pager__pages">
                    <li>
                        <?php if ($page > 1): ?>
                            <a class="pager__link" href="<?= $pageUrl($page - 1) ?>" rel="prev"><i class="fas fa-chevron-left"></i><span class="d-none d-sm-inline ms-1">Newer</span></a>
                        <?php else: ?>
                            <span class="pager__link is-disabled"><i class="fas fa-chevron-left"></i><span class="d-none d-sm-inline ms-1">Newer</span></span>
                        <?php endif; ?>
                    </li>
                    <?php $prev = null; foreach ($links as $p): ?>
                        <?php if ($prev !== null && $p - $prev > 1): ?><li class="pager__gap">…</li><?php endif; ?>
                        <li>
                            <?php if ($p === $page): ?>
                                <span class="pager__link is-current" aria-current="page"><?= $p ?></span>
                            <?php else: ?>
                                <a class="pager__link" href="<?= $pageUrl($p) ?>"><?= $p ?></a>
                            <?php endif; ?>
                        </li>
                        <?php $prev = $p; ?>
                    <?php endforeach; ?>
                    <li>
                        <?php if ($page < $totalPages): ?>
                            <a class="pager__link" href="<?= $pageUrl($page + 1) ?>" rel="next"><span class="d-none d-sm-inline me-1">Older</span><i class="fas fa-chevron-right"></i></a>
                        <?php else: ?>
                            <span class="pager__link is-disabled"><span class="d-none d-sm-inline me-1">Older</span><i class="fas fa-chevron-right"></i></span>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
(function () {
    var input = document.getElementById('newsFilter');
    var rows  = document.querySelectorAll('#newsTable tbody tr');
    if (!input || !rows.length) return;
    input.addEventListener('input', function () {
        var q = input.value.trim().toLowerCase();
        rows.forEach(function (tr) {
            tr.hidden = q !== '' && tr.getAttribute('data-search').indexOf(q) === -1;
        });
    });
})();
</script>
<?= $this->endSection() ?>
