<?php
$pageTitle = 'Backup Management';
require APP_ROOT . '/views/layouts/main.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <div class="alert alert-info">
            <i class="bi bi-info-circle-fill"></i>
            <strong>Backup Information:</strong> This prototype uses <code>mysqldump</code> to create SQL backups.
            In production, use automated backup infrastructure with off-site storage and encryption.
            Ensure <code>mysqldump</code> is in your system PATH.
        </div>
    </div>
    <div class="col-md-4 text-end">
        <form method="POST" action="<?= BASE_URL ?>/index.php?page=backups&action=create" 
              onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Creating...';">
            <?= CSRF::field() ?>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-database-add"></i> Create Backup Now
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Backup History</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($backups)): ?>
            <div class="empty-state">
                <i class="bi bi-database"></i>
                <h5>No backups yet</h5>
                <p>Click "Create Backup Now" to generate your first database backup.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File Name</th>
                            <th>Size</th>
                            <th>Created By</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($backups as $b): ?>
                            <tr>
                                <td>#<?= $b['backup_id'] ?></td>
                                <td><code><?= htmlspecialchars($b['file_name']) ?></code></td>
                                <td>
                                    <?= $b['file_size'] ? number_format($b['file_size'] / 1024, 1) . ' KB' : '-' ?>
                                </td>
                                <td><?= htmlspecialchars($b['created_by_name'] ?? '-') ?></td>
                                <td><?= date('d M Y H:i', strtotime($b['created_at'])) ?></td>
                                <td>
                                    <span class="badge bg-<?= $b['backup_status'] === 'success' ? 'success' : 'danger' ?>">
                                        <?= htmlspecialchars($b['backup_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['backup_status'] === 'success'): ?>
                                        <a href="<?= BASE_URL ?>/index.php?page=backups&action=download&id=<?= $b['backup_id'] ?>" 
                                           class="btn btn-sm btn-outline-success">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted"><?= htmlspecialchars($b['notes'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>