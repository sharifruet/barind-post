<?php
$title = 'আমাদের সম্পর্কে - বারিন্দ পোস্ট';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h1 class="h3 mb-0">বারিন্দ পোস্ট</h1>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <p>বারিন্দ পোস্ট (barindpost.com) রাজশাহী অঞ্চলের একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল। ২০২৪ সালে প্রতিষ্ঠিত এই পোর্টালের মূল লক্ষ্য হল বরেন্দ্র অঞ্চলের মানুষের কাছে বিশ্বাসযোগ্য, সময়োপযোগী ও নিরপেক্ষ সংবাদ পৌঁছে দেওয়া।</p>
                        <p>আমরা বিশ্বাস করি যে সঠিক তথ্য মানুষের জীবন পরিবর্তন করতে পারে। তাই আমরা সর্বদা সত্যতা যাচাই করে, নিরপেক্ষ দৃষ্টিভঙ্গি নিয়ে সংবাদ প্রকাশ করি।</p>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">আমাদের লক্ষ্য</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-bullseye text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">সঠিক তথ্য</h6>
                                        <p class="small text-muted">সময়োপযোগী ও সত্যতা যাচাইকৃত সংবাদ প্রদান</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-balance-scale text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">নিরপেক্ষতা</h6>
                                        <p class="small text-muted">রাজনৈতিক দল বা গোষ্ঠীর প্রতি পক্ষপাতহীন সংবাদ</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-users text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">জনগণের কণ্ঠস্বর</h6>
                                        <p class="small text-muted">স্থানীয় মানুষের সমস্যা ও সাফল্যের কথা তুলে ধরা</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-globe text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">ডিজিটাল বিপ্লব</h6>
                                        <p class="small text-muted">প্রযুক্তির মাধ্যমে সংবাদকে সহজলভ্য করা</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
<!--
                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">আমাদের কভারেজ এলাকা</h2>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card border-danger h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-map-marker-alt text-danger fa-2x mb-2"></i>
                                        <h5 class="card-title">রাজশাহী</h5>
                                        <p class="card-text small">রাজশাহী সিটি কর্পোরেশন ও আশপাশের এলাকা</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-danger h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-map-marker-alt text-danger fa-2x mb-2"></i>
                                        <h5 class="card-title">চাঁপাইনবাবগঞ্জ</h5>
                                        <p class="card-text small">চাঁপাইনবাবগঞ্জ জেলা ও উপজেলা</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-danger h-100">
                                    <div class="card-body text-center">
                                        <i class="fas fa-map-marker-alt text-danger fa-2x mb-2"></i>
                                        <h5 class="card-title">নাটোর</h5>
                                        <p class="card-text small">নাটোর জেলা ও আশপাশের এলাকা</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
-->
                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">আমাদের সংবাদ বিভাগ</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-flag text-danger me-2"></i>জাতীয়</span>
                                        <span class="badge bg-danger rounded-pill">প্রধান</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-landmark text-danger me-2"></i>রাজনীতি</span>
                                        <span class="badge bg-danger rounded-pill">নিয়মিত</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-chart-line text-danger me-2"></i>অর্থনীতি</span>
                                        <span class="badge bg-danger rounded-pill">নিয়মিত</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-globe text-danger me-2"></i>আন্তর্জাতিক</span>
                                        <span class="badge bg-danger rounded-pill">নিয়মিত</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6 mb-3">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-futbol text-danger me-2"></i>খেলাধুলা</span>
                                        <span class="badge bg-danger rounded-pill">নিয়মিত</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-film text-danger me-2"></i>বিনোদন</span>
                                        <span class="badge bg-danger rounded-pill">নিয়মিত</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-graduation-cap text-danger me-2"></i>শিক্ষা</span>
                                        <span class="badge bg-danger rounded-pill">নিয়মিত</span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span><i class="fas fa-heartbeat text-danger me-2"></i>স্বাস্থ্য</span>
                                        <span class="badge bg-danger rounded-pill">নিয়মিত</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">আমাদের দল</h2>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="fas fa-user-tie text-danger fa-3x mb-3"></i>
                                        <h5 class="card-title">সম্পাদক</h5>
                                        <p class="card-text small">অভিজ্ঞ সাংবাদিক যিনি সংবাদের মান নিশ্চিত করেন</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="fas fa-camera text-danger fa-3x mb-3"></i>
                                        <h5 class="card-title">ফটোগ্রাফার</h5>
                                        <p class="card-text small">দক্ষ ফটোগ্রাফার যারা ঘটনার ছবি তুলে ধরেন</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="fas fa-laptop text-danger fa-3x mb-3"></i>
                                        <h5 class="card-title">টেকনিক্যাল টিম</h5>
                                        <p class="card-text small">ওয়েবসাইট ও প্রযুক্তি পরিচালনা করেন</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">আমাদের মূল্যবোধ</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-shield-alt text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">সততা</h6>
                                        <p class="small text-muted">সবসময় সত্য কথা বলি, ভুল তথ্য প্রকাশ করি না</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-handshake text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">বিশ্বাসযোগ্যতা</h6>
                                        <p class="small text-muted">পাঠকদের বিশ্বাস অর্জন আমাদের প্রধান লক্ষ্য</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-clock text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">সময়ানুবর্তিতা</h6>
                                        <p class="small text-muted">সময়মতো সংবাদ প্রকাশ করি</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-heart text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">মানবিকতা</h6>
                                        <p class="small text-muted">মানুষের কল্যাণে কাজ করি</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">যোগাযোগ</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-danger">অফিস</h6>
                                        <p class="mb-1"><strong>ঠিকানা:</strong> অস্থায়ী কার্যালয়<br>মহিশালবাড়ী, গোদাগাড়ী, রাজশাহী, বাংলাদেশ</p>
                                        <p class="mb-1"><strong>ইমেইল:</strong> অফিস: info@barindpost.com<br>বার্তা বিভাগ: news@barindpost.com</p>
                                        <p class="mb-1"><strong>কর্মসময়:</strong> সকাল ৯টা - সন্ধ্যা ৬টা</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-danger">সামাজিক যোগাযোগ</h6>
                                        <div class="d-flex gap-2">
                                            <a href="https://facebook.com/barindpost" target="_blank" class="btn btn-outline-danger btn-sm">
                                                <i class="fab fa-facebook"></i> Facebook
                                            </a>
                                            <a href="https://instagram.com/barindpost" target="_blank" class="btn btn-outline-danger btn-sm">
                                                <i class="fab fa-instagram"></i> Instagram
                                            </a>
                                            <a href="https://x.com/BarindPost" target="_blank" class="btn btn-outline-info btn-sm">
                                                <i class="fa-brands fa-x-twitter"></i> X
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>আমাদের সাথে যোগাযোগ:</strong> আপনার মতামত, পরামর্শ বা প্রশ্ন থাকলে আমাদের <a href="/contact" class="alert-link">যোগাযোগ</a> পৃষ্ঠায় জানাতে পারেন।
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?> 