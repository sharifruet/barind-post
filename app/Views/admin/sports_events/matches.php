<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="bengali-text mb-0">Fixtures — <?= esc($event['title_bn']) ?></h2>
    <a href="/admin/sports-events/<?= $event['id'] ?>/matches/create" class="btn btn-primary">Add Fixture</a>
</div>
<?= view('admin/sports_events/_nav', ['event' => $event]) ?>

<div class="mb-3">
    <a href="/admin/sports-events/<?= $event['id'] ?>/matches" class="btn btn-sm btn-outline-secondary<?= !$filterStatus ? ' active' : '' ?>">All</a>
    <a href="/admin/sports-events/<?= $event['id'] ?>/matches?status=upcoming" class="btn btn-sm btn-outline-secondary<?= $filterStatus === 'upcoming' ? ' active' : '' ?>">Upcoming</a>
    <a href="/admin/sports-events/<?= $event['id'] ?>/matches?status=live" class="btn btn-sm btn-outline-danger<?= $filterStatus === 'live' ? ' active' : '' ?>">Live</a>
    <a href="/admin/sports-events/<?= $event['id'] ?>/matches?status=finished" class="btn btn-sm btn-outline-secondary<?= $filterStatus === 'finished' ? ' active' : '' ?>">Results</a>
    <?php foreach ($groups as $g): ?>
        <a href="/admin/sports-events/<?= $event['id'] ?>/matches?group=<?= urlencode($g) ?>" class="btn btn-sm btn-outline-info<?= $filterGroup === $g ? ' active' : '' ?>">Group <?= esc($g) ?></a>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr><th>Date</th><th>Match</th><th>Stage</th><th>Stadium</th><th>Status</th><th>Score</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($matches)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4">No matches found.</td></tr>
                <?php else: ?>
                    <?php foreach ($matches as $m): ?>
                        <?php
                        $sd = $m['sport_data'];
                        $isCricket = str_starts_with($event['sport_profile'], 'cricket');
                        $scoreDisplay = $isCricket
                            ? sports_format_cricket_scoreline($sd['innings'] ?? [])
                            : sports_format_football_score($sd, $m['status']);
                        ?>
                        <tr>
                            <td><?= $m['kickoff_at'] ? date('M d, H:i', strtotime($m['kickoff_at'])) : '—' ?></td>
                            <td class="bengali-text"><?= esc($m['team_a_name']) ?> vs <?= esc($m['team_b_name']) ?></td>
                            <td><?= esc(sports_stage_label($event['sport_profile'], $m['stage'])) ?><?= $m['group_name'] ? ' (' . esc($m['group_name']) . ')' : '' ?></td>
                            <td><?= esc($m['venue_name'] ?? '—') ?></td>
                            <td><span class="badge bg-<?= sports_is_live($m['status']) ? 'danger' : (sports_is_finished($m['status']) ? 'secondary' : 'primary') ?>"><?= esc(sports_match_status_label($event['sport_profile'], $m['status'])) ?></span></td>
                            <td><strong><?= esc($scoreDisplay) ?></strong></td>
                            <td>
                                <a href="/admin/sports-events/<?= $event['id'] ?>/matches/edit/<?= $m['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <form method="post" action="/admin/sports-events/<?= $event['id'] ?>/matches/delete/<?= $m['id'] ?>" class="d-inline" onsubmit="return confirm('Delete match?')"><?= csrf_field() ?>
                                    <button class="btn btn-sm btn-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
