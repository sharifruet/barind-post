<?php
$title = 'যোগাযোগ - বারিন্দ পোস্ট';
?>

<?= $this->extend('public/layout') ?>

<?= $this->section('content') ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h1 class="h3 mb-0">যোগাযোগ করুন</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h3 class="h5 text-danger mb-3">আমাদের সাথে যোগাযোগ</h3>
                            <div class="mb-3">
                                <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                <strong>ঠিকানা:</strong><br>
                                অস্থায়ী কার্যালয়<br>
                                মহিশালবাড়ী, গোদাগাড়ী, রাজশাহী, বাংলাদেশ
                            </div>
                            <!--
                            <div class="mb-3">
                                <i class="fas fa-phone text-primary me-2"></i>
                                <strong>ফোন:</strong><br>
                                +880-XXX-XXXXXXX
                            </div>
                            -->
                            <div class="mb-3">
                                <i class="fas fa-envelope text-danger me-2"></i>
                                <strong>ইমেইল:</strong><br>
                                অফিস: info@barindpost.com<br>
                                বার্তা বিভাগ: news@barindpost.com
                            </div>
                            <!--
                            <div class="mb-3">
                                <i class="fas fa-clock text-primary me-2"></i>
                                <strong>কর্মসময়:</strong><br>
                                সকাল ৯টা - সন্ধ্যা ৬টা<br>
                                (শুক্রবার বন্ধ)
                            </div>
                            -->
                        </div>
                        <div class="col-md-6 mb-4">
                            <h3 class="h5 text-danger mb-3">সামাজিক যোগাযোগ</h3>
                            <div class="mb-2">
                                <a href="https://facebook.com/barindpost" target="_blank" class="text-decoration-none">
                                    <i class="fab fa-facebook text-danger me-2"></i>
                                    Facebook
                                </a>
                            </div>
                            <div class="mb-2">
                                <a href="https://instagram.com/barindpost" target="_blank" class="text-decoration-none">
                                    <i class="fab fa-instagram text-danger me-2"></i>
                                    Instagram
                                </a>
                            </div>
                            <div class="mb-2">
                                <a href="https://x.com/BarindPost" target="_blank" class="text-decoration-none">
                                    <i class="fa-brands fa-x-twitter text-danger me-2"></i>
                                    X (Twitter)
                                </a>
                            </div>
                            <div class="mb-2">
                                <a href="https://barindpost.com" target="_blank" class="text-decoration-none">
                                    <i class="fas fa-globe text-danger me-2"></i>
                                    ওয়েবসাইট
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <h3 class="h5 text-danger mb-3">যোগাযোগ ফর্ম</h3>
                        <form id="contactForm" action="/contact" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">নাম *</label>
                                    <input type="text" class="form-control" id="name" name="name" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">ইমেইল *</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">ফোন নম্বর</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">বিষয় *</label>
                                <select class="form-select" id="subject" name="subject" required>
                                    <option value="">বিষয় নির্বাচন করুন</option>
                                    <option value="general">সাধারণ প্রশ্ন</option>
                                    <option value="news">সংবাদ সম্পর্কিত</option>
                                    <option value="advertising">বিজ্ঞাপন</option>
                                    <option value="technical">টেকনিক্যাল সমস্যা</option>
                                    <option value="feedback">মতামত</option>
                                    <option value="other">অন্যান্য</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">বার্তা *</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required placeholder="আপনার বার্তা লিখুন..."></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="newsletter" name="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        নিউজলেটার সাবস্ক্রাইব করতে চাই
                                    </label>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-danger" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>
                                <span id="submitText">বার্তা পাঠান</span>
                                <span id="submitSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status"></span>
                            </button>
                        </form>
                        
                        <!-- Alert for form response -->
                        <div id="formAlert" class="alert mt-3 d-none"></div>
                    </div>

                    <hr class="my-4">

                    <div class="mb-4">
                        <h3 class="h5 text-danger mb-3">অন্যান্য যোগাযোগ</h3>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h6 class="text-danger">বিজ্ঞাপন বিভাগ</h6>
                                <p class="small mb-1">বিজ্ঞাপন দিতে চাইলে</p>
                                <p class="small mb-1"><strong>ইমেইল:</strong> info@barindpost.com</p>
                               <!-- <p class="small mb-1"><strong>ফোন:</strong> +880-XXX-XXXXXXX</p> -->
                            </div>
                            <div class="col-md-6 mb-3">
                                <h6 class="text-danger">সম্পাদকীয় বিভাগ</h6>
                                <p class="small mb-1">সংবাদ পাঠাতে চাইলে</p>
                                <p class="small mb-1"><strong>ইমেইল:</strong> news@barindpost.com</p>
                               <!-- <p class="small mb-1"><strong>ফোন:</strong> +880-XXX-XXXXXXX</p> -->
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>দ্রষ্টব্য:</strong> আমরা ২৪ ঘণ্টার মধ্যে আপনার বার্তার উত্তর দেবার চেষ্টা করব।
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitText = document.getElementById('submitText');
    const submitSpinner = document.getElementById('submitSpinner');
    const formAlert = document.getElementById('formAlert');

    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading state
        submitBtn.disabled = true;
        submitText.textContent = 'পাঠানো হচ্ছে...';
        submitSpinner.classList.remove('d-none');
        
        // Hide any previous alerts
        formAlert.classList.add('d-none');
        
        // Get form data
        const formData = new FormData(contactForm);
        
        // Send AJAX request
        fetch('/contact', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            submitBtn.disabled = false;
            submitText.textContent = 'বার্তা পাঠান';
            submitSpinner.classList.add('d-none');
            
            // Show response message
            formAlert.classList.remove('d-none');
            formAlert.className = `alert mt-3 ${data.success ? 'alert-success' : 'alert-danger'}`;
            formAlert.innerHTML = `
                <i class="fas ${data.success ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                ${data.message}
            `;
            
            // If successful, reset form
            if (data.success) {
                contactForm.reset();
                
                // Scroll to alert
                formAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        })
        .catch(error => {
            // Reset button state
            submitBtn.disabled = false;
            submitText.textContent = 'বার্তা পাঠান';
            submitSpinner.classList.add('d-none');
            
            // Show error message
            formAlert.classList.remove('d-none');
            formAlert.className = 'alert alert-danger mt-3';
            formAlert.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>
                নেটওয়ার্ক সমস্যা। অনুগ্রহ করে আবার চেষ্টা করুন।
            `;
            
            console.error('Error:', error);
        });
    });
});
</script>

<?= $this->endSection() ?> 