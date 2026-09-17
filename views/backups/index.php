<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-database-fill-gear"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Backups</span>
                <span class="stat-value"><?= count($backups) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-hdd-stack"></i></div>
            <div class="stat-content">
                <span class="stat-label">Storage Used</span>
                <span class="stat-value"><?= BackupManager::humanSize($totalSize) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body d-flex align-items-center justify-content-end">
                <form method="POST" action="<?= BASE_URL ?>/index.php?page=backups&action=create" 
                      onsubmit="this.querySelector('button').disabled=true; this.querySelector('button').innerHTML='<span class=\'spinner-border spinner-border-sm\'></span> Creating...';">
                    <?= CSRF::field() ?>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-cloud-arrow-up"></i> Create Full Backup Now
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info small">
    <i class="bi bi-info-circle-fill"></i>
    <strong>What's included in a full backup:</strong>
    <ul class="mb-0 mt-1">
        <li>Database dump (all tables + data)</li>
        <li>All uploaded medical image files</li>
        <li>Manifest with timestamp, size, and image count</li>
        <li>SHA-256 checksum for integrity verification</li>
    </ul>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><i class="bi bi-x-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if (!empty($success)): ?>
    <div class="alert alert-success"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($warning)): ?>
    <div class="alert alert-warning"><i class="bi bi-exclamation-triangle"></i> <?= htmlspecialchars($warning) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Backup History</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($backups)): ?>
            <div class="empty-state">
                <i class="bi bi-database"></i>
                <h5>No backups yet</h5>
                <p>Click "Create Full Backup Now" to generate your first backup.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>File Name</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Images</th>
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
                                <td><code class="small"><?= htmlspecialchars($b['file_name']) ?></code></td>
                                <td>
                                    <span class="badge bg-<?= ($b['backup_type'] ?? 'database_only') === 'full' ? 'success' : 'secondary' ?>">
                                        <?= ($b['backup_type'] ?? 'database_only') === 'full' ? 'Full' : 'DB Only' ?>
                                    </span>
                                </td>
                                <td><?= $b['file_size'] ? BackupManager::humanSize((int)$b['file_size']) : '-' ?></td>
                                <td><?= !empty($b['includes_images']) ? '✓' : '-' ?></td>
                                <td>
                                    <small><?= htmlspecialchars($b['created_by_name'] ?? '-') ?></small>
                                    <?php if (!empty($b['restored_at'])): ?>
                                        <br><small class="text-info"><i class="bi bi-arrow-counterclockwise"></i> restored <?= date('d M H:i', strtotime($b['restored_at'])) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><small><?= date('d M Y H:i', strtotime($b['created_at'])) ?></small></td>
                                <td>
                                    <span class="badge bg-<?= $b['backup_status'] === 'success' ? 'success' : 'danger' ?>">
                                        <?= htmlspecialchars($b['backup_status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($b['backup_status'] === 'success'): ?>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/index.php?page=backups&action=download&id=<?= $b['backup_id'] ?>" 
                                               class="btn btn-outline-success" title="Download">
                                                <i class="bi bi-download"></i>
                                            </a>
                                            <form method="POST" action="<?= BASE_URL ?>/index.php?page=backups&action=verify" class="d-inline">
                                                <?= CSRF::field() ?>
                                                <input type="hidden" name="id" value="<?= $b['backup_id'] ?>">
                                                <button type="submit" class="btn btn-outline-info" title="Verify integrity">
                                                    <i class="bi bi-shield-check"></i>
                                                </button>
                                            </form>
                                            <?php if (($b['backup_type'] ?? '') === 'full'): ?>
                                                <button type="button" class="btn btn-outline-warning" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#restoreModal<?= $b['backup_id'] ?>"
                                                        title="Restore">
                                                    <i class="bi bi-arrow-counterclockwise"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted"><?= htmlspecialchars($b['notes'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <?php if (($b['backup_type'] ?? '') === 'full' && $b['backup_status'] === 'success'): ?>
                            <!-- Restore confirmation modal -->
                            <div class="modal fade" id="restoreModal<?= $b['backup_id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?page=backups&action=restore">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="id" value="<?= $b['backup_id'] ?>">
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title"><i class="bi bi-exclamation-triangle-fill"></i> Restore Backup</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Restore from:</strong> <code><?= htmlspecialchars($b['file_name']) ?></code></p>
                                                <p class="small">Created: <?= date('d M Y H:i', strtotime($b['created_at'])) ?></p>
                                                <hr>
                                                <p class="text-danger small">
                                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                                    <strong>Warning:</strong> This will overwrite existing image files.
                                                </p>
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" name="restore_database" id="restoreDb<?= $b['backup_id'] ?>" value="1">
                                                    <label class="form-check-label" for="restoreDb<?= $b['backup_id'] ?>">
                                                        Also restore database (OVERWRITES current data)
                                                    </label>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label small">Type <strong>RESTORE</strong> to confirm:</label>
                                                    <input type="text" name="confirm" class="form-control" placeholder="RESTORE" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-warning">
                                                    <i class="bi bi-arrow-counterclockwise"></i> Restore Backup
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="alert alert-secondary small mt-3">
    <i class="bi bi-info-circle"></i>
    <strong>Production note:</strong> This prototype stores backups locally.
    For a real hospital deployment, backups should be stored off-site
    (encrypted cloud storage or external media) with automated scheduling.
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
