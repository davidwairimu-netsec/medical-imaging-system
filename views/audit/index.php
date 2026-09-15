<?php
$pageTitle = 'Audit Logs';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card">
            <span class="stat-label">Total Events</span>
            <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <span class="stat-label">Duplicate Attempts</span>
            <span class="stat-value text-warning"><?= $stats['duplicate_attempts'] ?? 0 ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <span class="stat-label">Failed Logins</span>
            <span class="stat-value text-danger"><?= $stats['failed_logins'] ?? 0 ?></span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card">
            <span class="stat-label">Today's Events</span>
            <span class="stat-value text-info"><?= $stats['today_events'] ?? 0 ?></span>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="audit">
            
            <div class="col-md-3">
                <label class="form-label">Action</label>
                <select name="action" class="form-select">
                    <option value="">All Actions</option>
                    <?php
                    $actions = ['USER_LOGIN','USER_LOGOUT','USER_LOGIN_FAILED','PATIENT_CREATE','PATIENT_UPDATE',
                                'PATIENT_ARCHIVE','IMAGE_UPLOAD','IMAGE_DUPLICATE_ATTEMPT','IMAGE_VIEW',
                                'IMAGE_DOWNLOAD','IMAGE_UPDATE','IMAGE_ARCHIVE','USER_CREATE','USER_UPDATE',
                                'USER_DISABLE','BACKUP_CREATE'];
                    foreach ($actions as $a): ?>
                        <option value="<?= $a ?>" <?= ($filters['action'] ?? '') === $a ? 'selected' : '' ?>>
                            <?= str_replace('_', ' ', $a) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">User</label>
                <select name="user_id" class="form-select">
                    <option value="">All Users</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['user_id'] ?>" <?= ($filters['user_id'] ?? '') == $u['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['full_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-funnel"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-journal-text"></i> Audit Trail (<?= count($logs) ?> records)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <div class="empty-state">
                <i class="bi bi-journal"></i>
                <h5>No audit records found</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity</th>
                            <th>Description</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td><small><?= date('d M Y H:i:s', strtotime($log['created_at'])) ?></small></td>
                                <td>
                                    <?php if ($log['user_name']): ?>
                                        <strong><?= htmlspecialchars($log['user_name']) ?></strong><br>
                                        <small class="text-muted">@<?= htmlspecialchars($log['username'] ?? '') ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">System</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= str_contains($log['action'], 'FAILED') || str_contains($log['action'], 'DUPLICATE') ? 'warning' : 'secondary' ?>">
                                        <?= htmlspecialchars($log['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['entity_type']): ?>
                                        <small><?= htmlspecialchars($log['entity_type']) ?> #<?= $log['entity_id'] ?></small>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><small><?= htmlspecialchars($log['description'] ?? '') ?></small></td>
                                <td><small><code><?= htmlspecialchars($log['ip_address'] ?? '-') ?></code></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>