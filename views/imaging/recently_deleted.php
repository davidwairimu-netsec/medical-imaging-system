<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-trash"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Deleted</span>
                <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-warning">
            <div class="stat-icon"><i class="bi bi-calendar-day"></i></div>
            <div class="stat-content">
                <span class="stat-label">Today</span>
                <span class="stat-value"><?= number_format($stats['today'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card stat-card-info">
            <div class="stat-icon"><i class="bi bi-calendar-week"></i></div>
            <div class="stat-content">
                <span class="stat-label">This Week</span>
                <span class="stat-value"><?= number_format($stats['this_week'] ?? 0) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-archive"></i> Recently Deleted Images</h5>
        <a href="<?= BASE_URL ?>/index.php?page=imaging" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Active Images
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($deleted)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No deleted images</h5>
                <p>Archived images will appear here, with the ability to restore them.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Type / Body Part</th>
                            <th>Study Date</th>
                            <th style="width: 200px;">Deletion Reason</th>
                            <th>Requested By</th>
                            <th>Approved At</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deleted as $d): ?>
                            <tr>
                                <td><strong>#<?= $d['image_id'] ?></strong></td>
                                <td>
                                    <strong><?= htmlspecialchars($d['patient_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($d['hospital_number']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($d['imaging_type']) ?></span><br>
                                    <small><?= htmlspecialchars($d['body_part']) ?></small>
                                </td>
                                <td><?= date('d M Y', strtotime($d['study_date'])) ?></td>
                                <td>
                                    <?php if (!empty($d['deletion_reason'])): ?>
                                        <div class="alert alert-warning small mb-0 p-2">
                                            <i class="bi bi-info-circle"></i>
                                            <?= htmlspecialchars(substr($d['deletion_reason'], 0, 80)) ?>
                                            <?= strlen($d['deletion_reason']) > 80 ? '...' : '' ?>
                                        </div>
                                    <?php else: ?>
                                        <small class="text-muted">Direct admin delete</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($d['requested_by_name'])): ?>
                                        <i class="bi bi-person-check"></i>
                                        <small><?= htmlspecialchars($d['requested_by_name']) ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <small>
                                        <?= !empty($d['approved_at']) ? date('d M Y H:i', strtotime($d['approved_at'])) : date('d M Y H:i', strtotime($d['updated_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $d['image_id'] ?>" 
                                           class="btn btn-outline-primary" target="_blank" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?page=imaging&action=restore" 
                                              class="d-inline" 
                                              onsubmit="return confirm('Restore image #<?= $d['image_id'] ?>? It will become active again.');">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="id" value="<?= $d['image_id'] ?>">
                                            <button type="submit" class="btn btn-outline-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i> Restore
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="alert alert-info small mt-3">
    <i class="bi bi-info-circle"></i>
    <strong>Note:</strong> Restored images become active immediately. Their original metadata, hash, and file are preserved.
    Restoring does not require a reason, but the action is logged in the audit trail.
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
