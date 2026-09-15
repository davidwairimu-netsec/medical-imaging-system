<?php require APP_ROOT . '/views/layouts/main.php'; ?>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card stat-card-primary">
            <div class="stat-icon"><i class="bi bi-archive-fill"></i></div>
            <div class="stat-content">
                <span class="stat-label">Total Archived</span>
                <span class="stat-value"><?= number_format($stats['total'] ?? 0) ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card h-100">
            <div class="card-body py-2">
                <div class="row text-center small">
                    <div class="col"><strong>X-ray:</strong> <?= (int)($stats['xray'] ?? 0) ?></div>
                    <div class="col"><strong>CT:</strong> <?= (int)($stats['ct'] ?? 0) ?></div>
                    <div class="col"><strong>MRI:</strong> <?= (int)($stats['mri'] ?? 0) ?></div>
                    <div class="col"><strong>Ultrasound:</strong> <?= (int)($stats['ultrasound'] ?? 0) ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="page" value="imaging">
            <input type="hidden" name="action" value="archived">

            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control"
                       placeholder="Patient name, hospital no., body part..."
                       value="<?= htmlspecialchars($search) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select name="imaging_type" class="form-select">
                    <option value="">All</option>
                    <?php foreach (IMAGING_TYPES as $type): ?>
                        <option value="<?= $type ?>" <?= $imagingType === $type ? 'selected' : '' ?>><?= $type ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From</label>
                <input type="date" name="date_from" class="form-control" value="<?= htmlspecialchars($dateFrom) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">To</label>
                <input type="date" name="date_to" class="form-control" value="<?= htmlspecialchars($dateTo) ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-archive"></i> Archived Images (<?= $total ?>)</h5>
        <a href="<?= BASE_URL ?>/index.php?page=imaging" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Active Images
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($images)): ?>
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h5>No archived images</h5>
                <p>Archived images will appear here.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Patient</th>
                            <th>Type</th>
                            <th>Study Date</th>
                            <th>Body Part</th>
                            <th>Archived By</th>
                            <th>Reason</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($images as $img): ?>
                            <tr>
                                <td>#<?= $img['image_id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($img['patient_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($img['hospital_number']) ?></small>
                                </td>
                                <td><span class="badge bg-primary"><?= htmlspecialchars($img['imaging_type']) ?></span></td>
                                <td><?= date('d M Y', strtotime($img['study_date'])) ?></td>
                                <td><?= htmlspecialchars($img['body_part']) ?></td>
                                <td>
                                    <small>
                                        <?= htmlspecialchars($img['deleted_by_name'] ?? '-') ?><br>
                                        <span class="text-muted">
                                            <?= date('d M Y H:i', strtotime($img['updated_at'])) ?>
                                        </span>
                                    </small>
                                </td>
                                <td>
                                    <?php if (!empty($img['deletion_reason'])): ?>
                                        <small><?= htmlspecialchars(substr($img['deletion_reason'], 0, 60)) ?><?= strlen($img['deletion_reason']) > 60 ? '...' : '' ?></small>
                                    <?php else: ?>
                                        <small class="text-muted">—</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/index.php?page=imaging&action=view&id=<?= $img['image_id'] ?>"
                                           class="btn btn-outline-primary" target="_blank" title="View">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if (Auth::hasPermission('delete_image')): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/index.php?page=imaging&action=restore"
                                              class="d-inline"
                                              onsubmit="return confirm('Restore image #<?= $img['image_id'] ?>? It will become active again.');">
                                            <?= CSRF::field() ?>
                                            <input type="hidden" name="id" value="<?= $img['image_id'] ?>">
                                            <button type="submit" class="btn btn-outline-success" title="Restore">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <div class="card-footer">
                    <nav>
                        <ul class="pagination pagination-sm mb-0 justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=imaging&action=archived&page_num=<?= $i ?>&search=<?= urlencode($search) ?>&imaging_type=<?= urlencode($imagingType) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require APP_ROOT . '/views/layouts/footer.php'; ?>
