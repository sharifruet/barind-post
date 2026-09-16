<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">
                        <i class="fas fa-mosque"></i> 
                        নামাজের সময়সূচী - <?= $year ?>
                    </h2>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('message')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= session()->getFlashdata('message') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-check-circle"></i> 
                                        ডেটা আছে (<?= count($cities_with_data) ?>)
                                    </h5>
                                    <p class="card-text">এই শহরগুলোর জন্য নামাজের সময়সূচী ডেটাবেসে সংরক্ষিত আছে।</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-dark">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-exclamation-triangle"></i> 
                                        ডেটা নেই (<?= count($cities_without_data) ?>)
                                    </h5>
                                    <p class="card-text">এই শহরগুলোর জন্য নামাজের সময়সূচী এখনও ডেটাবেসে নেই।</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-database"></i> 
                                        মোট রেকর্ড (<?= isset($stats['total_records']) ? $stats['total_records'] : 0 ?>)
                                    </h5>
                                    <p class="card-text">এই বছরের জন্য মোট নামাজের সময়সূচী রেকর্ড।</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <i class="fas fa-map-marker-alt"></i> 
                                        শহর (<?= count($cities_with_data) + count($cities_without_data) ?>)
                                    </h5>
                                    <p class="card-text">বাংলাদেশের মোট শহরের সংখ্যা।</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cities with Data -->
                    <?php if (!empty($cities_with_data)): ?>
                    <div class="mb-5">
                        <h4 class="text-success mb-3">
                            <i class="fas fa-check-circle"></i> 
                            শহরসমূহ যাদের ডেটা আছে
                        </h4>
                        <div class="row">
                            <?php foreach ($cities_with_data as $city): ?>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="card border-success">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-success"><?= $city['name'] ?></h6>
                                        <p class="card-text small text-muted">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?= number_format($city['latitude'], 4) ?>, <?= number_format($city['longitude'], 4) ?>
                                        </p>
                                        <a href="/admin/prayer-times/city/<?= $city['id'] ?>/<?= $year ?>-01-01" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-eye"></i> দেখুন
                                        </a>
                                        <a href="/admin/prayer-times/delete/<?= $year ?>/<?= $city['id'] ?>" 
                                           class="btn btn-danger btn-sm ms-1" 
                                           onclick="return confirm('আপনি কি <?= $city['name'] ?> শহরের <?= $year ?> সালের নামাজের সময়সূচী ডেটা মুছে ফেলতে চান?')">
                                            <i class="fas fa-trash"></i> মুছুন
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Cities without Data -->
                    <?php if (!empty($cities_without_data)): ?>
                    <div class="mb-5">
                        <h4 class="text-warning mb-3">
                            <i class="fas fa-exclamation-triangle"></i> 
                            শহরসমূহ যাদের ডেটা নেই
                        </h4>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>নির্দেশনা:</strong> নিচের শহরগুলোর জন্য নামাজের সময়সূচী ডেটা এখনও নেই। 
                            "ডেটা আনুন" বাটনে ক্লিক করে Adhan API থেকে ডেটা আনতে পারেন।
                        </div>
                        <div class="row">
                            <?php foreach ($cities_without_data as $city): ?>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="card border-warning">
                                    <div class="card-body text-center">
                                        <h6 class="card-title text-warning"><?= $city['name'] ?></h6>
                                        <p class="card-text small text-muted">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?= number_format($city['latitude'], 4) ?>, <?= number_format($city['longitude'], 4) ?>
                                        </p>
                                        <a href="/admin/prayer-times/fetch/<?= $year ?>/<?= $city['id'] ?>" 
                                           class="btn btn-warning btn-sm" 
                                           onclick="return confirm('আপনি কি <?= $city['name'] ?> শহরের জন্য <?= $year ?> সালের নামাজের সময়সূচী ডেটা আনতে চান?')">
                                            <i class="fas fa-download"></i> ডেটা আনুন
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Year Navigation -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5 class="card-title">অন্যান্য বছর দেখুন</h5>
                                    <div class="btn-group" role="group">
                                        <?php for ($i = 2020; $i <= 2030; $i++): ?>
                                            <?php if ($i == $year): ?>
                                                <button type="button" class="btn btn-primary"><?= $i ?></button>
                                            <?php else: ?>
                                                <a href="/admin/prayer-times/<?= $i ?>" class="btn btn-outline-primary"><?= $i ?></a>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API Information -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-info-circle"></i> 
                                        API তথ্য
                                    </h6>
                                    <p class="card-text small">
                                        <strong>ডেটা সোর্স:</strong> Adhan Prayer Times API (api.aladhan.com)<br>
                                        <strong>পদ্ধতি:</strong> Islamic Society of North America (ISNA)<br>
                                        <strong>স্কুল:</strong> Shafi<br>
                                        <strong>আপডেট:</strong> ডেটা একবার আনলে ডেটাবেসে সংরক্ষিত থাকে
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
}

.btn-group .btn {
    margin: 2px;
}

@media (max-width: 768px) {
    .btn-group {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .btn-group .btn {
        flex: 1;
        min-width: 60px;
    }
}
</style>

<?= $this->endSection() ?>
