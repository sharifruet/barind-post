<?= $this->extend('admin/layout') ?>
<?= $this->section('content') ?>
<?php
$isEdit = !empty($match);
$action = $isEdit
    ? '/admin/sports-events/' . $event['id'] . '/matches/edit/' . $match['id']
    : '/admin/sports-events/' . $event['id'] . '/matches/create';
$sd = $isEdit ? ($match['sport_data'] ?? []) : [];
$isCricket = str_starts_with($event['sport_profile'], 'cricket');
if ($isCricket && empty($sd)) {
    $sd = sports_default_cricket_data(str_contains($event['sport_profile'], 'odi') ? 'odi' : 't20', $profile['default_config']['overs'] ?? 20);
} elseif (!$isCricket && empty($sd)) {
    $sd = sports_default_football_data();
}
$innings = $sd['innings'] ?? sports_default_cricket_data()['innings'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><?= $isEdit ? 'Edit Match' : 'Add Fixture' ?></h2>
    <a href="/admin/sports-events/<?= $event['id'] ?>/matches" class="btn btn-secondary">Back</a>
</div>
<?php if (session()->getFlashdata('success')): ?><div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

<form method="post" action="<?= $action ?>">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Match Details</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team A *</label>
                            <select name="participant_a_id" class="form-select" required>
                                <option value="">Select...</option>
                                <?php foreach ($teams as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= old('participant_a_id', $match['participant_a_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name_bn']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Team B *</label>
                            <select name="participant_b_id" class="form-select" required>
                                <option value="">Select...</option>
                                <?php foreach ($teams as $t): ?>
                                    <option value="<?= $t['id'] ?>" <?= old('participant_b_id', $match['participant_b_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= esc($t['name_bn']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kickoff Date & Time</label>
                            <input type="datetime-local" name="kickoff_at" class="form-control" value="<?= esc(old('kickoff_at', $match['kickoff_at'] ?? '' ? date('Y-m-d\TH:i', strtotime($match['kickoff_at'])) : '')) ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stadium</label>
                            <select name="venue_id" class="form-select">
                                <option value="">—</option>
                                <?php foreach ($venues as $v): ?>
                                    <option value="<?= $v['id'] ?>" <?= old('venue_id', $match['venue_id'] ?? '') == $v['id'] ? 'selected' : '' ?>><?= esc($v['name_bn']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <?php foreach ($profile['match_statuses'] as $key => $label): ?>
                                    <option value="<?= esc($key) ?>" <?= old('status', $match['status'] ?? 'scheduled') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Stage</label>
                            <select name="stage" class="form-select">
                                <?php foreach ($profile['default_stages'] as $key => $label): ?>
                                    <option value="<?= esc($key) ?>" <?= old('stage', $match['stage'] ?? 'group') === $key ? 'selected' : '' ?>><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Group</label>
                            <input type="text" name="group_name" class="form-control" value="<?= esc(old('group_name', $match['group_name'] ?? '')) ?>" list="groupList">
                            <datalist id="groupList"><?php foreach ($groups as $g): ?><option value="<?= esc($g) ?>"><?php endforeach; ?></datalist>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Match Slug</label>
                            <input type="text" name="match_slug" class="form-control" value="<?= esc(old('match_slug', $match['match_slug'] ?? '')) ?>" placeholder="auto-generated if empty">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Referee</label>
                            <input type="text" name="referee" class="form-control" value="<?= esc(old('referee', $match['referee'] ?? '')) ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Attendance</label>
                            <input type="number" name="attendance" class="form-control" value="<?= esc(old('attendance', $match['attendance'] ?? '')) ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header"><?= $isCricket ? 'Cricket Score' : 'Football Score' ?></div>
                <div class="card-body">
                    <?php if ($isCricket): ?>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Team A — Runs</label>
                                <input type="number" name="inn_a_runs" class="form-control" value="<?= esc($innings[0]['runs'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Wickets</label>
                                <input type="number" name="inn_a_wickets" class="form-control" value="<?= esc($innings[0]['wickets'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Overs</label>
                                <input type="text" name="inn_a_overs" class="form-control" value="<?= esc($innings[0]['overs'] ?? '') ?>" placeholder="20.0">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Team B — Runs</label>
                                <input type="number" name="inn_b_runs" class="form-control" value="<?= esc($innings[1]['runs'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Wickets</label>
                                <input type="number" name="inn_b_wickets" class="form-control" value="<?= esc($innings[1]['wickets'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Overs</label>
                                <input type="text" name="inn_b_overs" class="form-control" value="<?= esc($innings[1]['overs'] ?? '') ?>" placeholder="20.0">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Result Text (Bengali)</label>
                            <input type="text" name="result_text" class="form-control bengali-text" value="<?= esc($sd['result_text'] ?? '') ?>" placeholder="ব্রাজিল ৫ উইকেটে জিতেছে">
                        </div>
                        <input type="hidden" name="overs_limit" value="<?= esc($sd['overs_limit'] ?? $profile['default_config']['overs'] ?? 20) ?>">
                    <?php else: ?>
                        <div class="row text-center mb-3">
                            <div class="col-5">
                                <label class="form-label">Team A Score</label>
                                <input type="number" name="a_score" class="form-control form-control-lg text-center" value="<?= esc($sd['a_score'] ?? '') ?>">
                                <small>HT: <input type="number" name="a_ht" class="form-control form-control-sm d-inline-block" style="width:60px" value="<?= esc($sd['a_ht'] ?? '') ?>"></small>
                                <small class="ms-2">Pen: <input type="number" name="a_pen" class="form-control form-control-sm d-inline-block" style="width:60px" value="<?= esc($sd['a_pen'] ?? '') ?>"></small>
                            </div>
                            <div class="col-2 d-flex align-items-center justify-content-center"><h3>vs</h3></div>
                            <div class="col-5">
                                <label class="form-label">Team B Score</label>
                                <input type="number" name="b_score" class="form-control form-control-lg text-center" value="<?= esc($sd['b_score'] ?? '') ?>">
                                <small>HT: <input type="number" name="b_ht" class="form-control form-control-sm d-inline-block" style="width:60px" value="<?= esc($sd['b_ht'] ?? '') ?>"></small>
                                <small class="ms-2">Pen: <input type="number" name="b_pen" class="form-control form-control-sm d-inline-block" style="width:60px" value="<?= esc($sd['b_pen'] ?? '') ?>"></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between">
                    <span>Timeline Events</span>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addTimelineRow">+ Add Event</button>
                </div>
                <div class="card-body" id="timelineContainer">
                    <?php if (empty($timeline)): ?>
                        <div class="row g-2 timeline-row mb-2">
                            <div class="col-md-2"><input type="text" name="tl_minute[]" class="form-control form-control-sm" placeholder="Min"></div>
                            <div class="col-md-2">
                                <select name="tl_type[]" class="form-select form-select-sm">
                                    <option value="">Type</option>
                                    <?php foreach ($profile['timeline_types'] as $tk => $tl): ?>
                                        <option value="<?= esc($tk) ?>"><?= esc($tl) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="tl_team[]" class="form-select form-select-sm">
                                    <option value="">Team</option>
                                    <?php foreach ($teams as $t): ?>
                                        <option value="<?= $t['id'] ?>"><?= esc($t['short_code'] ?: $t['name_bn']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3"><input type="text" name="tl_player[]" class="form-control form-control-sm" placeholder="Player"></div>
                            <div class="col-md-3"><input type="text" name="tl_detail[]" class="form-control form-control-sm" placeholder="Detail"></div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($timeline as $tl): ?>
                            <div class="row g-2 timeline-row mb-2">
                                <div class="col-md-2"><input type="text" name="tl_minute[]" class="form-control form-control-sm" value="<?= esc($tl['event_minute'] ?? '') ?>"></div>
                                <div class="col-md-2">
                                    <select name="tl_type[]" class="form-select form-select-sm">
                                        <?php foreach ($profile['timeline_types'] as $tk => $tlabel): ?>
                                            <option value="<?= esc($tk) ?>" <?= ($tl['event_type'] ?? '') === $tk ? 'selected' : '' ?>><?= esc($tlabel) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="tl_team[]" class="form-select form-select-sm">
                                        <option value="">Team</option>
                                        <?php foreach ($teams as $t): ?>
                                            <option value="<?= $t['id'] ?>" <?= ($tl['participant_id'] ?? '') == $t['id'] ? 'selected' : '' ?>><?= esc($t['short_code'] ?: $t['name_bn']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3"><input type="text" name="tl_player[]" class="form-control form-control-sm" value="<?= esc($tl['player_name'] ?? '') ?>"></div>
                                <div class="col-md-3"><input type="text" name="tl_detail[]" class="form-control form-control-sm" value="<?= esc($tl['detail'] ?? '') ?>"></div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Summary & News</div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Short Summary (Bengali)</label>
                        <textarea name="summary_bn" class="form-control bengali-text" rows="5"><?= esc(old('summary_bn', $match['summary_bn'] ?? '')) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Link News Article</label>
                        <select name="news_id" class="form-select">
                            <option value="">— None —</option>
                            <?php foreach ($newsArticles as $n): ?>
                                <option value="<?= $n['id'] ?>" <?= old('news_id', $match['news_id'] ?? '') == $n['id'] ? 'selected' : '' ?>><?= esc(mb_substr($n['title'], 0, 60)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><?= $isEdit ? 'Update Match' : 'Create Match' ?></button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.getElementById('addTimelineRow')?.addEventListener('click', function() {
    const container = document.getElementById('timelineContainer');
    const first = container.querySelector('.timeline-row');
    if (first) container.appendChild(first.cloneNode(true));
});
</script>
<?= $this->endSection() ?>
