<?php $eventId = $event['id']; ?>
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link<?= url_is('admin/sports-events/manage/' . $eventId) ? ' active' : '' ?>" href="/admin/sports-events/manage/<?= $eventId ?>">Overview</a>
    </li>
    <li class="nav-item">
        <a class="nav-link<?= url_is('admin/sports-events/' . $eventId . '/teams*') ? ' active' : '' ?>" href="/admin/sports-events/<?= $eventId ?>/teams">Teams</a>
    </li>
    <li class="nav-item">
        <a class="nav-link<?= url_is('admin/sports-events/' . $eventId . '/matches*') ? ' active' : '' ?>" href="/admin/sports-events/<?= $eventId ?>/matches">Fixtures</a>
    </li>
    <li class="nav-item">
        <a class="nav-link<?= url_is('admin/sports-events/' . $eventId . '/standings*') ? ' active' : '' ?>" href="/admin/sports-events/<?= $eventId ?>/standings">Standings</a>
    </li>
    <li class="nav-item">
        <a class="nav-link<?= url_is('admin/sports-events/' . $eventId . '/news*') ? ' active' : '' ?>" href="/admin/sports-events/<?= $eventId ?>/news">News</a>
    </li>
    <li class="nav-item">
        <a class="nav-link<?= url_is('admin/sports-events/edit/' . $eventId) ? ' active' : '' ?>" href="/admin/sports-events/edit/<?= $eventId ?>">Settings</a>
    </li>
</ul>
