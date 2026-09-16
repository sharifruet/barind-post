<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<h2 class="bengali-text mb-3">News — <?= esc($event['title_bn']) ?></h2>
<?= view('admin/sports_events/_nav', ['event' => $event]) ?>
<p class="text-muted">Articles tagged: <code><?= esc($event['news_tag_slug'] ?? '') ?></code> · <a href="/admin/news/create">Create new article</a></p>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Title</th><th>Status</th><th>Published</th><th>Actions</th></tr></thead>
            <tbody>
                <?php if (empty($news)): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No related news yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($news as $n): ?>
                        <tr>
                            <td class="bengali-text"><?= esc($n['title']) ?></td>
                            <td><?= esc($n['status']) ?></td>
                            <td><?= $n['published_at'] ? date('M d, Y', strtotime($n['published_at'])) : '—' ?></td>
                            <td>
                                <a href="/news/<?= esc($n['slug']) ?>" target="_blank" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="/admin/news/edit/<?= $n['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
