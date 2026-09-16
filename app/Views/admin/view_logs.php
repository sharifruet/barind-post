<?= $this->extend('admin/layout') ?>

<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                    <h2 class="mb-0">
                        <i class="fas fa-file-alt"></i> 
                        Application Logs
                    </h2>
                    <a href="/admin/logs" class="btn btn-light btn-sm">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </a>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Log Information:</strong> Showing the last 100 log entries from <code><?= isset($currentLogFile) ? $currentLogFile : 'today\'s log file' ?></code>. 
                        This helps you monitor prayer times API requests and any errors that occur.
                    </div>
                    
                    <?php if (!empty($availableLogFiles)): ?>
                    <div class="alert alert-secondary">
                        <strong>Available Log Files:</strong>
                        <div class="mt-2">
                            <?php foreach (array_slice($availableLogFiles, 0, 5) as $logFile): ?>
                                <span class="badge bg-secondary me-1"><?= $logFile ?></span>
                            <?php endforeach; ?>
                            <?php if (count($availableLogFiles) > 5): ?>
                                <span class="text-muted">... and <?= count($availableLogFiles) - 5 ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (empty($logs)): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            No log entries found for today. Logs will appear here when you start using the prayer times API.
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Level</th>
                                        <th>Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $log): ?>
                                        <?php
                                        // Parse log entry
                                        $parts = explode(' --> ', $log);
                                        if (count($parts) >= 2) {
                                            $timestamp = $parts[0];
                                            $message = $parts[1];
                                            
                                            // Determine log level and color
                                            $level = 'INFO';
                                            $badgeClass = 'badge-info';
                                            if (strpos($message, 'ERROR') !== false) {
                                                $level = 'ERROR';
                                                $badgeClass = 'badge-danger';
                                            } elseif (strpos($message, 'WARNING') !== false) {
                                                $level = 'WARNING';
                                                $badgeClass = 'badge-warning';
                                            }
                                        } else {
                                            $timestamp = date('Y-m-d H:i:s');
                                            $message = $log;
                                            $level = 'INFO';
                                            $badgeClass = 'badge-info';
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-muted small"><?= htmlspecialchars($timestamp) ?></td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?>"><?= $level ?></span>
                                            </td>
                                            <td>
                                                <code class="small"><?= htmlspecialchars($message) ?></code>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>

                    <div class="mt-4">
                        <h5>How to Monitor Prayer Times API:</h5>
                        <ul>
                            <li><strong>INFO logs:</strong> Show successful API requests and data processing</li>
                            <li><strong>ERROR logs:</strong> Show failed API requests or database errors</li>
                            <li><strong>Progress tracking:</strong> Look for "Processing X days" and "Stored X/Y records" messages</li>
                            <li><strong>API issues:</strong> Check for "API request failed" or "JSON decode error" messages</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    font-size: 0.75em;
}

code {
    background-color: #f8f9fa;
    padding: 2px 4px;
    border-radius: 3px;
    font-size: 0.85em;
    word-break: break-all;
}

.table td {
    vertical-align: middle;
}
</style>

<?= $this->endSection() ?>
