<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-hourglass-split"></i> Pending Deletion Requests (<?= count($requests) ?>)</h5>
        <a href="<?= BASE_URL ?>/index.php?page=imaging" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Images
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <i class="bi bi-check-circle text-success"></i>
                <h5>No pending requests</h5>
                <p>All deletion requests have been processed.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Image ID</th>
                            <th>Patient</th>
                            <th>Type / Body Part</th>
                            <th>Requested By</th>
                            <th>Reason</th>
                            <th>Requested</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $r): ?>
                            <tr>
                                <td>
                                    <a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $r['image_id'] ?>" target="_blank">
                                        #<?= $r['image_id'] ?>
                                    </a>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($r['patient_first'] . ' ' . $r['patient_last']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($r['hospital_number']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?= htmlspecialchars($r['imaging_type']) ?></span><br>
                                    <small><?= htmlspecialchars($r['body_part']) ?></small>
                                </td>
                                <td><small><?= htmlspecialchars($r['requester_name']) ?></small></td>
                                <td><small><?= nl2br(htmlspecialchars($r['reason'])) ?></small></td>
                                <td><small><?= date('d M Y H:i', strtotime($r['created_at'])) ?></small></td>
                                <td style="min-width: 320px;">
                                    <form method="POST" action="<?= BASE_URL ?>/index.php?page=imaging&action=reviewDeletion" class="d-inline">
                                        <?= CSRF::field() ?>
                                        <input type="hidden" name="request_id" value="<?= $r['request_id'] ?>">
                                        <input type="hidden" name="decision" value="approve">
                                        <input type="text" name="admin_notes" class="form-control form-control-sm mb-1" placeholder="Optional note">
                                        <div class="btn-group btn-group-sm w-100">
                                            <button type="submit" class="btn btn-success" onclick="return confirm('Approve and archive this image?');">
                                                <i class="bi bi-check-circle"></i> Approve
                                            </button>
                                            <button type="submit" class="btn btn-danger" 
                                                    onclick="this.form.querySelector('[name=decision]').value='reject'; return confirm('Reject this request?');">
                                                <i class="bi bi-x-circle"></i> Reject
                                            </button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Decision History</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($history)): ?>
            <div class="empty-state"><i class="bi bi-inbox"></i><p>No history yet</p></div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Type / Body</th>
                            <th>Requested By</th>
                            <th>Status</th>
                            <th>Reviewed By</th>
                            <th>Reviewed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                            <tr>
                                <td>#<?= $h['image_id'] ?></td>
                                <td>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($h['imaging_type']) ?></span>
                                    <?= htmlspecialchars($h['body_part']) ?>
                                </td>
                                <td><small><?= htmlspecialchars($h['requester_name']) ?></small></td>
                                <td>
                                    <span class="badge bg-<?= $h['request_status'] === 'approved' ? 'success' : 'danger' ?>">
                                        <?= htmlspecialchars($h['request_status']) ?>
                                    </span>
                                </td>
                                <td><small><?= htmlspecialchars($h['reviewer_name'] ?? '-') ?></small></td>
                                <td><small><?= $h['reviewed_at'] ? date('d M Y H:i', strtotime($h['reviewed_at'])) : '-' ?></small></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
