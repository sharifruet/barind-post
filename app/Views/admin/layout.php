<?php
// A child view's local $title never reaches the layout (only view data does), so
// derive the page label from the URL unless the controller passed one explicitly.
if (! isset($title)) {
    $segments = explode('/', trim((string) uri_string(), '/'));
    $labels   = [
        ''                     => 'Dashboard',
        'news'                 => 'News',
        'categories'           => 'Categories',
        'tags'                 => 'Tags',
        'kickers'              => 'Kickers',
        'users'                => 'Users',
        'roles'                => 'Roles',
        'reporter-roles'       => 'Reporter Roles',
        'incoming'             => 'Incoming',
        'contacts'             => 'Contact Messages',
        'prayer-times'         => 'Prayer Times',
        'sports-events'        => 'Sports Events',
        'photo-card-generator' => 'Photo Cards',
        'logs'                 => 'Logs',
    ];
    $singular = [
        'news'           => 'article',
        'categories'     => 'category',
        'tags'           => 'tag',
        'kickers'        => 'kicker',
        'users'          => 'user',
        'roles'          => 'role',
        'reporter-roles' => 'reporter role',
        'sports-events'  => 'sports event',
    ];
    $key    = $segments[1] ?? '';
    $action = $segments[2] ?? '';
    $title  = $labels[$key] ?? ucwords(str_replace('-', ' ', $key));
    if ($action === 'create' && isset($singular[$key])) {
        $title = 'New ' . $singular[$key];
    } elseif ($action === 'edit' && isset($singular[$key])) {
        $title = 'Edit ' . $singular[$key];
    }
}
$adminPageLabel = $title;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($adminPageLabel) ?> · Barind Post Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-header" content="<?= csrf_header() ?>">
    <script>
    // CSRF for AJAX: every same-origin non-GET fetch()/XMLHttpRequest gets the token
    // header, so the page scripts (image upload, kickers, incoming queue, contacts)
    // need no changes. Forms carry csrf_field() instead; sendBeacon is excluded server-side.
    (function () {
        var t = document.querySelector('meta[name="csrf-token"]'), h = document.querySelector('meta[name="csrf-header"]');
        if (!t || !h) return;
        var token = t.content, header = h.content;
        var sameOrigin = function (u) { u = String(u || ''); return !/^[a-z][a-z0-9+.-]*:\/\//i.test(u) || u.indexOf(location.origin) === 0; };
        var mutating = function (m) { m = String(m || 'GET').toUpperCase(); return m !== 'GET' && m !== 'HEAD' && m !== 'OPTIONS'; };
        var origFetch = window.fetch;
        window.fetch = function (input, init) {
            init = init || {};
            var url = typeof input === 'string' ? input : (input && input.url) || '';
            var method = init.method || (input && input.method) || 'GET';
            if (mutating(method) && sameOrigin(url)) {
                var headers = new Headers(init.headers || (typeof Request !== 'undefined' && input instanceof Request ? input.headers : undefined));
                if (!headers.has(header)) headers.set(header, token);
                init.headers = headers;
            }
            return origFetch.call(this, input, init);
        };
        var open = XMLHttpRequest.prototype.open, send = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function (m, u) { this.__csrf = mutating(m) && sameOrigin(u); return open.apply(this, arguments); };
        XMLHttpRequest.prototype.send = function () { if (this.__csrf) { try { this.setRequestHeader(header, token); } catch (e) {} } return send.apply(this, arguments); };
    })();
    </script>
    <!-- CSP disabled via .htaccess -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?= base_url('assets/css/admin.css') ?>?v=<?= @filemtime(FCPATH . 'assets/css/admin.css') ?: '1' ?>" rel="stylesheet">
    <?= $customStyles ?? '' ?>
</head>
<body>
<div class="admin-shell">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="admin-overlay" id="sidebarOverlay"></div>

    <div class="admin-main">
        <?php include __DIR__ . '/header.php'; ?>

        <main class="admin-content">
            <?= $this->renderSection('content') ?>
        </main>

        <?php include __DIR__ . '/footer.php'; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>

<script>
    // Off-canvas sidebar on small screens.
    (function () {
        var toggle  = document.getElementById('sidebarToggle');
        var sidebar = document.getElementById('sidebar');
        var overlay = document.getElementById('sidebarOverlay');
        if (!toggle || !sidebar || !overlay) return;

        function close() { sidebar.classList.remove('is-open'); overlay.classList.remove('is-open'); }

        toggle.addEventListener('click', function () {
            sidebar.classList.toggle('is-open');
            overlay.classList.toggle('is-open');
        });
        overlay.addEventListener('click', close);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
    })();
</script>
<?= $customScripts ?? '' ?>
</body>
</html>
