<?php
$title = 'বিজ্ঞাপন - বারিন্দ পোস্ট';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h1 class="h1 mb-0">বিজ্ঞাপন সুবিধা</h1>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">আমাদের সম্পর্কে</h2>
                        <p>বারিন্দ পোস্ট রাজশাহী অঞ্চল থেকে পরিচালিত একটি শীর্ষস্থানীয় অনলাইন সংবাদ পোর্টাল। আমাদের দৈনিক হাজার হাজার পাঠক রয়েছে যারা বিশ্বাসযোগ্য ও সময়োপযোগী সংবাদ খুঁজছেন। আপনার ব্র্যান্ড আমাদের পাঠকদের কাছে পৌঁছানোর জন্য আমাদের বিজ্ঞাপন সুবিধা ব্যবহার করুন।</p>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">আমাদের পাঠক</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <i class="fas fa-users text-danger fa-2x mb-2"></i>
                                        <h5 class="card-title">দৈনিক ভিজিটর</h5>
                                        <p class="card-text h4 text-danger">১০,০০০+</p>
                                    </div>
                                </div>
                            </div>
                            <!--
                            <div class="col-md-6 mb-3">
                                <div class="card border-danger">
                                    <div class="card-body text-center">
                                        <i class="fas fa-map-marker-alt text-danger fa-2x mb-2"></i>
                                        <h5 class="card-title">প্রধান এলাকা</h5>
                                        <p class="card-text">রাজশাহী, চাঁপাইনবাবগঞ্জ, নাটোর</p>
                                    </div>
                                </div>
                            </div>
                            -->
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">বিজ্ঞাপনের ধরন</h2>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-danger">
                                            <i class="fas fa-banner me-2"></i>
                                            ব্যানার বিজ্ঞাপন
                                        </h5>
                                        <ul class="list-unstyled">
                                            <li>• হোমপেজ টপ ব্যানার</li>
                                            <li>• সাইডবার বিজ্ঞাপন</li>
                                            <li>• ইন-আর্টিকেল বিজ্ঞাপন</li>
                                            <li>• মোবাইল অপটিমাইজড</li>
                                        </ul>
                                        <p class="text-muted small">মূল্য: ৫,০০০ টাকা/মাস</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-danger">
                                            <i class="fas fa-newspaper me-2"></i>
                                            স্পনসরড কনটেন্ট
                                        </h5>
                                        <ul class="list-unstyled">
                                            <li>• স্পনসরড আর্টিকেল</li>
                                            <li>• ব্র্যান্ড স্টোরি</li>
                                            <li>• প্রোডাক্ট রিভিউ</li>
                                            <li>• ইভেন্ট কভারেজ</li>
                                        </ul>
                                        <p class="text-muted small">মূল্য: ১০,০০০ টাকা/আর্টিকেল</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title text-danger">
                                            <i class="fas fa-video me-2"></i>
                                            ভিডিও বিজ্ঞাপন
                                        </h5>
                                        <ul class="list-unstyled">
                                            <li>• প্রি-রোল বিজ্ঞাপন</li>
                                            <li>• মিড-রোল বিজ্ঞাপন</li>
                                            <li>• স্পনসরড ভিডিও</li>
                                            <li>• লাইভ স্ট্রিমিং</li>
                                        </ul>
                                        <p class="text-muted small">মূল্য: ১৫,০০০ টাকা/মাস</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">বিজ্ঞাপনের সুবিধা</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-chart-line text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">উচ্চ এনগেজমেন্ট</h6>
                                        <p class="small text-muted">আমাদের পাঠকরা গড়ে ৫ মিনিট সাইটে থাকেন</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-mobile-alt text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">মোবাইল ফ্রেন্ডলি</h6>
                                        <p class="small text-muted">৬০% পাঠক মোবাইল থেকে ভিজিট করেন</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-bullseye text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">টার্গেটেড অডিয়েন্স</h6>
                                        <p class="small text-muted">স্থানীয় ব্যবসায়ী ও পেশাজীবীদের কাছে পৌঁছান</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-analytics text-danger fa-lg me-3 mt-1"></i>
                                    <div>
                                        <h6 class="fw-bold">ডিটেইলড রিপোর্ট</h6>
                                        <p class="small text-muted">নিয়মিত ক্লিক ও ভিউ রিপোর্ট পাবেন</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">বিজ্ঞাপন প্যাকেজ</h2>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="card border-success h-100">
                                    <div class="card-header bg-success text-white text-center">
                                        <h5 class="mb-0">বেসিক প্যাকেজ</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="text-success">৫,০০০ টাকা</h3>
                                        <p class="text-muted">মাসিক</p>
                                        <ul class="list-unstyled">
                                            <li>• হোমপেজ ব্যানার (১ মাস)</li>
                                            <li>• সাইডবার বিজ্ঞাপন</li>
                                            <li>• সাপ্তাহিক রিপোর্ট</li>
                                            <li>• মোবাইল অপটিমাইজেশন</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-danger h-100">
                                    <div class="card-header bg-danger text-white text-center">
                                        <h5 class="mb-0">প্রিমিয়াম প্যাকেজ</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="text-danger">১৫,০০০ টাকা</h3>
                                        <p class="text-muted">মাসিক</p>
                                        <ul class="list-unstyled">
                                            <li>• সব ধরনের বিজ্ঞাপন</li>
                                            <li>• স্পনসরড কনটেন্ট (২টি)</li>
                                            <li>• সোশ্যাল মিডিয়া প্রমোশন</li>
                                            <li>• ডেইলি রিপোর্ট</li>
                                            <li>• ডেডিকেটেড অ্যাকাউন্ট ম্যানেজার</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="card border-warning h-100">
                                    <div class="card-header bg-warning text-white text-center">
                                        <h5 class="mb-0">কাস্টম প্যাকেজ</h5>
                                    </div>
                                    <div class="card-body text-center">
                                        <h3 class="text-warning">চুক্তি ভিত্তিক</h3>
                                        <p class="text-muted">আপনার প্রয়োজন অনুযায়ী</p>
                                        <ul class="list-unstyled">
                                            <li>• কাস্টম বিজ্ঞাপন সলিউশন</li>
                                            <li>• এক্সক্লুসিভ স্পনসরশিপ</li>
                                            <li>• ইভেন্ট কভারেজ</li>
                                            <li>• ব্র্যান্ড পার্টনারশিপ</li>
                                            <li>• প্রায়োরিটি সাপোর্ট</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="h4 text-danger mb-3">যোগাযোগ করুন</h2>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-danger">বিজ্ঞাপন বিভাগ</h6>
                                        <p class="mb-1"><strong>ইমেইল:</strong> info@barindpost.com</p>
                                        <!--
                                        <p class="mb-1"><strong>ফোন:</strong> +880-XXX-XXXXXXX</p>
                                        -->
                                        <p class="mb-1"><strong>কর্মসময়:</strong> সকাল ৯টা - সন্ধ্যা ৬টা</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="text-danger">দ্রুত যোগাযোগ</h6>
                                        <a href="/contact" class="btn btn-danger btn-sm">
                                            <i class="fas fa-envelope me-2"></i>
                                            যোগাযোগ ফর্ম
                                        </a>
                                        <a href="https://wa.me/880XXXXXXXXX" class="btn btn-success btn-sm ms-2">
                                            <i class="fab fa-whatsapp me-2"></i>
                                            WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>বিশেষ অফার:</strong> প্রথম ৩ মাসের জন্য ২০% ছাড়। যোগাযোগ করে জানুন।
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?> 