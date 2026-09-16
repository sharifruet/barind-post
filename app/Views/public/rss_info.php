<?php
$title = 'RSS Feed - বারিন্দ পোস্ট';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>

<style>
    .rss-icon { color: #ff6600; }
    .feed-card { border-left: 4px solid #ff6600; }
</style>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-5">
                <h1 class="display-4 mb-3">
                    <i class="fas fa-rss rss-icon"></i> RSS Feed
                </h1>
                <p class="lead">বারিন্দ পোস্টের সর্বশেষ সংবাদ পেতে RSS Feed ব্যবহার করুন</p>
            </div>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h3>RSS কি?</h3>
                    <p>RSS (Really Simple Syndication) হল একটি ওয়েব ফিড ফরম্যাট যা আপনাকে আপনার পছন্দের ওয়েবসাইটের সর্বশেষ আপডেট পেতে সাহায্য করে। RSS Feed ব্যবহার করে আপনি বারিন্দ পোস্টের নতুন সংবাদগুলি সহজেই দেখতে পারবেন।</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card feed-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-rss rss-icon"></i> সব সংবাদের RSS Feed
                            </h5>
                            <p class="card-text">বারিন্দ পোস্টে প্রকাশিত সব সংবাদের RSS Feed</p>
                            <a href="/rss" class="btn btn-primary" target="_blank">
                                <i class="fas fa-external-link-alt"></i> RSS Feed দেখুন
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card feed-card h-100">
                        <div class="card-body">
                            <h5 class="card-title">
                                <i class="fas fa-folder rss-icon"></i> বিভাগ অনুযায়ী RSS Feed
                            </h5>
                            <p class="card-text">নির্দিষ্ট বিভাগের সংবাদের জন্য আলাদা RSS Feed</p>
                            <div class="mt-3">
                                <?php foreach ($categories as $category): ?>
                                    <a href="/rss/category/<?= esc($category['slug']) ?>" class="btn btn-outline-secondary btn-sm me-2 mb-2" target="_blank">
                                        <?= esc($category['name'], 'raw') ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h3>RSS Feed কিভাবে ব্যবহার করবেন?</h3>
                    <ol>
                        <li><strong>RSS Reader ডাউনলোড করুন:</strong> Feedly, Inoreader, বা NewsBlur এর মত RSS Reader ব্যবহার করুন</li>
                        <li><strong>Feed URL যোগ করুন:</strong> উপরের RSS Feed লিংকগুলি আপনার RSS Reader এ যোগ করুন</li>
                        <li><strong>সংবাদ পড়ুন:</strong> নতুন সংবাদগুলি আপনার RSS Reader এ স্বয়ংক্রিয়ভাবে আসবে</li>
                    </ol>
                    
                    <h4 class="mt-4">জনপ্রিয় RSS Readers:</h4>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <a href="https://feedly.com" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Feedly
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="https://www.inoreader.com" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> Inoreader
                            </a>
                        </div>
                        <div class="col-md-4 mb-2">
                            <a href="https://newsblur.com" class="btn btn-outline-primary btn-sm" target="_blank">
                                <i class="fas fa-external-link-alt"></i> NewsBlur
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
