<footer class="footer mt-5">
    <div class="container text-center">
        <?php if (isset($categories) && is_array($categories)): ?>
            <div class="mb-2">
                <?php foreach ($categories as $cat): ?>
                    <a href="/section/<?= esc($cat['slug']) ?>" class="text-light me-3 text-decoration-none small">
                        <?= esc($cat['name'], 'raw') ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <div class="small">&copy; <?= date('Y') ?> বারিন্দ পোস্ট। সর্বস্বত্ব সংরক্ষিত।</div>
    </div>
</footer> 