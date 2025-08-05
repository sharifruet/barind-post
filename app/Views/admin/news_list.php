<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<?php 
$userRole = session('user_role');
$isReporter = $userRole === 'reporter';
?>

<?php if ($isReporter): ?>
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle"></i>
        <strong>Reporter Dashboard:</strong> You can only view and manage your own news articles as drafts. 
        Your articles will be reviewed by editors before publication.
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>News Articles</h2>
    <a href="/admin/news/create" class="btn btn-primary">Create News</a>
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

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <?php if (!$isReporter): ?>
                        <th>Author</th>
                    <?php endif; ?>
                    <th>Published At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($news as $item): ?>
                    <tr>
                        <td><?= esc($item['id']) ?></td>
                        <td><?= esc($item['title']) ?></td>
                        <td>
                            <?php
                            $cat = array_filter($categories, fn($c) => $c['id'] == $item['category_id']);
                            echo $cat ? esc(array_values($cat)[0]['name']) : '-';
                            ?>
                        </td>
                        <td>
                            <span class="badge bg-<?= $item['status'] == 'published' ? 'success' : ($item['status'] == 'draft' ? 'warning' : 'secondary') ?>">
                                <?= esc($item['status']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($item['featured']): ?>
                                <span class="badge bg-danger">Featured</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <?php if (!$isReporter): ?>
                            <td><?= esc($userMap[$item['author_id']] ?? 'Unknown') ?></td>
                        <?php endif; ?>
                        <td><?= esc($item['published_at']) ?></td>
                        <td>
                            <a href="/admin/news/edit/<?= esc($item['id']) ?>" class="btn btn-sm btn-warning">Edit</a>
                            <form method="post" action="/admin/news/toggle-featured/<?= esc($item['id']) ?>" style="display:inline;">
                                <button type="submit" class="btn btn-sm btn-<?= $item['featured'] ? 'secondary' : 'success' ?>" 
                                        onclick="return confirm('<?= $item['featured'] ? 'Unfeature' : 'Feature' ?> this news article?')">
                                    <?= $item['featured'] ? 'Unfeature' : 'Feature' ?>
                                </button>
                            </form>
                            <form method="post" action="/admin/news/delete/<?= esc($item['id']) ?>" style="display:inline;">
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this news article?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?> 