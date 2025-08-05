<?php
$title = 'যোগাযোগ বার্তা - অ্যাডমিন';
?>

<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">যোগাযোগ বার্তা</h1>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="refreshContacts()">
                <i class="fas fa-sync-alt me-1"></i> রিফ্রেশ
            </button>
            <button class="btn btn-outline-success btn-sm" onclick="exportContacts()">
                <i class="fas fa-download me-1"></i> এক্সপোর্ট
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label for="statusFilter" class="form-label">স্ট্যাটাস</label>
                    <select class="form-select" id="statusFilter" onchange="filterContacts()">
                        <option value="">সব</option>
                        <option value="unread">অপঠিত</option>
                        <option value="read">পঠিত</option>
                        <option value="replied">উত্তর দেওয়া</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="subjectFilter" class="form-label">বিষয়</label>
                    <select class="form-select" id="subjectFilter" onchange="filterContacts()">
                        <option value="">সব</option>
                        <option value="general">সাধারণ প্রশ্ন</option>
                        <option value="news">সংবাদ সম্পর্কিত</option>
                        <option value="advertising">বিজ্ঞাপন</option>
                        <option value="technical">টেকনিক্যাল সমস্যা</option>
                        <option value="feedback">মতামত</option>
                        <option value="other">অন্যান্য</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="dateFilter" class="form-label">তারিখ</label>
                    <input type="date" class="form-control" id="dateFilter" onchange="filterContacts()">
                </div>
                <div class="col-md-3">
                    <label for="searchFilter" class="form-label">অনুসন্ধান</label>
                    <input type="text" class="form-control" id="searchFilter" placeholder="নাম, ইমেইল, বার্তা..." onkeyup="filterContacts()">
                </div>
            </div>
        </div>
    </div>

    <!-- Contacts Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="contactsTable">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                            </th>
                            <th>নাম</th>
                            <th>ইমেইল</th>
                            <th>ফোন</th>
                            <th>বিষয়</th>
                            <th>বার্তা</th>
                            <th>তারিখ</th>
                            <th>স্ট্যাটাস</th>
                            <th>কার্যক্রম</th>
                        </tr>
                    </thead>
                    <tbody id="contactsTableBody">
                        <!-- Contacts will be loaded here -->
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <span id="totalContacts">মোট: 0</span>
                </div>
                <nav>
                    <ul class="pagination pagination-sm" id="pagination">
                        <!-- Pagination will be generated here -->
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>

<!-- Contact Detail Modal -->
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">যোগাযোগের বিবরণ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="contactModalBody">
                <!-- Contact details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বন্ধ</button>
                <button type="button" class="btn btn-primary" onclick="replyToContact()">উত্তর দিন</button>
            </div>
        </div>
    </div>
</div>

<!-- Reply Modal -->
<div class="modal fade" id="replyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">উত্তর দিন</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="replyForm">
                    <input type="hidden" id="replyContactId">
                    <div class="mb-3">
                        <label for="replySubject" class="form-label">বিষয়</label>
                        <input type="text" class="form-control" id="replySubject" required>
                    </div>
                    <div class="mb-3">
                        <label for="replyMessage" class="form-label">বার্তা</label>
                        <textarea class="form-control" id="replyMessage" rows="5" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-primary" onclick="sendReply()">পাঠান</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentPage = 1;
let totalPages = 1;
let currentContactId = null;

// Load contacts on page load
document.addEventListener('DOMContentLoaded', function() {
    loadContacts();
});

function loadContacts(page = 1) {
    const status = document.getElementById('statusFilter').value;
    const subject = document.getElementById('subjectFilter').value;
    const date = document.getElementById('dateFilter').value;
    const search = document.getElementById('searchFilter').value;

    fetch(`/admin/contacts/list?page=${page}&status=${status}&subject=${subject}&date=${date}&search=${search}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayContacts(data.contacts);
                displayPagination(data.pagination);
                document.getElementById('totalContacts').textContent = `মোট: ${data.total}`;
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'ডেটা লোড করতে সমস্যা হয়েছে');
        });
}

function displayContacts(contacts) {
    const tbody = document.getElementById('contactsTableBody');
    tbody.innerHTML = '';

    contacts.forEach(contact => {
        const row = document.createElement('tr');
        row.className = contact.status === 'unread' ? 'table-warning' : '';
        
        row.innerHTML = `
            <td>
                <input type="checkbox" class="contact-checkbox" value="${contact.id}">
            </td>
            <td>${contact.name}</td>
            <td>${contact.email}</td>
            <td>${contact.phone || '-'}</td>
            <td>${getSubjectText(contact.subject)}</td>
            <td>${contact.message.substring(0, 50)}${contact.message.length > 50 ? '...' : ''}</td>
            <td>${formatDate(contact.created_at)}</td>
            <td>
                <span class="badge bg-${getStatusColor(contact.status)}">${getStatusText(contact.status)}</span>
            </td>
            <td>
                <button class="btn btn-sm btn-outline-primary" onclick="viewContact(${contact.id})">
                    <i class="fas fa-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-success" onclick="replyContact(${contact.id})">
                    <i class="fas fa-reply"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteContact(${contact.id})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function displayPagination(pagination) {
    const paginationElement = document.getElementById('pagination');
    paginationElement.innerHTML = '';
    
    if (pagination.total_pages <= 1) return;

    // Previous button
    const prevLi = document.createElement('li');
    prevLi.className = `page-item ${pagination.current_page <= 1 ? 'disabled' : ''}`;
    prevLi.innerHTML = `<a class="page-link" href="#" onclick="loadContacts(${pagination.current_page - 1})">পূর্ববর্তী</a>`;
    paginationElement.appendChild(prevLi);

    // Page numbers
    for (let i = 1; i <= pagination.total_pages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === pagination.current_page ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="#" onclick="loadContacts(${i})">${i}</a>`;
        paginationElement.appendChild(li);
    }

    // Next button
    const nextLi = document.createElement('li');
    nextLi.className = `page-item ${pagination.current_page >= pagination.total_pages ? 'disabled' : ''}`;
    nextLi.innerHTML = `<a class="page-link" href="#" onclick="loadContacts(${pagination.current_page + 1})">পরবর্তী</a>`;
    paginationElement.appendChild(nextLi);
}

function filterContacts() {
    currentPage = 1;
    loadContacts(currentPage);
}

function viewContact(id) {
    fetch(`/admin/contacts/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.contact;
                document.getElementById('contactModalBody').innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>নাম:</strong> ${contact.name}</p>
                            <p><strong>ইমেইল:</strong> ${contact.email}</p>
                            <p><strong>ফোন:</strong> ${contact.phone || '-'}</p>
                            <p><strong>বিষয়:</strong> ${getSubjectText(contact.subject)}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>তারিখ:</strong> ${formatDate(contact.created_at)}</p>
                            <p><strong>স্ট্যাটাস:</strong> <span class="badge bg-${getStatusColor(contact.status)}">${getStatusText(contact.status)}</span></p>
                            <p><strong>আইপি ঠিকানা:</strong> ${contact.ip_address}</p>
                            <p><strong>নিউজলেটার:</strong> ${contact.newsletter_subscribed ? 'হ্যাঁ' : 'না'}</p>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <strong>বার্তা:</strong>
                        <p class="mt-2">${contact.message}</p>
                    </div>
                `;
                
                const modal = new bootstrap.Modal(document.getElementById('contactModal'));
                modal.show();
            }
        });
}

function replyContact(id) {
    currentContactId = id;
    fetch(`/admin/contacts/${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const contact = data.contact;
                document.getElementById('replySubject').value = `Re: ${getSubjectText(contact.subject)}`;
                document.getElementById('replyMessage').value = '';
                
                const modal = new bootstrap.Modal(document.getElementById('replyModal'));
                modal.show();
            }
        });
}

function sendReply() {
    const subject = document.getElementById('replySubject').value;
    const message = document.getElementById('replyMessage').value;
    
    if (!subject || !message) {
        showAlert('error', 'সব তথ্য পূরণ করুন');
        return;
    }

    fetch(`/admin/contacts/${currentContactId}/reply`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            subject: subject,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'উত্তর সফলভাবে পাঠানো হয়েছে');
            bootstrap.Modal.getInstance(document.getElementById('replyModal')).hide();
            loadContacts(currentPage);
        } else {
            showAlert('error', data.message);
        }
    });
}

function deleteContact(id) {
    if (confirm('আপনি কি নিশ্চিত যে আপনি এই বার্তাটি মুছে ফেলতে চান?')) {
        fetch(`/admin/contacts/${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', 'বার্তা সফলভাবে মুছে ফেলা হয়েছে');
                loadContacts(currentPage);
            } else {
                showAlert('error', data.message);
            }
        });
    }
}

function refreshContacts() {
    loadContacts(currentPage);
}

function exportContacts() {
    const status = document.getElementById('statusFilter').value;
    const subject = document.getElementById('subjectFilter').value;
    const date = document.getElementById('dateFilter').value;
    const search = document.getElementById('searchFilter').value;

    window.open(`/admin/contacts/export?status=${status}&subject=${subject}&date=${date}&search=${search}`, '_blank');
}

// Helper functions
function getSubjectText(subject) {
    const subjects = {
        'general': 'সাধারণ প্রশ্ন',
        'news': 'সংবাদ সম্পর্কিত',
        'advertising': 'বিজ্ঞাপন',
        'technical': 'টেকনিক্যাল সমস্যা',
        'feedback': 'মতামত',
        'other': 'অন্যান্য'
    };
    return subjects[subject] || subject;
}

function getStatusText(status) {
    const statuses = {
        'unread': 'অপঠিত',
        'read': 'পঠিত',
        'replied': 'উত্তর দেওয়া'
    };
    return statuses[status] || status;
}

function getStatusColor(status) {
    const colors = {
        'unread': 'warning',
        'read': 'info',
        'replied': 'success'
    };
    return colors[status] || 'secondary';
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('bn-BD');
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.querySelector('.container-fluid').insertBefore(alertDiv, document.querySelector('.card'));
    
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?= $this->endSection() ?> 