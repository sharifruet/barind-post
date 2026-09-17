<?php
$isPending = $view === 'pending';
$baseQuery = $isPending ? '' : 'view=discarded';
$withSource = static function (string $src) use ($baseQuery): string {
    $q = array_filter([$baseQuery, $src !== '' ? 'source=' . rawurlencode($src) : '']);
    return '/admin/incoming' . ($q ? '?' . implode('&', $q) : '');
};
?>

<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>

<div class="page-header">
    <div>
        <div class="page-header__eyebrow">Inbox</div>
        <h1 class="page-header__title">Incoming</h1>
        <p class="page-header__sub">
            Drafts created by the automation pipelines. Nothing here reaches the site until you publish it;
            discarded drafts are archived so the same source is never ingested twice.
        </p>
    </div>
    <div class="page-header__actions">
        <a href="/admin/incoming" class="btn btn-sm <?= $isPending ? 'btn-primary' : 'btn-secondary' ?>">
            Pending <span class="ms-1 opacity-75"><?= (int) $counts['pending'] ?></span>
        </a>
        <a href="/admin/incoming?view=discarded" class="btn btn-sm <?= $isPending ? 'btn-secondary' : 'btn-primary' ?>">
            Discarded <span class="ms-1 opacity-75"><?= (int) $counts['discarded'] ?></span>
        </a>
    </div>
</div>

<?php if (! empty($lastRun)): ?>
    <div class="run-banner run-banner--<?= esc($lastRun['status'], 'attr') ?>">
        <span class="run-banner__label"><i class="fas fa-robot me-1"></i>Last collection run</span>
        <strong><?= esc(date('d M, H:i', strtotime($lastRun['created_at']))) ?></strong>
        <span class="run-banner__wf">· <?= esc($lastRun['workflow']) ?></span> —
        <strong><?= (int) $lastRun['created_count'] ?></strong> new,
        <?= (int) $lastRun['duplicate_count'] ?> already seen,
        <span class="<?= $lastRun['error_count'] ? 'text-danger fw-bold' : '' ?>"><?= (int) $lastRun['error_count'] ?> errors</span>
        <?php if (! empty($lastRun['summary'])): ?>
            <span class="run-banner__sources">
                (<?= esc(implode(', ', array_map(static fn ($r) => $r['source'] . ' ' . (int) $r['created'] . ((int) ($r['error'] ?? 0) ? ' ⚠' . (int) $r['error'] : ''), $lastRun['summary']))) ?>)
            </span>
        <?php endif; ?>
        <?php if (! empty($lastRun['message'])): ?>
            <div class="run-banner__msg"><?= esc($lastRun['message']) ?></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

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

<div class="panel">
    <div class="panel__head" style="flex-wrap: wrap; gap: 0.8rem;">
        <div class="chip-row">
            <a class="chip<?= $source === '' ? ' is-active' : '' ?>" href="<?= esc($withSource(''), 'attr') ?>">
                All sources <span class="chip__count"><?= array_sum(array_column($sources, 'n')) ?></span>
            </a>
            <?php foreach ($sources as $s): ?>
                <a class="chip<?= $source === $s['source'] ? ' is-active' : '' ?>" href="<?= esc($withSource($s['source']), 'attr') ?>">
                    <?= esc($s['source'] !== '' ? $s['source'] : '(no source name)') ?> <span class="chip__count"><?= (int) $s['n'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <?php if ($isPending && ! empty($items)): ?>
            <form id="bulkForm" method="post" action="/admin/incoming/discard-bulk" class="bulk-bar ms-auto"><?= csrf_field() ?>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="pickAll">
                    <label class="form-check-label small" for="pickAll">Select all</label>
                </div>
                <button type="submit" id="bulkDiscardBtn" class="btn btn-sm btn-outline-danger" disabled>Discard selected</button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($items)): ?>
        <div class="empty">
            <i class="fas fa-inbox"></i>
            <?= $isPending ? 'No drafts waiting. Run a collector in n8n to fill the queue.' : 'Nothing has been discarded' . ($source !== '' ? ' from this source' : '') . '.' ?>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): ?>
            <?php
            $points   = key_points($item['lead_text'] ?? null);
            $hasImage = ! empty($item['image_url']);
            $thumb    = $hasImage ? get_image_url($item['image_url']) : ($item['suggested_image_url'] ?? '');
            ?>
            <div class="incoming-item">
                <div>
                    <?php if ($isPending): ?>
                        <input class="form-check-input pick-item" type="checkbox" value="<?= (int) $item['id'] ?>" aria-label="Select">
                    <?php endif; ?>
                </div>

                <div class="incoming-item__media">
                    <?php if ($thumb): ?>
                        <img class="incoming-item__thumb" src="<?= esc($thumb, 'attr') ?>" alt="" loading="lazy" referrerpolicy="no-referrer"
                             onerror="this.outerHTML='<div class=&quot;incoming-item__thumb incoming-item__thumb--empty&quot; title=&quot;Image could not be loaded&quot;><i class=&quot;fas fa-image-slash&quot;></i></div>'">
                        <div class="incoming-item__thumb-note">
                            <?php if ($hasImage): ?>
                                <i class="fas fa-check text-success"></i> Lead image set
                            <?php else: ?>
                                Suggested from source — not published until adopted
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="incoming-item__thumb incoming-item__thumb--empty"><i class="fas fa-image"></i></div>
                        <div class="incoming-item__thumb-note">No image found at source</div>
                    <?php endif; ?>
                </div>

                <div class="incoming-item__body">
                    <a class="incoming-item__title bengali-text" href="/admin/news/edit/<?= (int) $item['id'] ?>"><?= esc($item['title']) ?></a>
                    <?php if (! empty($item['subtitle'])): ?>
                        <p class="incoming-item__sub bengali-text"><?= esc($item['subtitle']) ?></p>
                    <?php endif; ?>
                    <?php if (count($points) >= 2): ?>
                        <ul class="incoming-item__points bengali-text">
                            <?php foreach ($points as $p): ?><li><?= esc($p) ?></li><?php endforeach; ?>
                        </ul>
                    <?php elseif (count($points) === 1): ?>
                        <p class="incoming-item__sub bengali-text"><?= esc($points[0]) ?></p>
                    <?php endif; ?>
                    <div class="incoming-item__meta">
                        <span>
                            <i class="fas fa-satellite-dish me-1"></i>
                            <?php if (! empty($item['source_url'])): ?>
                                <a href="<?= esc($item['source_url'], 'attr') ?>" target="_blank" rel="noopener noreferrer"><?= esc($item['source'] ?: parse_url($item['source_url'], PHP_URL_HOST)) ?> <i class="fas fa-arrow-up-right-from-square" style="font-size: 0.65em;"></i></a>
                            <?php else: ?>
                                <?= esc($item['source'] ?: 'Unknown source') ?>
                            <?php endif; ?>
                        </span>
                        <span><i class="far fa-clock me-1"></i><?= esc(date('d M Y, H:i', strtotime($item['created_at']))) ?></span>
                        <?php if (! empty($item['word_count'])): ?><span><?= (int) $item['word_count'] ?> words</span><?php endif; ?>
                        <span><i class="fas fa-folder me-1"></i><span data-category-label="<?= (int) $item['id'] ?>"><?= esc($item['category_name'] ?? '—') ?></span></span>
                        <?php if (! empty($item['possible_duplicate_of'])): ?>
                            <a class="status status--dup" href="/admin/news/edit/<?= (int) $item['possible_duplicate_of'] ?>" title="A recent article has a very similar headline — compare before publishing">
                                <i class="fas fa-clone me-1"></i>possible duplicate of #<?= (int) $item['possible_duplicate_of'] ?><?= ! empty($item['duplicate_score']) ? ' (' . (int) $item['duplicate_score'] . '%)' : '' ?>
                            </a>
                        <?php endif; ?>
                        <?php if (! $isPending): ?><span class="status status--archived">Discarded</span><?php endif; ?>
                    </div>
                </div>

                <div class="incoming-item__actions">
                    <?php if ($isPending): ?>
                        <form method="post" action="/admin/incoming/publish/<?= (int) $item['id'] ?>" class="d-flex flex-column gap-2"><?= csrf_field() ?>
                            <div>
                                <select name="category_id" class="form-select form-select-sm bengali-input" data-category-select="<?= (int) $item['id'] ?>" aria-label="Category">
                                    <?php foreach ($categories as $c): ?>
                                        <option value="<?= (int) $c['id'] ?>" <?= (int) $c['id'] === (int) $item['category_id'] ? 'selected' : '' ?>><?= esc($c['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="save-note"></div>
                            </div>
                            <div class="incoming-item__buttons">
                                <button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Publish</button>
                                <a class="btn btn-sm btn-secondary" href="/admin/news/edit/<?= (int) $item['id'] ?>"><i class="fas fa-pen me-1"></i>Edit</a>
                            </div>
                        </form>
                        <div class="incoming-item__buttons">
                            <?php if (! $hasImage && ! empty($item['suggested_image_url'])): ?>
                                <form method="post" action="/admin/incoming/adopt-image/<?= (int) $item['id'] ?>"><?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary" title="Copy the source image into our uploads and use it as the lead image"><i class="fas fa-image me-1"></i>Use image</button>
                                </form>
                            <?php endif; ?>
                            <form method="post" action="/admin/incoming/discard/<?= (int) $item['id'] ?>" onsubmit="return confirm('Discard this draft? It stays archived so the source URL is not ingested again.');"><?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-box-archive me-1"></i>Discard</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <div class="incoming-item__buttons">
                            <form method="post" action="/admin/incoming/restore/<?= (int) $item['id'] ?>"><?= csrf_field() ?>
                                <button type="submit" class="btn btn-sm btn-secondary"><i class="fas fa-rotate-left me-1"></i>Restore to pending</button>
                            </form>
                            <a class="btn btn-sm btn-outline-secondary" href="/admin/news/edit/<?= (int) $item['id'] ?>">Edit</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Inline category change: saves immediately; Publish also sends the selected value.
    document.querySelectorAll('[data-category-select]').forEach(function (sel) {
        sel.addEventListener('change', function () {
            var id   = sel.getAttribute('data-category-select');
            var note = sel.parentElement.querySelector('.save-note');
            var fd   = new FormData(); fd.append('category_id', sel.value);
            note.textContent = 'Saving…';
            fetch('/admin/incoming/category/' + id, { method: 'POST', body: fd, credentials: 'same-origin' })
                .then(function (r) { return r.json(); })
                .then(function (j) {
                    note.textContent = j.ok ? 'Saved' : (j.error || 'Failed');
                    var label = document.querySelector('[data-category-label="' + id + '"]');
                    if (j.ok && label) label.textContent = j.category;
                })
                .catch(function () { note.textContent = 'Failed'; })
                .finally(function () { setTimeout(function () { note.textContent = ''; }, 2500); });
        });
    });

    // Bulk discard: collect checked ids into the form on submit.
    var form  = document.getElementById('bulkForm');
    var all   = document.getElementById('pickAll');
    var btn   = document.getElementById('bulkDiscardBtn');
    var boxes = Array.prototype.slice.call(document.querySelectorAll('.pick-item'));
    if (!form || !btn) return;

    function sync() {
        var n = boxes.filter(function (b) { return b.checked; }).length;
        btn.disabled = n === 0;
        btn.textContent = n ? 'Discard selected (' + n + ')' : 'Discard selected';
        if (all) all.checked = n > 0 && n === boxes.length;
    }
    if (all) all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); sync(); });
    boxes.forEach(function (b) { b.addEventListener('change', sync); });

    form.addEventListener('submit', function (e) {
        var ids = boxes.filter(function (b) { return b.checked; }).map(function (b) { return b.value; });
        if (!ids.length || !confirm('Discard ' + ids.length + ' draft(s)? They stay archived so their source URLs are not ingested again.')) {
            e.preventDefault(); return;
        }
        form.querySelectorAll('input[name="ids[]"]').forEach(function (i) { i.remove(); });
        ids.forEach(function (id) {
            var i = document.createElement('input'); i.type = 'hidden'; i.name = 'ids[]'; i.value = id; form.appendChild(i);
        });
    });
});
</script>

<?= $this->endSection() ?>
