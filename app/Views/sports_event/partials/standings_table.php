<?php $isCricket = str_starts_with($event['sport_profile'], 'cricket'); ?>
<div class="table-responsive">
    <table class="table table-sm table-hover">
        <thead class="table-light">
            <tr>
                <th>#</th><th>দল</th><th>খেলা</th><th>জয়</th>
                <?php if (!$isCricket): ?><th>ড্র</th><?php endif; ?>
                <th>পরাজয়</th>
                <?php if ($isCricket): ?><th>NR</th><th>NRR</th><?php else: ?><th>GF</th><th>GA</th><th>GD</th><?php endif; ?>
                <th>পয়েন্ট</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $i => $row): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td class="bengali-text">
                        <?php if ($row['flag_url']): ?><img src="<?= esc($row['flag_url']) ?>" class="team-flag me-1" alt=""><?php endif; ?>
                        <?= esc($row['name_bn']) ?>
                    </td>
                    <td><?= $row['played'] ?></td>
                    <td><?= $row['won'] ?></td>
                    <?php if (!$isCricket): ?><td><?= $row['drawn'] ?></td><?php endif; ?>
                    <td><?= $row['lost'] ?></td>
                    <?php if ($isCricket): ?>
                        <td><?= $row['nr'] ?? 0 ?></td>
                        <td><?= number_format($row['nrr'] ?? 0, 3) ?></td>
                    <?php else: ?>
                        <td><?= $row['gf'] ?></td>
                        <td><?= $row['ga'] ?></td>
                        <td><?= $row['gd'] ?></td>
                    <?php endif; ?>
                    <td><strong><?= $row['points'] ?></strong></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
