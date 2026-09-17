<?php
// No <form> may live in this bar: news_form.php's scripts locate the editor
// with document.querySelector('form') and would grab the first one on the page.
$userName = (string) session('user_name');
$initial  = mb_strtoupper(mb_substr(trim($userName) !== '' ? $userName : '?', 0, 1));
?>
<header class="admin-topbar">
    <div class="admin-topbar__left">
        <button class="admin-topbar__toggle" type="button" id="sidebarToggle" aria-label="Open menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="admin-topbar__crumb">
            <strong>Barind Post</strong> &nbsp;/&nbsp; <?= esc($adminPageLabel ?? 'Admin') ?>
        </div>
    </div>

    <div class="admin-topbar__right">
        <div class="admin-topbar__user">
            <span class="admin-topbar__avatar"><?= esc($initial) ?></span>
            <span><?= esc($userName) ?></span>
            <span class="admin-topbar__role"><?= esc(session('user_role')) ?></span>
        </div>
        <a href="/" target="_blank" rel="noopener" class="btn btn-secondary btn-sm" title="Open public site">
            <i class="fas fa-arrow-up-right-from-square"></i>
        </a>
    </div>
</header>
